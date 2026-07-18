# LifePointe Mobile App — Staged Roadmap

Each stage has a **build scope** and a **test gate**. We do not move to the next stage until the test gate passes.

**Stack:** Laravel 12 + Sanctum (existing) · Expo (React Native) + TypeScript + NativeWind · Laravel Reverb (Phase 2, live quiz) · Spatie Media Library + S3 storage · Expo Push Notifications · EAS Build.

---

## Stage 0 — LifePointe branding on the web dashboard
*Independent of everything else; small visible win.*

**Build**
- [ ] Map LifePointe tokens (colors, typography, spacing) from the Claude Design project into `tailwind.config.js`
- [ ] Load brand fonts (Amaranth, Quicksand, Pacifico)
- [ ] Swap logo assets into `public/` and layouts
- [ ] Restyle base layout (`app.blade.php`) — page background cream, primary actions burnt orange

**Test gate**
- [ ] Dashboard, member portal, and public pages render with brand colors/fonts
- [ ] Check mobile viewport (responsive) and contrast on primary buttons
- [ ] No regressions in existing pages (spot-check each role dashboard)

---

## Stage 1 — Member-facing API layer (`me/*`)
*The mobile app's contract with the backend. Sanctum token auth already works (`AuthController::login` issues Bearer tokens).*

