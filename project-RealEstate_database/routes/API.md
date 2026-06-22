# API Routes Reference

Base URL for all endpoints: `/api`

Authentication uses **Laravel Sanctum** (`Authorization: Bearer {token}`) unless marked as public.

---

## Route file structure

```
routes/
├── api.php                          # Entry: loads v1 + admin modules
├── API.md                           # This document
└── api/
    ├── v1.php                       # User API wrapper (/api/v1)
    ├── admin.php                    # Admin API wrapper (/api/v1/admin)
    ├── v1/
    │   ├── auth.php                 # Register, login, logout
    │   ├── public.php               # Public catalogs (no auth)
    │   └── authenticated/
    │       ├── profile.php
    │       ├── estates.php
    │       ├── favorites.php
    │       ├── investment-analyses.php
    │       ├── companies.php
    │       ├── agents.php
    │       ├── notifications.php
    │       ├── messages.php
    │       └── social-media.php
    └── admin/
        ├── auth.php
        └── authenticated/
            ├── dashboard.php
            ├── users.php
            ├── companies.php
            ├── agents.php
            ├── locations.php          # cities & places
            ├── estates.php
            ├── notifications.php
            ├── messages.php
            └── social-media.php
```

---

## User API (`/api/v1`)

### Authentication

| Method | URI | Route name | Auth |
|--------|-----|------------|------|
| POST | `/register` | `api.register` | — |
| POST | `/login` | `api.login` | — |
| POST | `/logout` | `api.logout` | ✓ |

### Public catalogs

| Resource | Index | Show | Notes |
|----------|-------|------|-------|
| Companies | `GET /companies` | `GET /companies/{company}` | + `GET .../agents` |
| Agents | `GET /agents` | `GET /agents/{agent}` | + share, ratings |
| Cities | `GET /cities` | `GET /cities/{city}` | + `GET .../places` |
| Places | `GET /places` | `GET /places/{place}` | |
| Estates | `GET /estates` | `GET /estates/{estate}` | + `POST .../share` |

**Geolocation (public, no auth):**

| Method | URI | Route name | Description |
|--------|-----|------------|-------------|
| GET | `/estates/nearby` | `api.estates.nearby` | Nearest properties by lat/lng |
| GET | `/estates/in-radius` | `api.estates.in-radius` | Paginated radius search |
| GET | `/estates/map` | `api.estates.map` | Map markers + Leaflet/OSM/Google config |

See [docs/api/geolocation-maps.md](../docs/api/geolocation-maps.md) for parameters and examples.

**Trust & credibility (public listings + authenticated actions):**

| Method | URI | Auth | Description |
|--------|-----|------|-------------|
| GET | `/estates/{estate}/reviews` | — | Approved property reviews |
| GET | `/agents/{agent}/reviews` | — | Approved agent reviews |
| GET | `/companies/{company}/reviews` | — | Approved company reviews |
| GET | `/agents/{agent}/trust-score` | — | Agent trust score |
| GET | `/companies/{company}/trust-score` | — | Company trust score |
| POST | `/estates/{estate}/reviews` | ✓ | Submit property review |
| POST | `/my/verification-requests` | ✓ | Submit verification request |

See [docs/api/trust-credibility.md](../docs/api/trust-credibility.md) for full CRUD, moderation, and trust score details.

### Profile (authenticated)

| Method | URI | Route name |
|--------|-----|------------|
| GET | `/me` | `api.me` |
| PUT | `/profile` | `api.profile.update` |
| GET | `/users/{user}` | `api.users.show` |

### My estates (authenticated)

| Method | URI | Route name |
|--------|-----|------------|
| GET | `/my/estates` | `api.my.estates` |
| POST | `/my/estates` | `api.my.estates.store` |
| PUT | `/my/estates/{estate}` | `api.my.estates.update` |
| DELETE | `/my/estates/{estate}` | `api.my.estates.destroy` |
| PUT | `/my/estates/{estate}/social-media` | `api.my.estates.social-media` |

Nested under `/my/estates/{estate}/`:

- **images** — `api.my.estates.images.*`
- **videos** — `api.my.estates.videos.*`
- **ads** — `api.my.estates.ads.*`

Estate actions (authenticated):

| Method | URI | Route name |
|--------|-----|------------|
| POST | `/estates/{estate}/favorite` | `api.estates.favorite` |
| DELETE | `/estates/{estate}/favorite` | `api.estates.unfavorite` |
| POST | `/estates/{estate}/investment-analyses` | `api.estates.investment-analyses.store` |

### Investment analyses (authenticated)

| Method | URI | Route name |
|--------|-----|------------|
| GET | `/my/investment-analyses` | `api.my.investment-analyses.index` |
| POST | `/my/investment-analyses` | `api.my.investment-analyses.store` |
| GET | `/my/investment-analyses/{id}` | `api.my.investment-analyses.show` |
| PUT | `/my/investment-analyses/{id}` | `api.my.investment-analyses.update` |
| DELETE | `/my/investment-analyses/{id}` | `api.my.investment-analyses.destroy` |

### Favorites, companies, agents, notifications, messages

See route files under `routes/api/v1/authenticated/` for the full list.

Run `php artisan route:list --path=api/v1` to print every registered URI.

---

## Admin API (`/api/v1/admin`)

| Area | Route file | Middleware |
|------|------------|------------|
| Login / session | `api/admin/auth.php` | `auth:sanctum` + `admin` for logout & me |
| Dashboard | `api/admin/authenticated/dashboard.php` | ✓ |
| CRUD resources | `users`, `companies`, `agents`, `locations` | ✓ |
| Estate moderation | `api/admin/authenticated/estates.php` | ✓ |
| Trust moderation | `api/admin/authenticated/trust.php` | ✓ |

Admin login: `POST /api/v1/admin/login` → `admin.login`

---

## Controllers

| Namespace | Purpose |
|-----------|---------|
| `App\Http\Controllers\Api\` | User-facing API |
| `App\Http\Controllers\Api\Admin\` | Admin panel API |
| `App\Http\Controllers\Concerns\` | Shared controller traits (media, queries) |

All API controllers extend `App\Http\Controllers\Api\ApiController` (JSON responses via `ApiResponses` trait).

---

## Naming conventions

- **URLs**: kebab-case (`investment-analyses`, `social-links`)
- **Route names**: dot notation (`api.my.estates.images.store`, `admin.estates.status`)
- **Owner resources**: prefixed with `/my/` and named `api.my.*`
- **Nested resources**: `{parent}/{child}` groups in dedicated route files
