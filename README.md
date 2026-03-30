# JO_SHRBOT HTML5 + PHP API Project

JO_SHRBOT is a two-panel merge and refactor workbench.

It is designed to simulate or orchestrate this workflow:

1. paste pull request code into PANEL1
2. paste target codebase into PANEL2
3. click **MERGE**
4. inspect merge failures
5. click **REFACTOR**
6. review refactored output in the opposite panel
7. click **RE-MERGE**
8. repeat until the result is acceptable

This package provides:

- single-page HTML5 frontend
- PHP API endpoints
- Docker Compose for local hosting
- a mock merge/refactor flow
- clear placeholders for real Git CLI / GitHub / GitLab / AI integrations

---

## Project Structure

```text
JO_SHRBOT_HTML5_PHP_API_Project/
├── index.html
├── index.php
├── docker-compose.yml
├── README.md
├── api/
│   ├── merge.php
│   └── refactor.php
└── storage/
```

---

## Frontend Features

- black background
- white text
- green action buttons
- responsive Bootstrap + Tailwind UI
- sticky navbar
- two editable horizontal panels
- merge / refactor / re-merge cycle
- terminal-style log panel
- failure inspection panel
- footer with all navbar menu items
- contact modal popup pointing to:
  - `https://raiiarcomio.com/contact2`

---

## Backend Features

### `api/merge.php`
Handles merge requests.

Current behavior:
- validates JSON payload
- runs mock merge checks
- returns:
  - merge status
  - detected failures
  - execution log

Suggested real integrations:
- Git CLI merge check
- GitHub mergeability API
- GitLab mergeability API

### `api/refactor.php`
Handles refactor requests.

Current behavior:
- validates JSON payload
- applies mock fixes
- returns:
  - refactored code
  - log output

Suggested real integrations:
- AI refactor service
- OpenAI tool endpoint
- Anthropic tool endpoint
- internal refactor microservice

---

## Run With Docker Compose

### Start
```bash
docker compose up --build
```

### Open
```text
http://localhost:8080
```

### Stop
```bash
docker compose down
```

---

## Run Without Docker

You can also serve it from any PHP-enabled local environment:

- XAMPP
- MAMP
- WAMP
- built-in PHP server

Example:
```bash
php -S localhost:8080
```

Then open:
```text
http://localhost:8080
```

---

## API Request Examples

### Merge
POST `api/merge.php`

```json
{
  "provider": "mock",
  "language": "Python",
  "pr": "print('hello from pr')",
  "cb": "print('hello from target')",
  "direction": "1to2"
}
```

### Refactor
POST `api/refactor.php`

```json
{
  "mode": "safe",
  "language": "Python",
  "code": "print('hello')\n# TODO",
  "failures": ["PR contains TODO markers."],
  "source_side": "panel1"
}
```

---

## Where To Insert Real Integrations

### In `api/merge.php`
Replace the mock logic with:

- Git CLI merge execution
- temporary repository checkout
- branch merge check
- conflict capture
- stdout / stderr reporting

Or connect to a backend service that calls:

- GitHub mergeability APIs
- GitLab mergeability APIs

### In `api/refactor.php`
Replace the mock text replacement with:

- AI prompt call
- LLM-based code patching
- diff-based patch response
- structured validation and retry flow

---

## Recommended Next Enhancements

- Monaco Editor
- syntax highlighting by detected language
- file upload support
- diff viewer
- patch export
- branch / repo metadata inputs
- auth layer for protected merge operations
- persistent audit logs
- background job queue for long-running merges
- webhook integration for GitHub/GitLab

---

## Branding Requirements Included

- footer text links to `https://raiiarcomio.com`
- footer includes all navbar menu items
- contact modal popup points to `https://raiiarcomio.com/contact2`

---

## Summary

This project is a practical starter for a merge/refactor orchestration UI.

It already includes:
- frontend workbench
- PHP API endpoints
- local docker runtime
- mock behavior for demo/testing

It is ready to be extended into a real Git + AI automation system.
