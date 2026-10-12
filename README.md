# Espi's Random Tools

Small everyday tools. PHP + Tailwind, runs in Docker. Everything stays in your browser.

- **Weighted Picker** — Options with weights and day-of-week bonuses. Named sets saved to localStorage with import/export.
- **Passphrase Generator** — Word-based passphrases from the 7,776-word EFF pool. Configurable count, delimiter, capitalisation, appended number/symbol.
- **Tip Calculator** — Bill, tip percentage and split, with optional round-to-whole-dollar per person.
- **Dice Roller** — d4–d100, any count, flat modifier, animated rolls and history.
- **Coin Flip** — Animated flip with streak tracking and history.
- **Meme Builder** — Templates or your own image (upload, paste or drop), draggable text, PNG download.

The app is installable as a PWA and works offline once visited (service worker in `src/sw.js`).

## Run

```bash
docker compose up -d
```

Open [http://localhost:8083](http://localhost:8083). `src/` is volume-mounted — PHP edits reflect immediately.

## Styles

Tailwind is compiled to `src/assets/app.css`, which is committed so Docker needs no Node. After changing classes in any PHP file:

```bash
npm install     # first time
npm run build   # or: npm run watch
```

## Structure

```text
src/
├── assets/
│   ├── input.css      # Tailwind entry + global styles
│   └── app.css        # built output (committed)
├── includes/
│   ├── pages.php      # page registry: nav label, title, subtitle, card description
│   ├── header.php     # head, nav, page heading, shared JS helpers
│   ├── footer.php
│   └── words.php      # EFF large wordlist
├── index.php
├── picker.php  passphrase.php  tip.php  dice.php  coin.php  meme.php
├── sw.js              # service worker
└── manifest.json
```

To add a tool: create `src/<name>.php` (include header/footer) and add one entry to `includes/pages.php`; the nav, home card and heading come from it.
