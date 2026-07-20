# Running the quiz on a Sunday

Everything below is built and tested. Nothing here needs a developer.

## Before the service

1. **Dashboard → Quizzes → New Quiz.** Give it a title. The defaults (20s a
   question, answer shown for 6s, 1000 points) are sensible; change them if you
   want a faster or slower game.
2. **Write the questions.** Two to four answers each, tick the correct one. A
   question can override the timing or points if one needs longer.

   Or **Import questions**, if they already exist somewhere. Paste them in:

   ```
   1. Who led Israel across the Jordan?
   a) Moses
   b) Joshua *
   c) Caleb

   2. Where was Jesus born?
   a) Nazareth
   b) Bethlehem
   Answer: B
   ```

   Mark the answer with a `*` or an `Answer:` line — a letter, a number, or the
   words all work. Numbering, lettering and blank lines are optional. A CSV
   works too, with columns `question, a, b, c, d, answer`.

   It never guesses. A question with no answer marked, or two marked, is skipped
   and named, and the rest are imported — so you fix a couple by hand rather
   than hunting through the whole paste. Importing replaces whatever is there,
   and drops you in the editor to check it.
3. That is the whole preparation. Questions are locked once the quiz is opened,
   because scores stop meaning anything if the questions change underneath
   people who have already answered them.

## On the day

**The projector link exists as soon as the quiz does**, so you can send it to
whoever runs the screen days in advance. Find it on Dashboard → Quizzes →
*Run it*, under **Open the projector screen**. They open it, put it full screen,
and never sign in — it does not matter whose machine it is, and the link keeps
working when you open and start the quiz.

**On your phone**, open the same *Run it* page. You have three controls and you
will normally use one:

- **Open for joining** — lets people in. The code was set when you created the
  quiz and does not change.
- **Start the quiz** — the only thing you have to press. Questions then advance
  by themselves, all the way to the final scores.
- **Pause** / **End now** — for when the room needs holding, or you have run out
  of time.

The leaderboard on your phone updates as they play. You can remove a player with
the × beside their name if a display name is a problem.

## What the congregation does

**Put the join slide up before the quiz.** Dashboard → Quizzes → *Run it* →
**Join slide**. It shows a large QR, the short link and the code, and it can go
on the screen ten minutes early — it does not need the quiz to be open. On a
cinema screen a QR that size carries to most of the room, which makes scanning
the main way in rather than a novelty.

Anyone scanning before you open the quiz is told they are early and asked for
their name; they are then brought in automatically the moment you press *Open
for joining*. So the slide and your timing are independent, and the room is
already in when you start.

**Anyone at all** can also open `dash.lifepointeng.org/q/CODE` in their phone's
browser, type a name, and play. Nothing to install, works the same on an iPhone
as on an Android. This is the way in for a Sunday — the app is not on the App
Store, and asking a congregation to install anything to join in would lose most
of the room. Put that address on the screen next to the code.

**Signed-in app members** see a Join banner on the home screen the moment you
open the quiz for joining. One tap, no code, straight in under their own name.

The QR, the link and the code all live on the lobby screen too, for anyone
arriving after the slide has come down. And the host console has the QR with a
**Copy link for WhatsApp** button — worth posting to the groups, though not
everyone is in them, which is why the screen has to work on its own.

The web player asks for nothing but a name, on purpose. Asking for a password
on a phone during a service is exactly the friction it exists to remove — and
someone who installs the app and signs in afterwards keeps the score anyway, so
*"get the app and your score follows you"* is a real promise rather than a
slogan. It is also a much easier thing to say to someone who has just enjoyed
playing than to demand of them beforehand.

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
- **"Why can't I answer yet?"** Once someone has answered, they wait for the
  timer. That is deliberate — everyone has to start each question at the same
  moment, or answering quickly stops being worth anything. Their phone shows
  their position and how much of the room has answered while they wait. If it
  feels slow, drop the seconds per question rather than the wait.

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
