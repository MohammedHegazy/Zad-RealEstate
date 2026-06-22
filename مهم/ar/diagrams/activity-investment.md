# مخطط النشاط — الاستثمار وكل ما يتعلق به

> **النطاق:** حساب ROI، تحليلات الاستثمار، المحافظ، لوحة المستثمر  
> **المحرك المركزي:** `InvestmentCalculatorService`  
> **الملفات الرئيسية:** `app/Services/Investment/*`, `InvestmentAnalysisController`, `InvestorDashboardController`, `PortfolioService`

---

## 1. مخطط النشاط الشامل

```mermaid
flowchart TD
    Start([بداية — طلب متعلق بالاستثمار]) --> Entry{نوع العملية؟}

    %% ── مسار العقار ──
    Entry -->|إنشاء/تحديث عقار| E1[StoreEstateRequest / UpdateEstateRequest]
    E1 --> E2{التحقق ناجح؟}
    E2 -->|لا| E422[422 — خطأ تحقق]
    E2 -->|نعم| E3[EstateController::store / update]
    E3 --> E4[prepareEstateData — استخراج المدخلات]
    E4 --> E5[InvestmentCalculatorService::applyToEstatePayload]
    E5 --> E6[normalizeEstateArray]
    E6 --> E7[calculateCore]
    E7 --> E8[دمج: expected_annual_income, roi, payback_period]
    E8 --> E9[Estate::create / update]
    E9 --> E200[200 — عقار + مقاييس استثمار]

    %% ── مسار التحليل ──
    Entry -->|تحليل استثماري| A1[StoreInvestmentAnalysisRequest]
    A1 --> A2{التحقق ناجح؟}
    A2 -->|لا| A422[422]
    A2 -->|نعم| A3[InvestmentAnalysisController::createAnalysis]
    A3 --> A4{storeByEstate؟}
    A4 -->|نعم| A5[ملء القيم الافتراضية من Estate]
    A4 -->|لا| A6[استخدام المدخلات المرسلة]
    A5 --> A7[calculateForAnalysisStorage]
    A6 --> A7
    A7 --> A8[normalizeAnalysisArray — HOA = 0]
    A8 --> A9[calculateCore]
    A9 --> A10[InvestmentAnalysis::create]
    A10 --> A200[201 — تحليل محفوظ]

    %% ── مسار المحفظة ──
    Entry -->|إضافة عقار للمحفظة| P1[StorePortfolioItemRequest]
    P1 --> P2{التحقق + auth:sanctum؟}
    P2 -->|لا| P401[401 / 422]
    P2 -->|نعم| P3[PortfolioService::addEstateToPortfolio]
    P3 --> P4{العقار active؟}
    P4 -->|لا| P403[EstateNotPublishedException]
    P4 -->|نعم| P5{موجود مسبقاً؟}
    P5 -->|نعم| P409[DuplicatePortfolioItemException]
    P5 -->|لا| P6[PortfolioProperty::create — status=tracking]
    P6 --> P200[201 — عنصر محفظة]

    Entry -->|تحديث حالة عنصر| P7[UpdatePortfolioItemRequest]
    P7 --> P8[PortfolioService::updatePortfolioItemStatus]
    P8 --> P9{انتقال صالح؟ tracking→invested→sold}
    P9 -->|لا| P422b[InvalidPortfolioStatusTransitionException]
    P9 -->|نعم| P10[تحديث invested_at / sold_at]
    P10 --> P200b[200]

    %% ── مسار لوحة المستثمر ──
    Entry -->|لوحة المستثمر| D1[GET /investor/dashboard]
    D1 --> D2{auth:sanctum؟}
    D2 -->|لا| D401[401]
    D2 -->|نعم| D3[InvestorDashboardService::getSummary]
    D3 --> D4[تحميل PortfolioProperty + Estate]
    D4 --> D5[لكل عنصر: calculateForEstate]
    D5 --> D6[calculateCore — إعادة حساب حية]
    D6 --> D7[تجميع: total_investments, average_roi مرجّح]
    D7 --> D8[best/worst_performing_property]
    D8 --> D200[200 — ملخص JSON]

    style E422 fill:#fee
    style A422 fill:#fee
    style P401 fill:#fee
    style P403 fill:#fee
    style P409 fill:#fee
    style P422b fill:#fee
    style D401 fill:#fee
```

---

## 2. مخطط نشاط — نواة الحساب `calculateCore`

```mermaid
flowchart TD
    CStart([calculateCore]) --> I1[قراءة: monthlyRent, occupancyRate, expenses, maintenance, tax, hoa, purchasePrice]

    I1 --> G1["grossAnnual = monthlyRent × 12 × (occupancyRate / 100)"]
    G1 --> G2["totalCosts = expenses + maintenance + tax + hoa"]
    G2 --> G3["netProfit = grossAnnual − totalCosts"]

    G3 --> G4{netProfit > 0؟}
    G4 -->|نعم| G5["expectedAnnualIncome = round(netProfit, 2)"]
    G4 -->|لا| G6["expectedAnnualIncome = 0"]

    G5 --> G7["monthlyIncome = expectedAnnualIncome / 12"]
    G6 --> G7
    G7 --> G8["cashFlow = monthlyIncome"]

    G8 --> R1{purchasePrice > 0 AND expectedAnnualIncome > 0؟}
    R1 -->|نعم| R2["roi = (expectedAnnualIncome / purchasePrice) × 100"]
    R1 -->|نعم| R3["paybackPeriod = purchasePrice / expectedAnnualIncome"]
    R1 -->|لا| R4["roi = null, paybackPeriod = null"]

    R2 --> CEnd([InvestmentMetrics])
    R3 --> CEnd
    R4 --> CEnd
```

---

## 3. الملفات والمسارات ذات الصلة

| العملية | API | المتحكم | الخدمة |
|---------|-----|---------|--------|
| إنشاء عقار + ROI | `POST /my/estates` | `EstateController::store` | `InvestmentCalculatorService` |
| تحليل استثماري | `POST /investment-analyses` | `InvestmentAnalysisController` | `calculateForAnalysisStorage` |
| محفظة | `POST /investment-portfolios/{id}/properties` | `InvestmentPortfolioController` | `PortfolioService` |
| لوحة المستثمر | `GET /investor/dashboard` | `InvestorDashboardController` | `InvestorDashboardService` |

---

## 4. ملاحظات معمارية

- **مصدر واحد للحقيقة:** جميع حسابات ROI تمر عبر `InvestmentCalculatorService::calculateCore()`.
- **فرق Estate vs Analysis:** التحليل لا يشمل `annual_hoa_or_service`.
- **لوحة المستثمر:** تُعيد الحساب حياً؛ `PortfolioService::getPortfolioSummary()` يقرأ قيم مخزنة.
