---
name: memento-reindex
description: Verify and repair .memento/claude.md so it stays a complete, accurate router. Use after adding, removing, renaming, or splitting a file under .memento/ (e.g. turning business_logic.md into a business_logic/ folder per module), or whenever the index feels out of date. Not for editing file content — only for keeping the index's links correct.
---

# Memento Reindex

Keeps `.memento/claude.md` honest. It is index management only — never
touch the content of `1_Rules/`, `2_Knowledge/`, or `3_Sessions/` files
here, and never let this skill grow the index past its job of routing.

## Step 1 — Walk the actual file tree

List every file under `.memento/` (all subfolders, any depth — module
splits like `2_Knowledge/business_logic/billing.md` are expected, not an
error). This is ground truth; `claude.md` is a claim about it that may
have drifted.

## Step 2 — Diff against the index

For each file found:
- If it has no entry in `claude.md`, it's missing — add a line under the
  right section (`1_Rules`, `2_Knowledge`, `3_Sessions`, or a new section
  if the file doesn't fit an existing one).
- If a file was split into a folder (e.g. `business_logic.md` →
  `business_logic/*.md`), replace the old single-file entry with one line
  per new file, each with a short description of what that module covers
  (infer it from the file's own heading/first paragraph, don't guess
  blindly).

For each line already in `claude.md`:
- If it points to a file that no longer exists, remove the line.
- If the link path is wrong (file moved), fix the path.

## Step 3 — Keep it a router, not a summary

Every line should stay in the existing format: a short label plus a link,
one line each. Do not inline explanations, rules, or content from the
target files into `claude.md` — if a description is needed to tell files
apart, one clause is enough (see existing lines as the length reference).
If fixing the index would push it past ~150 lines, that's a signal the
underlying `2_Knowledge` or `1_Rules` structure needs another split
(e.g. group module files under one indented block) — flag this to the
user rather than letting the index bloat silently.

## Step 4 — Report

List what changed: lines added, removed, or fixed, and any file found on
disk that didn't cleanly fit an existing section (ask the user where it
belongs rather than guessing). If nothing was out of sync, say so plainly
instead of padding the report.
