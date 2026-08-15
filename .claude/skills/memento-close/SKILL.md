---
name: memento-close
description: End-of-session wrap-up for the Memento memory pack — reviews what happened in the current conversation and updates 3_Sessions/current_task.md and 3_Sessions/decisions.md (and 2_Knowledge/* if the stack or business rules changed) so the next session/chat can pick up cold. Use when the user says they're switching chats, ending the session, wants docs/memory updated before leaving, or says things like "yeni chate geçiyorum", "dokümanları güncelle", "session'ı kapat", "oturumu bitir", "update the docs before I go".
---

# Memento Close

Run this at the end of a working session, right before the user switches
to a new chat. Unlike `memento-sync` (which audits memory against the
*code*), this skill captures what happened in the *conversation* —
decisions made, bugs found and fixed, features shipped, what's left open
— so nothing gets lost when the chat ends and context resets.

## Step 1 — Read current state

Read `.memento/claude.md`, then `3_Sessions/current_task.md` and
`3_Sessions/decisions.md` to see what was already known before this
session started.

## Step 2 — Review this session's conversation

Scan back through the conversation (not the codebase) for:
- **Real decisions**: architecture/scope choices, trade-offs picked
  between alternatives, anything the user explicitly approved or
  redirected
- **Bugs found + fixed**: especially ones with a non-obvious root cause
  (permissions, config gotchas, third-party API quirks) — these are the
  highest-value entries, since they prevent re-debugging the same thing
  next time
- **What shipped**: features/endpoints/pages completed this session
- **What's still open**: anything explicitly left for later, blockers,
  things the user still needs to do (get credentials, test something,
  configure infra)

Don't re-derive this from the code — pull it from what was actually
discussed and done in the chat.

## Step 3 — Update `current_task.md`

Overwrite `## Şu An / Now` with a short, accurate snapshot of where
things stand at the end of this session. Rewrite `## Sırada / Next` to
reflect what's actually left (remove completed items, add newly
discovered ones). Update `## Açık Sorular / Blockers` similarly.

## Step 4 — Append to `decisions.md`

For each real decision or notable bug-fix from Step 2, append a new
entry following the existing template (title, Chose/Symptom, Why/Root
cause, Affects, Supersedes). Never delete or rewrite old entries — if a
new decision reverses an old one, mark the old one `SUPERSEDED` and
link to the new entry. Skip trivial/obvious changes that don't need a
paper trail (typo fixes, routine CRUD).

## Step 5 — Sync knowledge files if needed

Only if the session changed the stack, infra, or a business rule: update
`2_Knowledge/tech_stack.md` and/or `2_Knowledge/business_logic.md` to
match. Skip this step if nothing there actually changed — don't touch
files that are still accurate.

## Step 6 — Report and confirm it's safe to switch chats

End with a short summary (grouped by file, like `memento-sync` does) of
what was written, then explicitly tell the user the memory is up to
date and it's safe to start a fresh chat — the next session's Claude
will pick this up automatically via `.memento/claude.md`.
