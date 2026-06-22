# مخطط التسلسل — الاستثمار وكل ما يتعلق به

> **النطاق:** إنشاء عقار مع ROI، تحليل استثماري، لوحة المستثمر  
> **المحرك:** `InvestmentCalculatorService`

---

## 1. تسلسل — إنشاء عقار مع حساب ROI

```mermaid
sequenceDiagram
    autonumber
    actor User as المستخدم (مالك)
    participant Vue as Vue — POST /my/estates
    participant Req as StoreEstateRequest
    participant Ctrl as EstateController
    participant Calc as InvestmentCalculatorService
    participant Core as calculateCore
    participant DB as قاعدة البيانات

    User->>Vue: إرسال نموذج العقار (price, monthly_rent, expenses...)
    Vue->>Ctrl: POST /api/v1/my/estates
    Note over Ctrl: auth:sanctum

    Ctrl->>Req: validate()
    alt فشل التحقق
        Req-->>User: 422 Unprocessable
    end

    Ctrl->>Ctrl: prepareEstateData()
    Ctrl->>Calc: applyToEstatePayload($data)
    Calc->>Calc: normalizeEstateArray()
    Calc->>Core: calculateCore(monthlyRent, occupancy, costs, price...)
    Core-->>Calc: InvestmentMetrics (roi, income, payback)
    Calc->>Calc: دمج expected_annual_income, roi, payback_period في $data
    Calc-->>Ctrl: $data محدّث

    Ctrl->>DB: BEGIN TRANSACTION
    Ctrl->>DB: Estate::create($data)
    Ctrl->>DB: sync media / social links
    Ctrl->>DB: COMMIT

    Ctrl-->>Vue: 201 Created + formatEstate()
    Vue-->>User: عقار + مقاييس استثمار
```

---

## 2. تسلسل — حفظ تحليل استثماري

```mermaid
sequenceDiagram
    autonumber
    actor User as المستثمر
    participant Vue as Vue / API Client
    participant Req as StoreInvestmentAnalysisRequest
    participant Ctrl as InvestmentAnalysisController
    participant Calc as InvestmentCalculatorService
    participant DB as قاعدة البيانات

    User->>Vue: POST /estates/{id}/investment-analyses
    Vue->>Ctrl: storeByEstate(request, estate)
    Note over Ctrl: auth:sanctum

    Ctrl->>Req: validate()
    Ctrl->>Ctrl: ملء property_price, monthly_rent من Estate إن غابت

    Ctrl->>Ctrl: createAnalysis(user, estate_id, input)
    Ctrl->>Calc: calculateForAnalysisStorage(input)
    Calc->>Calc: normalizeAnalysisArray() — HOA = 0
    Calc->>Calc: calculateCore()
    Calc-->>Ctrl: { expected_annual_income, roi, payback_period }

    Ctrl->>DB: InvestmentAnalysis::create(مدخلات + مقاييس)
    Ctrl-->>Vue: 201 + formatInvestmentAnalysis()
    Vue-->>User: تحليل محفوظ
```

---

## 3. تسلسل — لوحة المستثمر (Dashboard)

```mermaid
sequenceDiagram
    autonumber
    actor User as المستثمر
    participant Vue as Vue
    participant Ctrl as InvestorDashboardController
    participant Dash as InvestorDashboardService
    participant Calc as InvestmentCalculatorService
    participant DB as قاعدة البيانات

    User->>Vue: GET /investor/dashboard
    Vue->>Ctrl: summary(request)
    Note over Ctrl: auth:sanctum + PortfolioPolicy

    Ctrl->>Dash: getSummary(user)
    Dash->>DB: InvestmentPortfolio + PortfolioProperty + Estate
    DB-->>Dash: عناصر المحفظة

    loop لكل PortfolioProperty
        Dash->>Calc: calculateForEstate(estate)
        Calc->>Calc: calculateCore() — إعادة حساب حية
        Calc-->>Dash: InvestmentMetrics
        Dash->>Dash: تجميع total_investments, weighted ROI
    end

    Dash-->>Ctrl: summary array
    Ctrl->>Ctrl: DashboardSummaryResource
    Ctrl-->>Vue: 200 JSON
    Vue-->>User: total_investments, average_roi, best/worst property
```

---

## 4. تسلسل — إضافة عقار للمحفظة

```mermaid
sequenceDiagram
    autonumber
    actor User as المستثمر
    participant Ctrl as InvestmentPortfolioController
    participant Svc as PortfolioService
    participant DB as قاعدة البيانات

    User->>Ctrl: POST /investment-portfolios/{id}/properties
    Note over Ctrl: auth:sanctum

    Ctrl->>Svc: addEstateToPortfolio(portfolio, estate_id, amount, status)
    Svc->>DB: Estate::find — status = active?
    alt عقار غير نشط
        Svc-->>User: EstateNotPublishedException
    end
    Svc->>DB: PortfolioProperty::create — status=tracking
    Svc-->>Ctrl: PortfolioProperty
    Ctrl-->>User: 201 — عنصر محفظة (ROI يُقرأ لاحقاً من Estate)
```

---

## 5. الملفات المرجعية

| التسلسل | المتحكم | الخدمة |
|---------|---------|--------|
| إنشاء عقار | `EstateController` | `InvestmentCalculatorService` |
| تحليل | `InvestmentAnalysisController` | `calculateForAnalysisStorage` |
| لوحة | `InvestorDashboardController` | `InvestorDashboardService` |
| محفظة | `InvestmentPortfolioController` | `PortfolioService` |
