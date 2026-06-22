# HTTP Controllers

## Layout

```
Controllers/
├── Controller.php              # Base Laravel controller
├── Concerns/                   # Reusable controller traits
│   ├── ManagesEstateAds.php
│   ├── ManagesEstateImages.php
│   ├── ManagesEstateVideos.php
│   ├── QueriesAgents.php
│   ├── QueriesCities.php
│   ├── QueriesPlaces.php
│   └── ResolvesOwnedCompany.php
└── Api/
    ├── ApiController.php       # JSON API base (ApiResponses)
    ├── AuthController.php
    ├── UserController.php
    ├── EstateController.php
    ├── EstateImageController.php
    ├── EstateVideoController.php
    ├── EstateAdController.php
    ├── InvestmentAnalysisController.php
    ├── FavoriteEstateController.php
    ├── FavoriteAgentController.php
    ├── CompanyController.php
    ├── CompanySocialLinkController.php
    ├── AgentController.php
    ├── AgentRateController.php
    ├── CityController.php
    ├── PlaceController.php
    ├── NotificationController.php
    ├── MessageController.php
    ├── SocialMediaController.php
    └── Admin/                  # Admin-only endpoints
        ├── AuthController.php
        ├── DashboardController.php
        ├── UserController.php
        ├── EstateController.php
        └── …
```

## Conventions

1. **User API** lives in `Api\` and is wired in `routes/api/v1/`.
2. **Admin API** lives in `Api\Admin\` and is wired in `routes/api/admin/`.
3. **Form requests** live in `app/Http/Requests/` (shared rules in `Concerns/EstateValidationRules.php`).
4. **Response formatting** uses traits under `app/Traits/` (e.g. `FormatsEstateResponse`, `FormatsInvestmentAnalysisResponse`).
5. **Business logic** that is not HTTP-specific belongs in `app/Services/`.

## Adding a new endpoint

1. Create or update the controller under the correct namespace (`Api\` vs `Api\Admin\`).
2. Add a form request if validation is non-trivial.
3. Register the route in the matching file under `routes/api/` (do not add routes directly to a monolithic file).
4. Document the endpoint in `routes/API.md`.
5. Run `php artisan route:list --path=api/v1` to verify URI and route name.
