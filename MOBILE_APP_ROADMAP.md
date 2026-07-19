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

## Stage 1 — Member-facing API layer (`me/*`) — ✅ DONE (2026-07-18)
*The mobile app's contract with the backend. Sanctum token auth already works (`AuthController::login` issues Bearer tokens).*

**Built**
- [x] `GET /api/me` — profile (member + branch); 404 when no member profile is linked
- [x] `PUT /api/me/profile` — self-service update. Allow-list only: branch, member_status, growth_level and teci_status are **not** self-editable
- [x] `GET /api/me/events` — upcoming events for my branch, paginated, each flagged `is_registered`
- [x] `GET /api/me/events/registered` — my registrations
- [x] `POST|DELETE /api/me/events/{event}/register` — register / cancel. Enforces branch match, capacity, duplicate registration, and `registration_type: none`
- [x] **Form-builder round-trip:** the event payload carries the pastor's `custom_form_fields` so the app renders the form natively, and submissions are validated server-side against that same definition (types + select options)
- [x] `GET /api/me/small-groups` — my groups with members
- [x] `GET /api/me/small-groups/available` — active groups in my branch I'm not in, with my `join_request_status`
- [x] **Small group self-join flow (new):** `POST /api/me/small-groups/{group}/join-request` → leader reviews via `GET /api/small-group-join-requests` + `/approve` `/decline`. Approval adds the member to the group in a transaction.
- [x] New table/model: `small_group_join_requests` (pending/approved/declined, message, response_note, reviewer, reviewed_at)
- [x] API Resources for consistent shapes (`MemberProfileResource`, `MemberEventResource`, `MemberSmallGroupResource`)

**Test gate — passed**
- [x] 33 feature tests across 4 classes: auth required on every route, branch scoping, cross-member isolation, and review authorization (leader ✓, branch pastor ✓ own branch only, applicant ✗ own request, stranger ✗)
- [x] Manual smoke test with a real Sanctum token against the dev server — profile, events, and available-groups all return correct live data; unauthenticated returns 401
- [x] `php artisan test` fully green — **407 passed**, 0 failed

**Notes for the app**
- Responses are `{success, data, …}`; list endpoints add `meta` for pagination
- `meeting_time` is returned as `HH:MM` (the column is a TIME, so no date is leaked)

---

## Mobile app design — reviewed 2026-07-18

Design lives in the Claude Design project (`prototype/` folder); handoff bundle is
`LifePointe Design System-handoff.zip` in the repo root (gitignored — 16 MB).

**Decision: implement the design at Stage 5, not before** — there is no app to
implement into until then, and the screens depend on APIs from Stages 2–4. But the
design has already been reviewed, and Stages 2–4 below are revised to match it.

**App shell:** 5 tabs — Home · Watch (sermons) · Read (devotional/plan) · Give · More.
Overlay screens: sermon player (supports a `live` flag), event detail, events list,
groups, profile, visit (newcomers), plus an onboarding flow.

**Decisions taken:**
- **Give stays Phase 2.** The tab exists in the shell from v1 but links out to the
  existing giving page (webview). Full Paystack integration lands after launch so a
  payments sub-project cannot delay the app.
- **One reading-plan model covers both.** A `ReadingPlan` may be a 365-day
  Bible-in-a-year (passage references + "What Now?" questions, imported from the
  existing WhatsApp content) *or* a short written devotional series like
  "Rooted — Day 12 of 21" (focus verse, body paragraphs, reflection prompt).
  Members follow a plan; the streak spans whichever plan they are on.

**Gaps the design exposed (folded into the stages below):**
- Cover images are needed on sermons, series, events and small groups. Today only
  `Member` and `Business` use Spatie media — `Event` and `SmallGroup` have none.
- Sermons need duration, speaker, series, cover image and a `is_live` flag.
- Series/events/groups carry a `tone` accent colour (orange/purple/amber/lemon).
- Events should expose **spots remaining** (capacity − registrations).
- Profile stats imply **saved/bookmarked sermons** and a per-year giving total.
- Groups display an **area** (Sangotedo, Ajah, Ikate) — confirm whether the existing
  `location` field covers this or a separate field is needed.

