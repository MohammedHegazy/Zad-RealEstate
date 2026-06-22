# Trust & Credibility API

Base path: `/api/v1`

## Overview

The trust module provides moderated reviews for properties, agents, and companies; identity verification; and a composite trust score (0–100) for agents and companies.

Review statuses: `pending`, `approved`, `rejected`  
Only **approved** reviews appear on public listing endpoints.

---

## Property Reviews

| Method | URI | Auth | Description |
|--------|-----|------|-------------|
| GET | `/estates/{estate}/reviews` | — | List approved reviews |
| GET | `/estates/{estate}/reviews/summary` | — | Average rating & count |
| POST | `/estates/{estate}/reviews` | ✓ | Submit review (pending) |
| PUT | `/my/property-reviews/{propertyReview}` | ✓ | Update own review |
| DELETE | `/my/property-reviews/{propertyReview}` | ✓ | Delete own review |

**Rules:** One review per user per property. Rating 1–5. Cannot review own property.

---

## Agent Reviews

| Method | URI | Auth | Description |
|--------|-----|------|-------------|
| GET | `/agents/{agent}/reviews` | — | List approved reviews |
| GET | `/agents/{agent}/reviews/summary` | — | Average rating & count |
| POST | `/agents/{agent}/reviews` | ✓ | Submit review |
| PUT | `/my/agent-reviews/{agentReview}` | ✓ | Update own review |
| DELETE | `/my/agent-reviews/{agentReview}` | ✓ | Delete own review |
| GET | `/agents/{agent}/trust-score` | — | Agent trust score (0–100) |

---

## Company Reviews

| Method | URI | Auth | Description |
|--------|-----|------|-------------|
| GET | `/companies/{company}/reviews` | — | List approved reviews |
| GET | `/companies/{company}/reviews/summary` | — | Average rating & count |
| POST | `/companies/{company}/reviews` | ✓ | Submit review |
| PUT | `/my/company-reviews/{companyReview}` | ✓ | Update own review |
| DELETE | `/my/company-reviews/{companyReview}` | ✓ | Delete own review |
| GET | `/companies/{company}/trust-score` | — | Company trust score |

---

## Verification Requests

| Method | URI | Auth | Description |
|--------|-----|------|-------------|
| GET | `/my/verification-requests` | ✓ | List own requests |
| POST | `/my/verification-requests` | ✓ | Submit verification |

**Body:** `document_type` (national_id, passport, business_license, other), `document` (file) or `document_path` (string).

On admin approval, `users.is_verified` is set to `true` and trust scores are recalculated.

---

## Trust Score

Calculated by `TrustScoreService` using weighted factors (config: `realestate.trust_score.weights`):

| Factor | Weight |
|--------|--------|
| Verified account | 25 |
| Approved properties | 25 |
| Average rating | 25 |
| Review count | 15 |
| Platform activity | 10 |

Stored on `agents.trust_score` and `companies.trust_score`.

---

## Admin Moderation

Base: `/api/v1/admin/trust` (requires admin)

| Method | URI | Description |
|--------|-----|-------------|
| GET | `/reviews/pending?type=property\|agent\|company` | Pending reviews |
| GET | `/verifications/pending` | Pending verification requests |
| POST | `/property-reviews/{id}/approve` | Approve property review |
| POST | `/property-reviews/{id}/reject` | Reject property review |
| POST | `/agent-reviews/{id}/approve` | Approve agent review |
| POST | `/agent-reviews/{id}/reject` | Reject agent review |
| POST | `/company-reviews/{id}/approve` | Approve company review |
| POST | `/company-reviews/{id}/reject` | Reject company review |
| POST | `/verifications/{id}/approve` | Approve verification |
| POST | `/verifications/{id}/reject` | Reject verification |

---

## Architecture

- **Services:** `ReviewService`, `TrustScoreService`, `VerificationRequestService`
- **Policies:** `PropertyReviewPolicy`, `AgentReviewPolicy`, `CompanyReviewPolicy`, `VerificationRequestPolicy`
- **Resources:** `PropertyReviewResource`, `AgentReviewResource`, `CompanyReviewResource`, `VerificationRequestResource`, `TrustScoreResource`, `RatingSummaryResource`
