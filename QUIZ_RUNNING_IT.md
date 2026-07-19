# Running the quiz on a Sunday

Everything below is built and tested. Nothing here needs a developer.

## Before the service

1. **Dashboard → Quizzes → New Quiz.** Give it a title. The defaults (20s a
   question, answer shown for 6s, 1000 points) are sensible; change them if you
   want a faster or slower game.
2. **Write the questions.** Two to four answers each, tick the correct one. A
   question can override the timing or points if one needs longer.
3. That is the whole preparation. Questions are locked once the quiz is opened,
   because scores stop meaning anything if the questions change underneath
   people who have already answered them.

## On the day

**On the projector machine**, open the quiz from Dashboard → Quizzes → *Run it*,
then follow **Open the projector screen**. Put that window full screen. It needs
no login, so it does not matter whose machine it is.

**On your phone**, open the same *Run it* page. You have three controls and you
will normally use one:

- **Open for joining** — puts the code on the screen and lets people in.
- **Start the quiz** — the only thing you have to press. Questions then advance
  by themselves, all the way to the final scores.
- **Pause** / **End now** — for when the room needs holding, or you have run out
  of time.

The leaderboard on your phone updates as they play. You can remove a player with
the × beside their name if a display name is a problem.

## What the congregation does

Open the LifePointe app → **More** → **Join a Quiz** → type the code.

Members are in immediately under their own name. Anyone without an account can
play by typing a name — and if they sign in afterwards, on that same phone, the
score follows them onto the new account. That is the moment worth pointing at
from the front: *"if you want to keep your score, sign in."*

## Practising

```bash
php artisan quiz:rehearse --players=100          # a full room, waiting in the lobby
php artisan quiz:rehearse --players=100 --play   # plays a whole quiz through
php artisan quiz:rehearse --cleanup              # removes practice quizzes only
```

Practice quizzes are titled `[Rehearsal] …`, and cleanup will not touch anything
else.

## If something goes wrong

- **A phone locked, or lost signal.** It catches up by itself within two
  seconds of coming back. Nothing to do.
- **Someone joins late.** They play from the question the room is on. They
  simply have nothing for the ones already gone.
- **The projector page went blank or was closed.** Reopen the same URL. The
  quiz is unaffected — the screen only ever displays it.
- **You need to stop.** *Pause* holds the clock exactly where it is; the time
  lost does not eat into anyone's question.

## One thing to fix first

Your own member profile has no first name or surname saved, so you would appear
on the leaderboard as "Member". Worth filling in from Profile → Edit before
Sunday.

## What is deliberately not there

- **Images in questions** — text only, as agreed.
- **A live connection.** It polls every one and a half to two seconds instead.
  At a hundred people that is comfortably enough, and it recovers from a dropped
  signal in a way a socket does not. Worth revisiting only if the room grows a
  lot.
