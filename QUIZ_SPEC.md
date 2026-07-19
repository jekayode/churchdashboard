# In-Service Quiz — my understanding

Draft for Emmanuel to correct and add to. Nothing here is built yet.

## What I think we're building

A Kahoot-style quiz run live during a service. The pastor starts a quiz and a
join code goes up on the screen. Members open the app, enter the code, and answer
questions on their phones. The big screen shows the question and a leaderboard
that updates as people answer. The pastor ends the game and the winners are
shown with their points. Afterwards a member can look back at quizzes they played
and where they placed.

## Three surfaces, not one

This is the thing that makes it more than a normal feature. The same quiz is seen
in three places at once, and they must agree with each other in real time.

| Surface | Who | Where it runs |
|---|---|---|
| **Host console** | Pastor / service host | Dashboard on a laptop (assumed — needs confirming) |
| **Player** | Members | The mobile app |
| **Big screen** | Everyone | A browser page on the projector machine |

The big screen is the one most easily forgotten. It has no keyboard, is read from
30 metres away, and nobody can fix it mid-service. It needs its own layout with
very large type, and it must survive a refresh without losing the game.

## Lifecycle

```
draft ──► lobby ──► question_active ──► question_reveal ──┐
                          ▲                               │
                          └───────────── next question ◄──┘
                                                          │
                                                     finished
```

- **draft** — questions being written; no code yet
- **lobby** — code is live, players joining, nobody can answer yet
- **question_active** — a question is showing, answers accepted, timer running
- **question_reveal** — answers closed, correct option shown, leaderboard updated
- **finished** — final standings; the quiz becomes read-only history

The pastor drives every transition. Nothing advances on its own except the
question timer closing answers.

## Data model (first cut)

- `quizzes` — branch, title, join code, status, current question, settings
  (seconds per question, base points, whether speed affects score), timestamps
- `quiz_questions` — quiz, position, text, optional image, time limit, points
- `quiz_options` — question, text, correct flag, position (2–4 options)
- `quiz_participants` — quiz, member (nullable if guests allowed), display name,
  running score, joined at
- `quiz_answers` — question, participant, option, response time in ms, correct
  flag, points awarded

`quiz_answers` is the audit trail: score is derived from it, never edited
directly, so a disputed result can always be explained.

## Scoring

Kahoot's model, which I'd suggest copying because people already expect it:

```
correct answer → points = base × (1 − (response_ms / time_limit_ms) × 0.5)
wrong answer   → 0
```

So a correct answer is worth between 50% and 100% of the base, depending on
speed. Fast and right beats slow and right; guessing early is not rewarded.

**The response time must be measured on the server** — from when the question was
broadcast to when the answer arrived. If the phone reports its own timing,
winning the quiz is a matter of editing one number.

## Realtime

Laravel Reverb (first-party WebSockets, already supported on Laravel Cloud).

Broadcast events: `ParticipantJoined`, `QuestionStarted`, `QuestionClosed`,
`LeaderboardUpdated`, `QuizEnded`.

Two rules that matter under load:

1. **Do not broadcast per answer.** With 200 people answering in the same two
   seconds, that is 200 events to 200 subscribers. Aggregate and broadcast the
   leaderboard on question close, or on a throttle.
2. **State must be recoverable by fetching, not only by listening.** A phone that
   locks, loses signal, or joins late must be able to ask "what's happening now?"
   and get the current question and its remaining time. Anything that relies on
   having heard an earlier event will break in a real room.

**Fallback:** if a persistent WebSocket process is a problem on the host, this
also works with 2-second polling. Less elegant, far simpler operationally, and at
congregation scale it is fine. Worth keeping as the safety option.

## Things that will go wrong in a real service

- **Late joiners.** Someone enters the code during question 3. Do they play from
  there with zero for the missed questions, or are they refused?
- **Reconnects.** A phone locks and rejoins. It must land on the current question
  without being able to re-answer an earlier one.
- **Double answers.** One answer per participant per question, enforced by a
  unique constraint, not by hiding the button.
- **Answers after time.** Rejected by the server on timestamp, not trusted from
  the client.
- **Ties.** Decide now: shared position, or broken by total response time?
- **Name misuse.** People type display names in front of the whole church.
  Needs at minimum a length limit and a profanity filter, and ideally the ability
  for the host to remove a participant mid-game.
- **The projector losing the page.** Refresh must restore the live state.

## Load

The risky moment is everyone answering at once. 200 people in a two-second window
is roughly 100 writes a second, plus the broadcast fan-out.

That is not a lot for Laravel — but it has never been tested here, and it fails
in front of the whole church rather than in a log. Before it runs in a main
service it needs a simulated run at the real congregation size.

## Decisions taken (2026-07-19)

- Pastor writes the quiz, its questions and the correct answers.
- Each quiz has its own join code.
- **The pastor's live role is minimal**: start the quiz, watch the leaderboard.
  He is not tapping "next question" between every question.
- The projector is where everyone watches. Kahoot-ish, kept minimal.
- Members sign in to play — the quiz is partly there to drive app adoption.
- **Guests may play by entering a name.** Seeing your score history requires an
  account, which is the reason to sign up.

### What "minimal pastor" implies

If the pastor only starts the quiz, questions must advance on their own. That is
a large simplification and it removes most of the live risk — but it also means
nobody can hold the room. I'd suggest auto-advance as the behaviour, with pause
and skip available on the host screen without being required.

It also unlocks something better: once a quiz has started, the whole timeline is
deterministic. Question 3 begins at `started_at + q1 + q2`, and every client can
work out "question 3, seven seconds left" from a single fetch. Realtime becomes a
refinement rather than a dependency, and a dropped connection self-heals on the
next poll.

### Guest play, and claiming a score later

A guest is given a token that stays on the device. If they sign up afterwards,
the app presents that token and their earlier participations are attached to the
new member. Otherwise "sign in to keep your score" is an empty promise — the
score is already gone by the time they read it.

### Host and projector belong on the web, not in the app

An app change needs a rebuild and everyone reloading. A web page changes
instantly. For something going live in days and running in front of a
congregation, the host console and the projector screen should both be pages in
the dashboard — mobile-friendly for the pastor's phone, full-screen for the
projector. Only the player experience needs to be in the app.

## Open questions

1. **How many people, realistically, in the room?** This sets what we rehearse
   against, and it is the only number that decides whether polling is enough.
2. **Do questions need images?** Text-only is materially quicker to build.
3. **Should the pastor be able to pause, skip, or remove someone mid-game?**
   My recommendation is yes for pause and remove, since names appear on the
   projector in front of everyone.
4. **Ties** — shared position, or broken by who answered faster overall?
5. **Does the code need to be chosen** (e.g. `SUNDAY`) or is a generated code
   fine? Generated avoids clashes and typos.
6. **What is the event in a few days** — a one-off, or the start of a regular
   thing?

## What I'd suggest given the timeline

A live multiplayer game in front of a congregation is the highest-risk thing on
this roadmap, and "a few days" is tight for the full version. I would rather ship
something narrower that certainly works than the whole design that might not.

Suggested order:

1. Questions admin, host console, player join and answer, server-side scoring,
   leaderboard on the big screen — the whole loop, kept plain
2. A rehearsal at real scale before it is used
3. Polish, past-quiz history for members, images, sounds, animation

If the schedule gets tight, the honest fallback is to run the first outing with a
smaller group — youth, or one service rather than both.