**Build**
- [ ] `GET /api/me` — profile (member + user + branch)
- [ ] `PUT /api/me/profile` — update own profile
- [ ] `GET /api/me/events` — upcoming events for my branch; `POST /api/me/events/{event}/register` + cancel
      *(`/api/events/{event}/register` already accepts `custom_fields` — app renders the pastor's form-builder JSON natively)*
- [ ] `GET /api/me/small-group` — my group, next meeting, members
- [ ] **Small group self-join flow (new):** `GET /api/me/small-groups/available` → `POST /join-request` → leader approve/decline in dashboard (join_requests table)
- [ ] API Resources for consistent JSON shapes; rate limiting; proper 401/403 responses

**Test gate**
- [ ] PHPUnit feature tests per endpoint (auth required, branch scoping, cannot see other members' data)
- [ ] Manual smoke test with curl/Postman using a real token from `/api/auth/login`
- [ ] `php artisan test` fully green

---

## Stage 2 — Sermon library (backend + pastor admin UI)

**Build**
- [ ] Models + migrations: `Series`, `Sermon` (title, preacher, date, series_id, description), `SermonPassage` (book/chapter/verses, order)
- [ ] Media via Spatie: audio recording, slides (PDF/images) — configure S3-compatible disk
- [ ] Pastor web UI: create/edit sermon, upload recording + slides, attach passages
- [ ] Member API: `GET /api/sermons` with filters (series, preacher, search, sort), `GET /api/sermons/{id}` incl. media URLs + passages

**Test gate**
- [ ] Feature tests: CRUD, filters, authorization (only pastors create)
- [ ] Upload a real sermon recording + slide deck; confirm playback URL works and large files stream (no memory blowup)
- [ ] Passages return in order

---

## Stage 3 — Bible-in-a-year reading plan + streak

**Build**
- [ ] Models: `ReadingPlan`, `ReadingDay` (date, passages JSON, study_questions richtext), `MemberReadingProgress` (member, day, completed_at)
- [ ] Excel bulk import (Maatwebsite — already installed): 365 days in one upload
- [ ] Streak logic service: current streak, longest streak, timezone-safe day boundaries
- [ ] Member API: `GET /api/me/reading/today`, `GET /api/me/reading/{date}`, `POST /api/me/reading/{date}/complete`, `GET /api/me/reading/streak`
- [ ] **Decision:** Bible text source — API.Bible integration vs references-only v1

**Test gate**
- [ ] Import the real 2026 plan spreadsheet end-to-end; spot-check July 17 renders correctly (passages + "What Now?" questions)
- [ ] Unit tests for streak edge cases: missed a day, completed late at night, timezone boundary, completing past days
- [ ] Feature tests for all endpoints

---

## Stage 4 — Unified Notes

**Build**
- [ ] Polymorphic `Note` model (`notable`: Sermon, ReadingDay, or null = standalone)
- [ ] API: CRUD `/api/me/notes`, filter by type, include context (sermon title / reading date)
- [ ] Notes are strictly private to the member

**Test gate**
- [ ] Feature tests: create note on sermon, on reading day, standalone; member A can never read member B's notes
- [ ] "All my notes" endpoint returns mixed types with correct context labels

---

## Stage 5 — Expo app skeleton

**Build**
- [ ] Expo + TypeScript project, NativeWind theme generated from LifePointe tokens
- [ ] Auth flow: login → store Sanctum token (SecureStore) → auto-refresh → logout
- [ ] Tab navigation shell: Home · Bible · Sermons · Events · Profile
- [ ] API client (axios + TanStack Query) pointed at the Laravel API

**Test gate**
- [ ] Log in with a real member account on **Expo Go on a physical phone** and on iOS Simulator
- [ ] Token survives app restart; logout revokes token (verify in `personal_access_tokens` table)
- [ ] Wrong password / expired token handled gracefully

---

## Stage 6 — App features (one at a time, each with its own gate)

**6a. Events**: list, detail, register/cancel → *gate: RSVP from phone appears in dashboard admin*
**6b. Bible reading**: today's reading, date picker, passages + study questions, mark-as-read, streak display → *gate: streak increments correctly across two real days*
**6c. Sermons**: browse/filter, detail with audio player + slides viewer, passages toggle → *gate: audio plays in foreground + background; slides render*
**6d. Notes**: add/edit note from sermon + reading screens, "My Notes" hub → *gate: notes persist and appear in hub with context*

---

## Stage 7 — Push notifications

**Build**
- [ ] Expo push tokens stored per device on the backend
- [ ] Laravel scheduled job: daily reading nudge (respect quiet hours / opt-out)
- [ ] Event reminder pushes; streak-at-risk nudge
- [ ] Notification preferences screen in app

**Test gate**
- [ ] **Physical devices required** (push doesn't work on iOS Simulator): receive daily nudge on both an iPhone and an Android phone
- [ ] Tapping notification deep-links to the right screen
- [ ] Opt-out actually stops sends

---

## Stage 8 — Beta + store release

**Build**
- [ ] Apple Developer account ($99/yr) + Google Play Console ($25 one-time)
- [ ] EAS Build (cloud) → TestFlight (iOS) + Play Internal Testing (Android)
- [ ] App icons/splash from LifePointe brand; store listings
- [ ] Privacy policy page (required by both stores)

**Test gate**
- [ ] 10–20 real members beta test for 1–2 weeks; collect crashes (Sentry) + feedback
- [ ] Fix top issues → submit for store review

---

## Phase 2 (after launch)

| Feature | Notes |
|---|---|
| Business directory in app | v1: in-app webview of existing /biz pages (fast). Phase 2: native via new `/api/directory` + `/api/me/businesses` endpoints |
| Prayer requests (member) | Extend existing guest prayer models |
| Giving (Paystack) | Payments — own mini-project, compliance + reconciliation |
| Sermon transcription | Church-side, once per sermon (Whisper/Deepgram) |
| **Live in-service quiz** | Laravel Reverb; load-test at congregation scale; soft-launch in youth/small group before main service |
| Quiz history | Free once quiz stores results |

---

## faith-scan-hub integration (lifepointe.netlify.app) — ⏸️ ON HOLD

**Deferred until Emmanuel speaks with the other pastor. Do not build against it or borrow its UX until then.** Code available locally at `~/Herd/faith-scan-hub` for reference when the time comes.

Separate Lovable/Supabase app by another pastor. **Not just check-in** — it has quizzes, sermon notes/slides, scripture streaks, forms, feed, badges (validates our feature list). Strategy decided:

1. **Laravel is the single source of truth** (members, branches, reporting already live here)
2. **Bridge (short-term):** Supabase webhook/edge function pushes each `check_ins` row to a Laravel endpoint — scan hub keeps working, data lands in our DB
3. **Native (Stage 6):** check-in moves into the mobile app — member QR + door scan. Laravel already has the machinery: `EventController::checkIn()`, QR generation (`app/Support/QrCodePngViaGd.php`), public check-in page (`resources/views/public/check-in.blade.php`) — built but never used
4. **Requires a conversation with the other pastor** — bring their best UX (quiz, streaks, check-in) into the official app; export Supabase history into Laravel when ready

---

## Test suite health — ✅ GREEN (2026-07-18)

Suite was **42 failed / 332 passed** at session start. Now **0 failed / 374 passed** (5 skipped).

### Real product bugs found and fixed (not just stale tests)

1. **Public registration was completely broken (500 on every signup).** `RegisteredUserController::redirectBasedOnRole()` redirected to route names (`member.dashboard`, `pastor.dashboard`, `admin.dashboard`…) that were **never registered**. Only a single `dashboard` route exists, and it already resolves the role-specific view internally. Now redirects there.
2. **Pastors could get 403 on their own branch's events.** `User::getPrimaryBranch()` used an unordered `->first()` over role pivots, so a user holding a role both globally (`branch_id` null) and branch-scoped could resolve to *no* branch. Now prefers the branch-scoped pivot.
3. **Page headings never rendered anywhere.** `<x-sidebar-layout>` accepted a `header` slot and `title` prop but output neither. Both now render (and `<title>` includes the page title).
4. **`BranchReportToken::createForBranch()` returned an invalid token.** It never set `is_active`, so the in-memory model had `is_active = null` and `isValid()` returned false even though the DB default made the row active. Now set explicitly.
5. **Member import hardening:** `branch_id` is now required (members must have a branch), and a branch pastor targeting another branch gets a 403 instead of being silently redirected to their own branch.

### Stale tests brought in line with current behaviour

- `AuthenticationTest` — API responses are `{success, message, data:{user, token}}`; register requires `branch_id`/`role_id`/`device_name`, login requires `device_name`; revoke-all is `/auth/logout-all`
- `MemberControllerTest` — members use `first_name`/`surname` (not `name`), `phone` required
- `ImportExportTest` (15) — export & template now stream file downloads (removed `Storage::fake` which broke the controller's real-path lookup); import returns `summary{...}` and 422 on any failed row; validate returns `{valid, message, preview}`; stats returns `members`/`exports`/`user_context`
- `PasswordResetTest` — app sends custom `ChurchPasswordResetNotification`
- `PerformanceControllerTest` — **time-bomb**: reports hardcoded to `2025-01-15` while the endpoint defaults to the *current* year. Now year-relative so it won't rot again.
- `MemberImportWithWelcomeEmailTest` — import needs auth; events use `is_public` (not `is_published`); job is `SendBulkAccountSetupEmailsJob`
- `MinisterPermissionsTest`, `ProjectionServiceTest`, `DepartmentControllerTest`, `GuestRegistrationAttemptTest`, `ProfileTest` (User uses `SoftDeletes`, so `fresh()` bypasses the scope — use `assertSoftDeleted`)

---

## Testing setup (reference)

- **Backend:** PHPUnit (`php artisan test`) — already configured in this repo
- **Day-to-day mobile dev:** **Expo Go on your real phone** (scan QR) — fastest loop, works for iPhone + Android simultaneously, no simulators needed
- **iOS Simulator:** via Xcode (Mac-only advantage) — good for many screen sizes; **no push notifications**
- **Android Emulator:** via Android Studio — can receive push (with Play services image)
- **Beta:** TestFlight + Play Internal Testing via EAS cloud builds — no local Xcode/Android Studio required to build
