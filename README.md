# Espi's Random Tools

A small collection of randomization utilities. Runs in Docker with PHP and Tailwind CSS.

## Tools

**Weighted Picker** — Add options, assign weights, and pick randomly. Higher weight = more likely to be chosen. Saves named sets to browser localStorage. Export and import sets as JSON.

**Passphrase Generator** — Generate memorable word-based passphrases. Control word count (2–10), delimiter style (dash, underscore, dot, space, none, digit-between, or custom), capitalisation, and optional appended number or symbol.

**Dice Roller** — Roll d4, d6, d8, d10, d12, d20, d100, or any custom die. Roll multiple at once with an optional modifier. Nat 1s and max rolls are highlighted. History saved to localStorage.

**Random Numbers** — Generate one or many integers in a range. Optional no-duplicates mode (Fisher-Yates), sort ascending, copy as CSV. Quick presets for common ranges.

## Stack

- PHP 8.2 (Apache)
- Tailwind CSS (Play CDN)
- Vanilla JavaScript
- Docker / Docker Compose

## Running

```bash
docker compose up -d
```

Then open [http://localhost:8082](http://localhost:8082).

The `src/` directory is mounted as a volume, so edits to PHP files are reflected immediately without rebuilding.

## Development

```
random/
├── docker-compose.yml
├── Dockerfile
└── src/
    ├── includes/
    │   ├── header.php     # nav, Tailwind config, fonts
    │   ├── footer.php     # closing tags + repo link
    │   └── words.php      # word pool for passphrase generator (~200 words)
    ├── index.php
    ├── picker.php
    ├── passphrase.php
    ├── dice.php
    └── number.php
```

To add words to the passphrase pool, edit `src/includes/words.php`.

To rebuild the image (e.g. after changing the Dockerfile):

```bash
docker compose build && docker compose up -d
```

## License

MIT