---

## Stage 2 — Sermon library (backend + pastor admin UI) — ✅ DONE (2026-07-18)

Backend, member API and the pastor admin UI are all in. Pastors can create and
publish sermons from the dashboard, and what they publish reaches the app.

**Pastor UI:** `pastor/sermons` (index with search + series filter) and a create/edit
form covering details, cover/recording/slides upload, a repeatable Bible-passage
builder, draft-vs-published, and a live-stream toggle. `SermonPolicy`/`SeriesPolicy`
scope everything to the pastor's branch; network-wide sermons stay with super admins.

**Test gate — passed:** 17 pastor-UI tests (authorization, validation, uploads,
passage replacement, branch scoping, delete) plus an end-to-end check that a
published sermon appears in `/api/me/sermons` and an unpublished one does not.
Suite: **442 passed**.

**Two bugs caught while testing, both real:**
- `required_if:is_live,1` never fired because `prepareForValidation` casts `is_live`
  to a boolean, which never equals the string `"1"` — live sermons could be saved
  with no stream URL. Now `required_if_accepted:is_live`.
- `[x-cloak]` had **no CSS rule anywhere**, so Alpine never hid cloaked elements.
  This affected 4 pre-existing pages too (builders form, roles, guests, permissions),
  all of which flashed hidden content on load. Fixed once in `app.css`.

### Original plan (for reference)

**Build**
- [ ] Models + migrations: `Series` (name, description, cover, tone), `Sermon` (title,
      preacher/speaker, date, series_id, description, duration, `is_live`, tone),
      `SermonPassage` (book/chapter/verses, order)
- [ ] Media via Spatie: audio/video recording, slides (PDF/images), **cover image** on
      both Sermon and Series — configure S3-compatible disk
- [ ] **Add Spatie media to `Event` and `SmallGroup`** (cover images) — required by the design
- [ ] Saved/bookmarked sermons per member (drives the "Messages saved" profile stat)
- [ ] Pastor web UI: create/edit sermon, upload recording + slides + cover, attach passages
- [ ] Member API: `GET /api/sermons` with filters (series, preacher, search, sort),
      `GET /api/sermons/{id}` incl. media URLs + passages; `POST/DELETE /api/me/sermons/{id}/save`
- [ ] Add `spots_remaining` to the member event payload

**Test gate**
- [ ] Feature tests: CRUD, filters, authorization (only pastors create), save/unsave
- [ ] Upload a real sermon recording + slide deck; confirm playback URL works and large files stream (no memory blowup)
- [ ] Passages return in order

---

## Stage 3 — Reading plans + streak — ✅ BACKEND DONE (2026-07-18)

One model serves both plan types, per the earlier decision. The church's real
**Bible in a Year** is imported: 365 days, published, set as default.

**Data**
- `reading_plans` — annual (keyed by month-day, so one year's content serves
  every year) or finite; `passages` or `devotional` type
- `reading_days` — OT / NT / Psalm / Proverbs kept apart for the app's sections,
  plus both "What Now?" questions and devotional fields
- `member_plan_enrolments`, `member_reading_progress` (stores the member's own
  local completion date, so streaks are timezone-fair)

**Importer** — `reading-plan:import` (CSV or XLSX), matching headers by name and
locating the header row. `--dry-run`, `--annual`, `--publish`, `--default`,
`--attribution`.

**Member API** — `me/reading/today`, `/plan` (windowed day list with done/today
flags), `/days/{day}` (optional `?with_text=1`), complete/uncomplete, `/streak`,
`/plans` and enrol.

**Streak engine** — current and longest, spanning plans. Yesterday still counts,
so a member who hasn't read *yet today* keeps their streak. Client-supplied
dates are clamped to ±1 day so the streak can't be gamed.

**Verified against the real plan, not fixtures:** today resolves to day 199
"July 18" with its four readings, two questions and attribution; completing it
returns a streak of 1; Proverbs 19:17 fetches live NLT text with its copyright.
19 tests here; suite **495 passed**.

