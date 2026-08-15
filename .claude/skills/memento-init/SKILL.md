---
name: memento-init
description: Populate a freshly-copied .memento/ pack by analyzing the host project — detects stack, conventions, and business logic automatically, then asks the user only what genuinely can't be inferred from code. Use when .memento/ was just added to a project and its files still contain TODO placeholders, or when the user asks to "fill in memento", "initialize memento", "run memento-init".
---

# Memento Init

Populates `.memento/` for the project it now lives in. Runs once, right
after the pack is copied/cloned into a repo. Do not run this speculatively
in a repo that already has filled-in `.memento/` files — check for `TODO`
markers first; if none remain, ask the user before overwriting anything.

## Step 1 — Detect stack → `2_Knowledge/tech_stack.md`

Look for manifest/lockfiles at the project root and in common subfolders:
`package.json`, `composer.json`, `requirements.txt`/`pyproject.toml`,
`Gemfile`, `go.mod`, `Cargo.toml`, plus their lockfiles for exact versions.
Also check for framework fingerprints (`artisan` → Laravel, `vue.config.js`
→ Vue, `next.config.js` → Next, etc.). Write what you find: language,
framework + version, package manager, notable infra config (Dockerfile,
CI config) if present. Prefix the section with `<!-- AUTO-DETECTED, verify -->`.

## Step 2 — Detect conventions → `1_Rules/dev_guidelines.md`

Check for linter/formatter/static-analysis configs (`.eslintrc*`,
`.prettierrc*`, `pint.json`, `phpstan.neon`, `.rubocop.yml`, `ruff.toml`,
etc.) and note which are active. Look at the existing folder structure and
naming conventions in 2-3 representative source directories (don't read
the whole codebase) to describe the pattern the project already follows
(e.g. "controllers are thin, logic lives in Actions/ classes"). Note any
test framework in use. Tag this section `<!-- AUTO-DETECTED, verify -->` too
— conventions inferred from a handful of files can be wrong; the user
should skim and correct.

## Step 3 — Detect design signals → `1_Rules/brand_voice.md`

Only if the project already has a UI layer: check for `tailwind.config.*`,
CSS custom properties / design tokens, a component library, existing
color/typography choices. If found, summarize the de facto visual language
under `<!-- AUTO-DETECTED, verify -->`. If the project has no UI yet or you
find nothing, leave the TODO as-is — don't invent an aesthetic.

## Step 4 — Detect business logic → `2_Knowledge/business_logic.md`

This is the hard one — most business rules are implicit, not documented.
Look for signals rather than expecting a clean source:
- Comments near magic numbers, date/time math, pricing, or discount logic
  ("why is this 3.52", "day starts at 6am" style notes)
- Constants/config files with non-obvious names or values
- Existing docs: README, `/docs`, wiki exports, ADRs already in the repo
- Cron/scheduled job definitions (often encode business timing rules)
- Validation rules in models/forms that encode domain constraints

Write every finding under `<!-- AUTO-DETECTED, verify -->` with a pointer
to where you found it (file:line) so the user can check it fast. If you
find conflicting rules in different places, flag the conflict explicitly
instead of picking one silently.

## Step 5 — Ask what's left

`prompt_patterns.md` cannot be detected from code — always ask the user
directly about it (how they want you to communicate, response length,
confirm-before-acting preferences).

For `business_logic.md`, after Step 4's scan, ask the user 2-4 targeted
questions only about the gaps you couldn't resolve from code — not a fixed
script, tailor them to what's actually missing or ambiguous. Example shape:
"I didn't find where the discount multiplier comes from — is there a
formula behind it, or is it a fixed constant?" Use AskUserQuestion for
this if the choices are discrete, otherwise ask in plain text.

Do not ask about anything you already found with reasonable confidence —
the point of Steps 1-4 is to cut down what needs to be asked, not to
double-check everything.

## Step 6 — Report

Summarize what was auto-filled vs. what still needs the user's input, file
by file. Remind the user that anything tagged `AUTO-DETECTED` should be
skimmed once before being trusted as a permanent rule.
