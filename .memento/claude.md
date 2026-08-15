# Memento Index

This file is a **router only** — it holds no rules or content itself.
Read the relevant file below before starting work on a matching topic.
Keep this index under 150 lines; if it grows past that, split a section
into its own file and add a line here instead of inlining detail.

## 1_Rules — permanent, rarely changes
- Coding standards & stack → [1_Rules/dev_guidelines.md](../.memento/1_Rules/dev_guidelines.md)
- UI/UX visual language → [1_Rules/brand_voice.md](../.memento/1_Rules/brand_voice.md)
- How to talk to the AI / prompt conventions → [1_Rules/prompt_patterns.md](../.memento/1_Rules/prompt_patterns.md)

## 2_Knowledge — domain facts
- Tech stack details → [2_Knowledge/tech_stack.md](../.memento/2_Knowledge/tech_stack.md)
- Business/domain logic → [2_Knowledge/business_logic.md](../.memento/2_Knowledge/business_logic.md)

## 3_Sessions — dynamic, changes every session
- What's happening right now + what's next → [3_Sessions/current_task.md](../.memento/3_Sessions/current_task.md)
- Architectural decisions log (with supersede tracking) → [3_Sessions/decisions.md](../.memento/3_Sessions/decisions.md)

## Protocol
1. Read this index first. Only open the file(s) the current task needs.
2. When a decision changes or invalidates a previous one, don't delete the
   old entry in `decisions.md` — mark it `SUPERSEDED → [link to new entry]`,
   then update the `## Sırada / Next` section in `current_task.md` to match.
3. When a feature/task is finished, say so explicitly and suggest clearing
   the chat/context — the next session should start clean and re-read this
   index rather than carry old conversational bias forward.
