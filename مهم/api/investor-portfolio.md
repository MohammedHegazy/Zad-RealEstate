# Investor Portfolio REST API (`/api/v1`)

All endpoints require **Sanctum** authentication: `Authorization: Bearer {token}`.

Base URL: `https://your-host/api/v1`

Responses use the global envelope: `{ "success": true|false, "message": string, "data": ..., "meta"?: ... }` (see `ApiResponses`).

---

## 1. `GET /my/portfolios`

**Purpose:** List the authenticated user's portfolios (paginated).

**Policy:** `viewAny` on `Portfolio`.

**Query:** `per_page` (optional, 1–100, default 15).

**Route name:** `api.my.portfolios.index`

### Example request

```http
GET /api/v1/my/portfolios?per_page=10
Authorization: Bearer 1|xxxxxxxx
```

### Example response (200)

```json
{
  "success": true,
  "message": "Portfolios retrieved.",
  "data": [
    {
      "id": 1,
      "name": "My Portfolio",
      "description": null,
      "is_default": true,
      "items_count": 3,
      "created_at": "2026-05-24T12:00:00+00:00",
      "updated_at": "2026-05-24T12:00:00+00:00"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 10,
    "total": 1
  }
}
```

---

## 2. `POST /my/portfolios`

**Purpose:** Create a named portfolio.

**Policy:** `create` on `Portfolio`.

**Body (JSON):** `name` (required), `description` (optional), `is_default` (optional boolean — clears other defaults when true).

**Route name:** `api.my.portfolios.store`

### Example request

```http
POST /api/v1/my/portfolios
Authorization: Bearer 1|xxxxxxxx
Content-Type: application/json

{
  "name": "Rental income",
  "description": "Long-term holds",
  "is_default": false
}
```

### Example response (201)

```json
{
  "success": true,
  "message": "Portfolio created.",
  "data": {
    "id": 2,
    "name": "Rental income",
    "description": "Long-term holds",
    "is_default": false,
    "items_count": 0,
    "created_at": "2026-05-24T12:05:00+00:00",
    "updated_at": "2026-05-24T12:05:00+00:00"
  }
}
```

---

## 3. `GET /my/portfolio-items`

**Purpose:** List portfolio line items (paginated, eager-loaded `portfolio` + `estate.place`).

**Policy:** `viewAny` on `PortfolioItem`.

**Query:** `per_page` (optional), `portfolio_id` (optional, must belong to user), `status` (`tracking`|`invested`|`sold`).

**Route name:** `api.my.portfolio-items.index`

### Example request

```http
GET /api/v1/my/portfolio-items?portfolio_id=1&status=invested&per_page=20
Authorization: Bearer 1|xxxxxxxx
```

### Example response (200)

```json
{
  "success": true,
  "message": "Portfolio items retrieved.",
  "data": [
    {
      "id": 10,
      "portfolio_id": 1,
      "estate_id": 42,
      "status": "invested",
      "investment_amount": "250000.00",
      "notes": "Bought Q1",
      "invested_at": "2026-05-01T10:00:00+00:00",
      "sold_at": null,
      "created_at": "2026-05-01T10:00:00+00:00",
      "updated_at": "2026-05-20T08:00:00+00:00",
      "portfolio": {
        "id": 1,
        "name": "My Portfolio",
        "description": null,
        "is_default": true,
        "created_at": "2026-05-24T12:00:00+00:00",
        "updated_at": "2026-05-24T12:00:00+00:00"
      },
      "estate": {
        "id": 42,
        "name": "Apartment — Mazzeh",
        "status": "active",
        "type_text": "sale",
        "kind_text": "apartment",
        "price": "150000.00",
        "roi": "8.5000",
        "expected_annual_income": "12000.00",
        "place": { "id": 3, "name": "Mazzeh" }
      }
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 20,
    "total": 1
  }
}
```

---

## 4. `POST /my/portfolio-items`

**Purpose:** Add an **active** estate to a portfolio (default portfolio if `portfolio_id` omitted).

**Policy:** `create` on `PortfolioItem`.

**Body:** `estate_id` (required), `portfolio_id` (optional), `status` (`tracking`|`invested`), `investment_amount`, `notes`.

**Route name:** `api.my.portfolio-items.store`

### Example response (201)

```json
{
  "success": true,
  "message": "Estate added to portfolio.",
  "data": {
    "id": 11,
    "portfolio_id": 1,
    "estate_id": 42,
    "status": "tracking",
    "investment_amount": null,
    "notes": "Watching price",
    "invested_at": null,
    "sold_at": null
  }
}
```

---

## 5. `PATCH /my/portfolio-items/{id}`

**Purpose:** Update status and/or fields (at least one of `status`, `investment_amount`, `notes`).

**Policy:** `update` on `PortfolioItem`.

**Route name:** `api.my.portfolio-items.update`

---

## 6. `DELETE /my/portfolio-items/{id}`

**Purpose:** Remove one line item.

**Policy:** `delete` on `PortfolioItem`.

**Route name:** `api.my.portfolio-items.destroy`

---

## 7. `GET /my/dashboard/summary`

**Purpose:** Aggregated metrics across **all** portfolios.

**Policy:** `viewAny` on `Portfolio`.

**Route name:** `api.my.dashboard.summary`

### Example response (200)

```json
{
  "success": true,
  "message": "Dashboard summary retrieved.",
  "data": {
    "total_portfolios": 2,
    "total_invested": 400000,
    "weighted_average_roi": 7.875,
    "expected_annual_income": 28000,
    "counts_by_status": {
      "tracking": 1,
      "invested": 2,
      "sold": 0
    },
    "total_items": 3
  }
}
```

---

## Architecture

| Layer | Responsibility |
|-------|----------------|
| **Form Requests** | Validation |
| **Policies** | Ownership |
| **API Resources** | JSON shape |
| **`PortfolioService`** | Business rules, transactions, queries |
