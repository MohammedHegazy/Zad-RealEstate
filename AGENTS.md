# System Reference

## Build & Lint
- Frontend dir: `project-RealEstate/`
- Lint: `npx eslint src/ --no-error-on-unmatched-pattern`
- Backend dir: `project-RealEstate_database/`

## UI Components (always prefer these over raw Bootstrap HTML)
- `AppInput`, `AppSelect`, `AppFormGroup`, `AppTextarea`, `AppCheckbox`, `AppButton`, `AppFileUpload`, `AppAutocomplete`
- `Breadcrumbs`, `DirectoryToolbar`, `Pagination`, `TableAction`, `TableActionGroup`
- `EmptyState`, `FormAlert`, `ErrorAlert`, `LoadingSpinner`
- `StatusBadge`, `AdminPageHeader`, `AdminStatCard`, `AdminStatsSection`
- `AdminSidebar`, `AdminDataTable`
- `MapLocationPicker` (from `@/components/map/`)

### Global Components
- `ToastNotification` — mounted in `App.vue`, auto-shows toasts for all API calls
- `ConfirmDialog` — mounted in `App.vue`, use via `useConfirmStore().show({...})`

## Response Format
Backend uses `BaseApiController` methods:
- `successResponse(data, message, status, pagination?)` → `{ success: true, message, data, pagination? }`
- `createdResponse(data, message)` → HTTP 201
- `deletedResponse(message)` → HTTP 200
- `errorResponse(message, status, errors)` → `{ success: false, message, errors }`
- `notFoundResponse(message)` → HTTP 404

Exception handler (in `bootstrap/app.php`):
- 422: `{ success: false, message: 'Validation failed.', errors }`
- 401: `{ success: false, message, errors: [] }`
- 404: `{ success: false, message: 'Resource not found.', errors: [] }`

## Toast System
- `src/stores/toast.js` — Pinia store (`useToastStore`) with `success()`, `error()`, `info()`, `warning()`
- `src/components/ui/ToastNotification.vue` — floating stack at top-center, auto-dismiss 4s
- `src/api/client.js` — `setToastHandler()` wires the store; auto-fires:
  - **Success toast** for all non-GET calls with `payload.message`
  - **Error toast** for ALL errors (including 422 validation)
- Components should **NOT** call `toast.success/error` directly for API operations — the global handler covers it

## Confirm Dialog
- `src/stores/confirm.js` — Pinia store (`useConfirmStore`) with `show({title, message, confirmText, cancelText, variant}) -> Promise<bool>`
- `src/components/ui/ConfirmDialog.vue` — modal overlay with icon + message + buttons
- Usage: `if (!(await confirmStore.show({ message: '...' }))) return`

## Form Error Handling
- `src/composables/useFormErrors.js` — provides `handleSubmitError(error)` that sets field errors from 422 responses
- Every form component must `defineExpose({ handleSubmitError })`
- Every parent page must:
  1. Use `<FormComponent ref="formRef">` in template
  2. In catch block: `formRef.value?.handleSubmitError(err)`
  3. Remove `FormAlert` for save/submit errors (toast covers it)
- This pattern applies to: `EstateForm`, `AdminEstateForm`, `AgentForm`, `AdminAgentForm`, `AdminCityForm`, `AdminPlaceForm`, `AdminCompanyForm`, `AdminUserForm` and their parent pages
- Inline forms (Login, Register) use `useFormErrors` directly — no ref needed

## Social Links
- `SOCIAL_PLATFORM_OPTIONS` in `src/config/admin.js` — 8 platforms
- `getPlatformStyle(platform)` returns `{ color, icon }` for brand-colored display
- Three places with social links UI:
  1. `SocialLinksManager.vue` — standalone manager (brand-colored cards, inline editing)
  2. `EstateForm.vue` — dynamic links section in company estate form
  3. `AdminEstateForm.vue` — dynamic links section in admin estate form
- Backend: `SocialLinkService@syncFromRequest` accepts `links` array `[{platform, url}]` or legacy `facebook`/`instagram` fields
- Both store endpoints use `syncFromRequest` (not `syncLegacyFields`)
- Validation rules include `links`, `links.*.platform`, `links.*.url`

## Estates
- Company form: `EstateForm.vue` — city cascade, `MapLocationPicker`, social links, videos/ads uploads
- Admin form: `AdminEstateForm.vue` — same fields + owner autocomplete + status
- Both support investment indicators section
- Company list: `EstatesPage.vue` uses `useCompanyEstatesList` composable (debounced search + status/type/kind filters)
- Backend `myEstates` supports `search`, `status`, `type_text`, `kind_text` query params
- Company submits FormData; admin submits JSON + optional files

## Agents
- Company agents list: `AgentsPage.vue` — debounced search + `usePaginatedList`
- Company agent form: `AgentForm.vue` — search by email/phone, user selection, profile image upload
- Admin agent form: `AdminAgentForm.vue` — full CRUD

## Key Conventions
- No comments in code unless essential
- Arabic labels in UI
- English messages from backend appear in toasts
- All `confirm()` replaced with `useConfirmStore`
- All `alert()` for errors replaced with global toast handler
