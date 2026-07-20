# Getting content onto production

## Read this first

**Never run `php artisan db:import` against production.** Its own description
says "DANGER: Replaces existing data!", and it means it. Production holds the
real members — the people who have actually signed up — and local is a stale
copy taken at some point in the past. Importing local over the top would delete
every account created since.

The direction of truth runs both ways, and that is the whole point:

| | Where the real version lives |
|---|---|
| Members, users, accounts, notes, small groups | **Production** |
| Reading plan, giving accounts, projects, seeded events | **This repository** |

So nothing gets copied between databases. Production keeps its own people, and
the content is rebuilt from the repository by running commands there. Everything
below is safe to re-run.

## 1. Deploy the branch

`feat/in-service-quiz` needs to be merged and deployed before any of this. As of
writing, production is still on `main` — `POST /api/quiz/join` returns 404 there,
so the quiz does not exist yet, and neither does the projector screen.

Laravel Cloud runs migrations on deploy. That creates the five quiz tables and
adds the online-meeting and video columns.

## 2. The reading plan

`database/data/bible-in-a-year.csv` holds all 365 days with their readings and
study questions, exported from the working copy. Run on production:

```bash
php artisan reading-plan:import database/data/bible-in-a-year.csv \
  --annual --publish --default \
  --name="Bible in a Year" \
  --attribution="One Year Bible readings courtesy of bibleinayearonline.com"
```

Running it twice is now refused rather than silently making a second plan — add
`--replace` if you genuinely mean to update the one that is there. `--replace`
matches days on their number and updates them in place; it does **not** delete
and recreate them, because member reading progress hangs off day ids with a
cascade and would go with them.

## 3. Giving accounts and projects

```bash
php artisan db:seed --class=LifePointeGivingSeeder
```

Safe to re-run — every row is an `updateOrCreate`. It also sets the giving
declaration on branch 1. **Update the projects in this seeder each quarter**;
they are currently marked Q1 2026.

## 4. Events — check before running

```bash
php artisan db:seed --class=LifePointeEventsSeeder
```

This one needs a look first. It creates nine recurring events, and if production
already has any of them entered by hand, you will end up with both — exactly
what happened locally, where "UnFiltered Thursday" exists twice. Check what
production already has, and if the events are already there, skip this and add
only the missing online links by hand.

## 5. By hand

- The one sermon in local — quicker to re-enter than to migrate.
- Your own member profile has no first name or surname, so you appear as
  "Member" on quiz leaderboards. Profile → Edit.

## 6. Check it worked

```bash
curl -s -o /dev/null -w "%{http_code}\n" -X POST \
  -H "Accept: application/json" https://dash.lifepointeng.org/api/quiz/join
```

`422` means the quiz is deployed and validating. `404` means it is not there yet.

Then sign in on the app, confirm the Read tab shows today's passage, and open a
quiz's projector link from Dashboard → Quizzes → Run it.
