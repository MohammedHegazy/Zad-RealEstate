# مخطط النشاط — نظام التوصيات (Suggestions / Recommendations)

> **النطاق:** توصيات مخصصة، عقارات مشابهة، تفضيلات، سلوك، مفضلات  
> **التقنية:** محرك تقييم مرجّح (Heuristics) — **ليس** ML/LLM  
> **الملفات الرئيسية:** `RecommendationService`, `RecommendationGeneratorService`, `RecommendationScoringService`, `PropertyInteractionService`

---

## 1. مخطط النشاط — جلب التوصيات

```mermaid
flowchart TD
    Start([GET /recommendations]) --> Auth[auth:sanctum]
    Auth --> AuthOK{مصادق؟}
    AuthOK -->|لا| E401[401]
    AuthOK -->|نعم| Svc[RecommendationService::getRecommendationsForUser]

    Svc --> Load[تحميل: preference, behavior, favorites]
    Load --> Signals{يوجد إشارات؟ preferences / behavior / favorites}

    Signals -->|لا| Empty[paginator فارغ + رسالة: أضف مفضلات]
    Empty --> E200a[200 — قائمة فارغة]

    Signals -->|نعم| Stale{forceRefresh OR shouldRegenerate؟}
    Stale -->|نعم| Gen[RecommendationGeneratorService::generateForUser]
    Stale -->|لا| Read[قراءة من جدول recommendations]

    Gen --> Deact[إلغاء تفعيل التوصيات القديمة]
    Deact --> Pool[جلب pool — 150 عقار candidate]
    Pool --> Fav{favorites موجودة؟}

    Fav -->|نعم| ScoreFav[scoreCandidatesAgainstFavorites — 75%]
    Fav -->|لا| ScorePref[scoreCandidates — تفضيلات + سلوك]

    ScoreFav --> Merge[دمج 75% favorites + 25% preferences]
    ScorePref --> Sort[sortByDesc score]
    Merge --> Sort

    Sort --> Filter[filter: score >= minScore — 40]
    Filter --> Take[take limit — 30]
    Take --> Upsert[Recommendation::updateOrCreate]
    Upsert --> Read

    Read --> Paginate[paginate per_page]
    Paginate --> Format[FormatsRecommendationResponse]
    Format --> E200[200 — JSON + match_summary]

    style E401 fill:#fee
```

---

## 2. مخطط نشاط — محرك التقييم `scoreEstate`

```mermaid
flowchart TD
    SStart([scoreEstate]) --> Factors[حساب 5 عوامل — 0 إلى 100]

    Factors --> B[budget_match — 25%]
    Factors --> L[location_match — 25%]
    Factors --> T[property_type_match — 20%]
    Factors --> R[roi_potential — 15%]
    Factors --> G[investment_goal_match — 15%]

    B --> Sum["matchingPercentage = Σ(factor × weight)"]
    L --> Sum
    T --> Sum
    R --> Sum
    G --> Sum

    Sum --> Bonus[behaviorBonus — +5 نوع, +3 موقع]
    Bonus --> Cap["score = min(100, matchingPercentage + bonus)"]
    Cap --> Reasons[buildReasons — why_recommended]
    Reasons --> SEnd([return score, factors, reasons])
```

---

## 3. مخطط نشاط — عقارات مشابهة

```mermaid
flowchart TD
    SimStart([GET /recommendations/similar-estates/{estate}]) --> Sim1{estate active؟}
    Sim1 -->|لا| Sim404[404]
    Sim1 -->|نعم| Sim2[RecommendationService::getSimilarEstates]
    Sim2 --> Sim3[جلب candidates — نفس المدينة — pool 50]
    Sim3 --> Sim4[لكل candidate: scoreSimilarity]
    Sim4 --> Sim5{favorites أو preferences؟}
    Sim5 -->|نعم| Sim6[scoreAgainstFavorites / scoreEstate]
    Sim5 -->|لا| Sim7[similarity فقط]
    Sim6 --> Sim8["combined = similarity×0.6 + personalized×0.4"]
    Sim7 --> Sim9[similarity فقط]
    Sim8 --> Sim10[sortByDesc — take limit]
    Sim9 --> Sim10
    Sim10 --> Sim200[200 — similar_estates JSON]
```

---

## 4. مخطط نشاط — محفّزات إعادة التوليد

```mermaid
flowchart TD
    TStart([shouldRegenerate]) --> T1{لا توجد توصيات active؟}
    T1 -->|نعم| TYes[إعادة توليد]
    T1 -->|لا| T2{المفضلات أحدث من آخر توصية؟}
    T2 -->|نعم| TYes
    T2 -->|لا| T3{مرّ recommendation_stale_hours — 24h؟}
    T3 -->|نعم| TYes
    T3 -->|لا| TNo[استخدام المخزنة]

    TYes --> Gen[generateForUser]
    TNo --> Read[قراءة DB]
```

**محفّزات إضافية:**
- `?refresh=1` في GET
- `POST /recommendations/refresh`
- إضافة/حذف مفضلة عبر `FavoriteEstateController`

---

## 5. الملفات والمسارات

| العملية | API | المتحكم |
|---------|-----|---------|
| قائمة التوصيات | `GET /recommendations` | `RecommendationController::index` |
| أعلى N | `GET /recommendations/top` | `RecommendationController::top` |
| إعادة توليد | `POST /recommendations/refresh` | `RecommendationController::refresh` |
| عقارات مشابهة | `GET /recommendations/similar-estates/{estate}` | `similarEstates` |
| تفضيلات | `GET/POST/DELETE /my/preferences` | `UserPreferenceController` |
| تفاعلات | `POST /estates/{id}/interactions` | `PropertyInteractionController` |

---

## 6. إعدادات `config/realestate.php`

| المفتاح | الافتراضي |
|---------|-----------|
| `recommendation_limit` | 30 |
| `recommendation_min_score` | 40 |
| `recommendation_candidate_pool` | 150 |
| `recommendation_stale_hours` | 24 |
| `similar_estates_pool` | 50 |
