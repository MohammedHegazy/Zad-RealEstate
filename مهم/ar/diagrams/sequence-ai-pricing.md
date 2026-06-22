# مخطط التسلسل — نموذج تسعير الذكاء الاصطناعي (ML)

> **النطاق:** تنبؤ سعر العقار عبر Flask + scikit-learn  
> **الملفات:** `PricePredictionController`, `EstatePricePredictionService`, `PricePredictionClient`, `ml/pricing/server.py`

---

## 1. تسلسل — تنبؤ سعر عقار موجود

```mermaid
sequenceDiagram
    autonumber
    actor User as المستخدم
    participant Vue as EstatePricePrediction.vue
    participant API as POST /estates/{id}/price-prediction
    participant Sanctum as auth:sanctum
    participant Ctrl as PricePredictionController
    participant Svc as EstatePricePredictionService
    participant Client as PricePredictionClient
    participant Flask as ml/pricing/server.py
    participant Model as sklearn model.pkl
    participant DB as price_predictions

    User->>Vue: زر «احسب السعر المتوقع»
    Vue->>API: POST + Bearer token
    API->>Sanctum: التحقق من التوكن
    alt غير مصادق
        Sanctum-->>User: 401
    end

    Sanctum->>Ctrl: forEstate(request, estate)
    Ctrl->>Ctrl: canViewEstate(active أو مالك)
    alt عقار غير متاح
        Ctrl-->>User: 404
    end

    Ctrl->>Svc: predictForEstate(estate, user)
    Svc->>Svc: buildPayloadFromEstate() — place, space, rooms, date...
    Svc->>Client: predict(payload)

    Client->>Flask: HTTP POST /predict (JSON)
    Flask->>Flask: LabelEncoder.transform(place)
    Flask->>Flask: استخراج سنة البناء
    Flask->>Model: model.predict([11 features])
    Model-->>Flask: predicted_price
    Flask-->>Client: { predicted_price }

    alt Flask غير متاح
        Client-->>Ctrl: RuntimeException
        Ctrl-->>User: 503
    end

    Client-->>Svc: { predicted_price }
    Svc->>Svc: formatResult — difference, valuation_insight

    alt ML_PRICE_PREDICTION_LOG = true
        Svc->>DB: PricePrediction::create()
        DB-->>Svc: prediction_id
    end

    Svc-->>Ctrl: enriched JSON
    Ctrl-->>Vue: 200 success
    Vue-->>User: السعر المتوقع + الفرق + insight
```

---

## 2. تسلسل — تنبؤ ad-hoc (Preview)

```mermaid
sequenceDiagram
    autonumber
    actor User as المستخدم
    participant API as POST /price-predictions/preview
    participant Req as StorePricePredictionPreviewRequest
    participant Ctrl as PricePredictionController
    participant Svc as EstatePricePredictionService
    participant Client as PricePredictionClient
    participant Flask as Flask /predict

    User->>API: POST { place, space, rooms, date_of_build... }
    Note over API: auth:sanctum

    API->>Req: validate()
    alt فشل التحقق
        Req-->>User: 422
    end

    Req->>Ctrl: preview(request)
    Ctrl->>Svc: predictFromInput(validated, user)
    Svc->>Svc: buildPayloadFromArray()
    Svc->>Client: predict(payload)
    Client->>Flask: POST /predict
    Flask-->>Client: { predicted_price }
    Client-->>Svc: result
    Svc->>Svc: formatResult(listed_price اختياري)
    Svc-->>Ctrl: JSON
    Ctrl-->>User: 200
```

> **ملاحظة:** لا توجد واجهة Vue موصولة بـ preview حالياً — API فقط.

---

## 3. تسلسل — معالجة Flask داخلياً

```mermaid
sequenceDiagram
    autonumber
    participant Client as PricePredictionClient
    participant Flask as server.py
    participant LE as label_encoder.pkl
    participant M as real_estate_model.pkl

    Client->>Flask: POST /predict JSON
    Flask->>Flask: parse: place, space_of_estate, is_furnished, floor...
    Flask->>LE: transform([place])
    LE-->>Flask: place_encoded
    Flask->>Flask: date_of_build → year only
    Flask->>Flask: features = [encoded, space, furnished, floor, beds, ...]
    Flask->>M: predict([features])
    M-->>Flask: scalar price
    Flask-->>Client: { "predicted_price": 450000.0 }
```

---

## 4. تسلسل — insight السعر

```mermaid
sequenceDiagram
    autonumber
    participant Svc as EstatePricePredictionService
    participant Logic as formatResult()

    Svc->>Logic: predictedPrice, listedPrice
    Logic->>Logic: difference = predicted − listed
    Logic->>Logic: differencePercent = (difference / listed) × 100

    alt |differencePercent| < 5%
        Logic->>Logic: insight = aligned_with_model
    else difference > 0
        Logic->>Logic: insight = listed_below_prediction
    else difference < 0
        Logic->>Logic: insight = listed_above_prediction
    end

    Logic-->>Svc: JSON enriched
```

---

## 5. الملفات والمسارات

| التسلسل | API | المتحكم |
|---------|-----|---------|
| تنبؤ عقار | `POST /estates/{estate}/price-prediction` | `PricePredictionController::forEstate` |
| preview | `POST /price-predictions/preview` | `PricePredictionController::preview` |
| Flask | `POST http://127.0.0.1:5000/predict` | `server.py::predict()` |

**Config:** `config/ml.php` — `ML_PRICE_PREDICTION_URL`, `ML_PRICE_PREDICTION_LOG`
