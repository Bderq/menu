---
name: memento-sync
description: Audit .memento/ against the actual state of the project and reconcile drift — memory files that describe a stack version, convention, business rule, or decision that no longer matches the code. Use when a lot of work has happened since .memento/ was last touched, when the user suspects memory is stale, or when they ask to "check memento", "sync memento", "audit memory vs project".
---

# Memento Sync

Detects and fixes drift between `.memento/` and the real project. Memory
files are a claim about reality made at some point in the past — code
moves on, and nobody remembers to update the docs. This skill closes that
gap. It does not run on every session; invoke it periodically or when
asked.

Read `.memento/claude.md` first to know which files exist, then check
each source below. Don't re-read the whole codebase — sample the same
kind of evidence `memento-init` used (manifests, configs, a few
representative files, recent git history).

## What to check

**`tech_stack.md`** — re-read manifest/lockfiles (`package.json`,
`composer.json`, `requirements.txt`, etc.) and compare versions/frameworks
against what's written. Version bumps and added/removed dependencies are
unambiguous drift.

**`dev_guidelines.md`** — check if linter/formatter configs changed, or if
recently-added code follows a different pattern than what's documented
(e.g. docs say "logic lives in Actions/ classes" but the last 10 commits
put logic in controllers instead).

**`business_logic.md`** — for each rule written down, grep for the
constant/value/comment it's based on (same evidence trail `memento-init`
recorded) and check it still matches. A changed number, a removed
comment, or logic that moved without an update to this file are all
drift signals.

**`decisions.md`** — for each non-superseded decision, check whether the
code still reflects that choice. If it doesn't, the decision was silently
reversed and the log wasn't updated.

**`current_task.md`** — compare `## Sırada / Next` against recent commits
(`git log --oneline -20` or similar). Items already implemented should be
checked off/removed; a `## Şu An / Now` entry that no longer matches any
active work is stale.

## Classify each finding, don't handle them all the same way

- **Safe to auto-fix**: unambiguous facts with one correct answer — a
  version number, a dependency that was added/removed, a `Sırada` item
  that's now clearly done. Fix directly, tag the change
  `<!-- SYNCED YYYY-MM-DD -->` so the user can see what moved without
  digging through git blame.
- **Needs the user's judgment**: anything where code diverging from
  memory could mean the memory is stale OR the code is an unintentional
  regression — a business rule value that changed, a decision that looks
  reversed, a convention that shifted. Don't silently pick a side. Ask,
  and only ask about the specific ambiguous cases (not the whole file).

## When the user confirms a real change happened

Follow the existing protocol in `.memento/claude.md`: don't delete the old
`decisions.md` entry, mark it `SUPERSEDED → [link to new entry]`, add the
new decision, and update `current_task.md`'s `Sırada / Next` to match.
Treat this the same as if the user had just told you about the decision
directly in chat — it's not a special "sync mode" format.

## Report

End with a short summary grouped by file: what was auto-fixed, what was
confirmed and updated, what's still open (waiting on the user to decide).
Don't just say "synced" — list the actual deltas, since that list is the
point of running this.
