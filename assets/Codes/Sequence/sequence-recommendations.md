# مخطط التسلسل — نظام التوصيات (Suggestions / Recommendations)

> **النطاق:** جلب توصيات، توليد، تقييم، عقارات مشابهة  
> **الملفات:** `RecommendationController`, `RecommendationService`, `RecommendationGeneratorService`, `RecommendationScoringService`

---

## 1. تسلسل — جلب قائمة التوصيات

```mermaid
sequenceDiagram
    autonumber
    actor User as المستخدم
    participant Vue as RecommendationsPage.vue
    participant Ctrl as RecommendationController
    participant Svc as RecommendationService
    participant Gen as RecommendationGeneratorService
    participant Score as RecommendationScoringService
    participant Inter as PropertyInteractionService
    participant DB as recommendations

    User->>Vue: GET /recommendations?refresh=0
    Vue->>Ctrl: index(request)
    Note over Ctrl: auth:sanctum

    Ctrl->>Svc: getRecommendationsForUser(user, per_page, refresh)

    Svc->>Svc: load preference, favorites
    Svc->>Inter: inferBehavioralProfile(user)

    alt لا preferences ولا behavior ولا favorites
        Svc-->>Ctrl: paginator فارغ + رسالة
        Ctrl-->>User: 200 — أضف مفضلات
    end

    Svc->>Svc: shouldRegenerate(user)?
    alt stale أو refresh=1
        Svc->>Gen: generateForUser(user)
        Gen->>Inter: inferBehavioralProfile()
        Gen->>DB: deactivate old recommendations

        alt favorites موجودة
            Gen->>Score: scoreCandidatesAgainstFavorites(pool 150)
        else
            Gen->>Score: scoreEstate لكل candidate
        end

        Score-->>Gen: scores + reasons
        Gen->>Gen: sortByDesc — filter minScore(40) — take(30)
        Gen->>DB: Recommendation::updateOrCreate
        Gen-->>Svc: { generated, deactivated }
    end

    Svc->>DB: paginate active recommendations
    DB-->>Svc: Recommendation[] + estates
    Svc-->>Ctrl: result + match_summary
    Ctrl->>Ctrl: formatListResponse()
    Ctrl-->>Vue: 200 JSON
    Vue-->>User: RecommendationCard[] + match %
```

---

## 2. تسلسل — تقييم عقار واحد `scoreEstate`

```mermaid
sequenceDiagram
    autonumber
    participant Gen as RecommendationGeneratorService
    participant Score as RecommendationScoringService
    participant Estate as Estate model
    participant Pref as UserPreference

    Gen->>Score: scoreEstate(estate, preference, behavior)
    Score->>Score: scoreBudget() — 25%
    Score->>Score: scoreLocation() — 25%
    Score->>Score: scorePropertyType() — 20%
    Score->>Score: scoreRoi() — 15% — يستخدم estate.roi
    Score->>Score: scoreInvestmentGoal() — 15%
    Score->>Score: behaviorBonus() — +5/+3
    Score->>Score: Σ(factor × weight) → min(100)
    Score->>Score: buildReasons() — why_recommended
    Score-->>Gen: { score, factors, reasons }
```

---

## 3. تسلسل — إعادة توليد صريح

```mermaid
sequenceDiagram
    autonumber
    actor User as المستخدم
    participant Vue as RecommendationsPage
    participant Ctrl as RecommendationController
    participant Svc as RecommendationService
    participant Gen as RecommendationGeneratorService

    User->>Vue: زر «تحديث التوصيات»
    Vue->>Ctrl: POST /recommendations/refresh
    Ctrl->>Svc: refreshForUser(user)
    Svc->>Gen: generateForUser(user)
    Gen-->>Svc: { generated, deactivated }
    Svc->>Svc: getRecommendationsForUser(forceRefresh=false)
    Svc-->>Ctrl: result + last_generation
    Ctrl-->>Vue: 200 — N properties saved
    Vue-->>User: قائمة محدّثة
```

---

## 4. تسلسل — عقارات مشابهة

```mermaid
sequenceDiagram
    autonumber
    actor User as المستخدم
    participant Ctrl as RecommendationController
    participant Svc as RecommendationService
    participant Score as RecommendationScoringService
    participant DB as estates

    User->>Ctrl: GET /recommendations/similar-estates/{estate}
    Ctrl->>Ctrl: estate.status === active?
    alt غير active
        Ctrl-->>User: 404
    end

    Ctrl->>Svc: getSimilarEstates(user, estate, limit)
    Svc->>DB: candidates — نفس المدينة — pool 50
    DB-->>Svc: Estate[]

    loop لكل candidate
        Svc->>Score: scoreSimilarity(source, candidate)
        Score-->>Svc: similarity 0-100
        opt favorites أو preferences
            Svc->>Score: scoreAgainstFavorites / scoreEstate
            Score-->>Svc: personalized score
            Svc->>Svc: combined = similarity×0.6 + personalized×0.4
        end
    end

    Svc->>Svc: sortByDesc combined — take(limit)
    Svc-->>Ctrl: similar_estates[]
    Ctrl-->>User: 200 JSON
```

---

## 5. تسلسل — محفّز: إضافة مفضلة

```mermaid
sequenceDiagram
    autonumber
    actor User as المستخدم
    participant Fav as FavoriteEstateController
    participant Gen as RecommendationGeneratorService
    participant DB as recommendations

    User->>Fav: POST /estates/{id}/favorite
    Fav->>DB: Favorit_estate::create
    Fav->>Gen: generateForUser(user)
    Gen->>DB: regenerate recommendations
    Gen-->>Fav: { generated }
    Fav-->>User: 201 favorite + توصيات محدّثة
```

---

## 6. الملفات والمسارات

| التسلسل | API | المتحكم |
|---------|-----|---------|
| قائمة | `GET /recommendations` | `RecommendationController::index` |
| refresh | `POST /recommendations/refresh` | `refresh` |
| مشابه | `GET /recommendations/similar-estates/{estate}` | `similarEstates` |
| top | `GET /recommendations/top` | `top` |

**Vue:** `RecommendationsPage.vue`, `RecommendationCard.vue`, `src/api/recommendations.js`
