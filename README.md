# Espi's Random Tools

Randomization utilities. PHP + Tailwind, runs in Docker.

**[Weighted Picker](/src/picker.php)** — Options with weights, picks randomly. Named sets saved to localStorage with import/export.

**[Passphrase Generator](/src/passphrase.php)** — Word-based passphrases. 7,776-word EFF pool. Configurable word count, delimiter, capitalisation, optional appended number/symbol.

## Run

```bash
docker compose up -d
```

Open [http://localhost:8083](http://localhost:8083). `src/` is volume-mounted — PHP edits reflect immediately.

## Structure

```text
src/
├── includes/
│   ├── header.php
│   ├── footer.php
│   └── words.php      # EFF large wordlist (7,776 words)
├── index.php
├── picker.php
└── passphrase.php
```
