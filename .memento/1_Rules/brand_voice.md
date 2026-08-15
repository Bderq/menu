# Brand / Visual Voice

<!-- AUTO-DETECTED, verify — source: resources/css/app.css @theme block -->

- Design references: "CRASH Pub Style" — punk/grunge, sticker/label aesthetic. Sharp corners (`--radius-sharp: 0px`), hard drop-shadows with no blur (`sticker-shadow`, `heavy-shadow` utilities use solid offset shadows, not blur), rotated "sticker-tag" labels, grunge/asphalt texture overlays, text-outline and text-grunge effects.
- Color palette: `pub-gold` (#ffb000), `pitch-black` (#000000), `crash-white` (#ffffff), `off-white` (#f8f8f8), `acid-blue` (#0047ff) — high-contrast black/gold primary pairing.
- Typography: `Archivo Black` for headings (heavy, condensed), `Permanent Marker` for script/handwritten accents, `JetBrains Mono` for mono/technical bits, `Inter` for body/sans.
- Motion: Framer Motion for micro-animations, plus a custom slow-spin keyframe utility.
- Tone of copy: <TODO — not inferable from CSS alone, confirm with user; README markets the product as "Premium"/"Vercel-standard" polish, which sits in tension with the punk/grunge visual style — worth clarifying which wins for new UI copy>
- Things to avoid: generic SaaS gradients, soft/blurred shadows, rounded corners that break the sharp/sticker look
