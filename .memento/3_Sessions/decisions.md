# Decisions Log

<!-- Append-only. Never delete an entry — mark it SUPERSEDED instead so the
     "why" behind old choices stays visible. Newest at the bottom or top,
     pick one and stay consistent. -->

## Template

### [YYYY-MM-DD] Decision: <short title>
- **Chose:** <what was decided>
- **Why:** <the reasoning / constraint that drove it>
- **Affects:** <link to current_task.md section it changes, e.g. "Sırada → step X">
- **Supersedes:** <link to a prior entry, or "none">

<!-- Example entry:

### 2026-07-21 Decision: Use queue-based email sending instead of sync
- Chose: dispatch email jobs to a queue worker instead of sending inline
- Why: sync sending was blocking checkout requests under load
- Affects: current_task.md → Sırada: "wire up queue worker"
- Supersedes: none

-->

### 2026-07-26 Decision: Install Memento by direct file copy, not git submodule
- **Chose:** Copy `CLAUDE.md`, `.memento/`, and `.claude/skills/memento-*`
  directly into the qr-menu repo instead of adding Bderq/memento as a git
  submodule.
- **Why:** User explicitly chose this over submodule to avoid submodule
  workflow complexity; project had no existing `CLAUDE.md` or
  `.claude/skills/`, so there was no conflict risk either way.
- **Affects:** current_task.md → Şu An (Memento kurulumu)
- **Supersedes:** none

### 2026-07-26 Decision: Communication style — terse, confirm-before-acting
- **Chose:** Short responses, no trailing summaries; state the plan and
  wait for confirmation before non-trivial code edits (not just risky/
  irreversible ones).
- **Why:** User's explicit preference, captured via AskUserQuestion during
  memento-init population of `prompt_patterns.md`.
- **Affects:** `.memento/1_Rules/prompt_patterns.md` (source of truth for
  this rule); current_task.md going forward should reflect plan-first
  behavior.
- **Supersedes:** none

### 2026-07-26 Fix: business_logic.md had two factual errors, corrected after verification
- **Symptom:** Initial AUTO-DETECTED pass claimed 4 campaign types and
  cited wrong line numbers/scope for the duplicated percentage-discount
  formula.
- **Root cause:** `app/Enums/CampaignType.php` actually defines 5 cases
  (missing `COLLECTIVE`, which the README also omits); the discount
  formula is duplicated across 4 call sites (2 in `CampaignService.php`,
  2 in `MenuService.php`), not 2 as first assumed.
- **Affects:** `.memento/2_Knowledge/business_logic.md` (corrected in
  place with verified file:line references).
- **Supersedes:** none (same session, caught before user relied on it)