### Still to do for Stage 3
- [ ] **Pastor UI to write/edit reading days** — the church is replacing the
      imported "What Now?" questions with its own, so days must be editable
- [ ] Leap-year note: a 365-day annual plan resolves 29 Feb to 28 Feb

## Stage 4 — Unified Notes — ✅ DONE (2026-07-19)

One polymorphic `Note` model serves sermon notes, reading notes and standalone
personal notes, so the app can offer a single "My Notes" hub rather than three
disconnected lists.

**API**
- `GET /api/me/notes` — the hub. Filter by `type` (sermon / reading / personal),
  free-text search, paginated, with per-kind counts for the app's filter tabs.
- `POST /api/me/notes` — standalone, or attached via `type` + `notable_id`
- `GET /api/me/notes/for/{type}/{id}` — the note panel on a sermon or reading screen
- `GET|PUT|DELETE /api/me/notes/{note}`

Each note carries a **context label** (the sermon's title, the reading day's
date), which is what makes the hub readable instead of a wall of text.

**Privacy** — notes are strictly personal. Another member's note returns **404,
not 403**, so the API never confirms it exists, and a branch pastor gets nothing
from `me/*` either: those routes are always the caller's own data regardless of
role. Both are covered by tests.

**Test gate — passed:** 16 tests (all three kinds, context labels, filters,
search, per-item lookup, edit/delete, and four separate privacy assertions).
Smoke-tested live against real data — a note on the "WISDOM FOR INCREASE" sermon
and on reading day "July 18" both returned correct context. Suite: **529 passed**.

## Stage 5 — Expo app skeleton (implement the design here)

**Build**
- [ ] Expo + TypeScript project, NativeWind theme generated from LifePointe tokens
- [ ] Auth flow: login → store Sanctum token (SecureStore) → auto-refresh → logout
- [ ] Tab navigation shell per the design: **Home · Watch · Read · Give · More**
      (Give links out to the existing giving page until Phase 2)
- [ ] Onboarding flow + light/dark theme support (the prototype ships both)
- [ ] Port the prototype screens (`prototype/screens1-3.jsx`, `onboarding.jsx`, `kit.jsx`)
      to React Native — match the visuals, not the prototype's internal structure
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

## Security advisories — ✅ CLEARED (2026-07-18)

`composer audit` reported **12 advisories across 8 packages** (all pre-existing,
surfaced while installing the R2 adapter). Now reports **none**.

Done on branch `chore/security-dependency-upgrades`.

The critical one needed far less than feared: **`phpoffice/phpspreadsheet`**
(CVE-2026-45034 — a patch bypass, reachable from the user-uploaded spreadsheets in
member import) affects `<=1.30.4`, and **1.30.6 is patched while still satisfying
`maatwebsite/excel`'s `^1.29.9`**. No move to PhpSpreadsheet 2.x+ and no
`maatwebsite/excel` 4.x-dev was required — the feared major upgrade was avoidable.

| Package | Change | Severity |
|---|---|---|
| phpoffice/phpspreadsheet | 1.30.4 → 1.30.6 | **critical** |
| spatie/laravel-medialibrary | 11.15.0 → 11.23.2 | high + medium |
| phpunit/phpunit | 11.5.22 → 11.5.56 | high (dev-only) |
| laravel/framework | 12.61.0 → 12.64.0 | medium |
| guzzlehttp/guzzle | 7.10.5 → 7.15.1 | medium ×2 |
| guzzlehttp/psr7 | 2.10.4 → 2.13.0 | medium |
| maatwebsite/excel | 3.1.64 → 3.1.69 | — |
| psy/psysh, symfony/yaml | updated | medium, low |

All within existing `composer.json` constraints — none widened.

**Verified beyond the suite:** exported 361 real members to `.xlsx` and read the file
back with PhpSpreadsheet (362 rows, values intact). The tests use CSV fixtures, but
Excel is PhpSpreadsheet's actual job, so it needed exercising directly.
Suite unchanged at 425 passed.

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
