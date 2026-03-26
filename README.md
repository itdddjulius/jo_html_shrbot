# JO_SHRBOT HTML5 + PHP API Project

JO_SHRBOT is a two-panel merge and refactor workbench with a JO-branded UI.

This project contains:

- a **single HTML5 front-end** using:
  - HTML5
  - JavaScript
  - Tailwind CSS
  - Bootstrap 5
  - FontAwesome
- backend **PHP API endpoints** for:
  - Git CLI merge checks
  - GitHub API merge integration point
  - GitLab API merge integration point
  - AI refactor integration
- log storage
- a repeatable browser workflow with `localStorage`

---

# 1. What the app does

The application has **two horizontal panels**.

## Panel 1
The user enters:

- Pull Request code (`PR`)
- CODE-BASE (`CB`)

Then clicks:

- **MERGE**

If merge checks fail:

- all failures are shown in **Panel 1**
- a **REFACTOR** button appears

## Refactor from Panel 1
When the user clicks:

- **REFACTOR**

the refactored code is placed into **Panel 2** and the user sees:

- `PANEL2 code has BEEN REFACTORED`

## Panel 2
The user reviews the refactored code and can click:

- **RE-MERGE**

If the re-merge fails:

- failures are shown in **Panel 2**
- a new **REFACTOR** button appears in **Panel 2**

If that button is clicked:

- refactored code moves back to **Panel 1**
- alert appears:
  - `PANEL1 code has BEEN REFACTORED`

This cycle can continue as many times as needed.

---

# 2. Project structure

```text
JO_SHRBOT_HTML_API_PROJECT/
├── index.html
├── README.md
├── api/
│   ├── merge_git_cli.php
│   ├── merge_github.php
│   ├── merge_gitlab.php
│   └── refactor_ai.php
└── storage/
    └── logs/
```

---

# 3. Front-end file

## `index.html`
This is the **single HTML5 front-end**.

It contains:

- navbar
- two horizontal panels
- merge workflow UI
- Bootstrap modal for contact
- localStorage persistence
- JavaScript endpoint calls to the PHP backend

---

# 4. Backend endpoints

## `api/merge_git_cli.php`
Runs a **real local Git CLI merge validation** in a temporary workspace.

### What it does
- validates PR and CODE-BASE
- initializes a temporary Git repository
- commits the CODE-BASE as base
- creates a PR branch
- commits the PR code
- attempts a real merge

### Output
Returns JSON:

```json
{
  "ok": true,
  "failures": [],
  "merged_code": "..."
}
```

or

```json
{
  "ok": false,
  "failures": ["..."]
}
```

### Important
This requires:

- `git` installed on the server
- `proc_open()` enabled
- permission to create temporary folders

---

## `api/merge_github.php`
This is the **GitHub API merge integration point**.

### It is scaffolded but not repository-specific yet
The endpoint already:

- validates input
- validates configuration
- returns structured JSON errors
- clearly marks where GitHub API code must be inserted

### Environment variables used
Set these before using real GitHub calls:

```bash
GITHUB_TOKEN=your_token
GITHUB_OWNER=your_owner
GITHUB_REPO=your_repo
GITHUB_API_BASE=https://api.github.com
```

### Where to add your real GitHub code
Inside:

```php
api/merge_github.php
```

look for:

```php
INSERT REAL GITHUB API MERGE WORKFLOW HERE
```

### Suggested GitHub flow
- create/update branch from CODE-BASE
- create blob/tree/commit for PR code
- create pull request
- attempt merge
- return merge conflicts or success

### Example GitHub endpoints
- `POST /repos/{owner}/{repo}/git/blobs`
- `POST /repos/{owner}/{repo}/git/trees`
- `POST /repos/{owner}/{repo}/git/commits`
- `PATCH /repos/{owner}/{repo}/git/refs/heads/{branch}`
- `POST /repos/{owner}/{repo}/pulls`
- `PUT /repos/{owner}/{repo}/pulls/{pull_number}/merge`

---

## `api/merge_gitlab.php`
This is the **GitLab API merge integration point**.

### It is scaffolded but not project-specific yet
The endpoint already:

- validates input
- validates configuration
- returns structured JSON errors
- clearly marks where GitLab API code must be inserted

### Environment variables used
Set these before using real GitLab calls:

```bash
GITLAB_TOKEN=your_token
GITLAB_PROJECT_ID=your_project_id
GITLAB_API_BASE=https://gitlab.com/api/v4
```

### Where to add your real GitLab code
Inside:

```php
api/merge_gitlab.php
```

look for:

```php
INSERT REAL GITLAB API MERGE WORKFLOW HERE
```

### Suggested GitLab flow
- create or update repository commit
- create merge request
- attempt merge
- inspect merge status / pipeline status

### Example GitLab endpoints
- `POST /projects/{id}/repository/commits`
- `POST /projects/{id}/merge_requests`
- `PUT /projects/{id}/merge_requests/{merge_request_iid}/merge`

---

## `api/refactor_ai.php`
This endpoint handles **refactoring**.

It supports two modes:

### Mode 1 — local stub
If the front-end sends:

```json
{ "backend": "stub" }
```

then the endpoint performs a local safe refactor fallback:
- normalizes line endings
- replaces tabs
- removes merge markers
- replaces TODO/FIXME
- appends a refactor summary comment

### Mode 2 — AI API
If the front-end sends:

```json
{ "backend": "ai_api" }
```

then the endpoint calls a real AI service.

### Environment variables used
Set these before using the live AI mode:

```bash
AI_API_URL=https://your-openai-compatible-endpoint
AI_API_KEY=your_api_key
AI_MODEL=your_model_name
```

### Where the AI call is implemented
Inside:

```php
api/refactor_ai.php
```

The live call already exists and expects an OpenAI-compatible API response shape.

---

# 5. Completed front-end endpoint calls

The HTML file already contains completed JavaScript functions for:

## `mergeWithGitCli(pr, cb)`
```javascript
return postJson("api/merge_git_cli.php", {
  pr,
  cb,
  repo_name: repoName.value.trim()
});
```

## `mergeWithGitHubApi(pr, cb)`
```javascript
return postJson("api/merge_github.php", {
  pr,
  cb,
  repo_name: repoName.value.trim()
});
```

## `mergeWithGitLabApi(pr, cb)`
```javascript
return postJson("api/merge_gitlab.php", {
  pr,
  cb,
  repo_name: repoName.value.trim()
});
```

## `refactorWithAi(code, failures)`
```javascript
return postJson("api/refactor_ai.php", {
  code,
  failures,
  repo_name: repoName.value.trim(),
  backend: refactorBackend.value
});
```

These are **fully wired AJAX calls** to the provided backend endpoints.

---

# 6. Browser-side persistence

The front-end stores workflow state in:

```javascript
localStorage
```

using the key:

```text
jo_shrbot_html_state_v2
```

This preserves:
- Panel 1 inputs
- Panel 2 outputs
- selected backend
- repository/context name
- failure lists
- alert state

---

# 7. UI / branding

The UI follows JO branding:

- background: black
- text: white
- buttons: green
- Bootstrap + Tailwind + FontAwesome
- glassmorphism cards
- sticky navbar
- smooth scrolling
- footer with:
  - navigation links
  - `Another Website by Julius Olatokunbo`
- contact modal popup linked to:
  - `https://raiiarcomio.com/contact2`

---

# 8. How to run locally

## Option A — PHP built-in server
From the project root, run:

```bash
php -S localhost:8000
```

Then open:

```text
http://localhost:8000/index.html
```

## Option B — XAMPP / Apache / MAMP
Copy the project into your web root and browse to:

```text
http://localhost/your-folder/index.html
```

---

# 9. Server requirements

Minimum recommended:

- PHP 8+
- `curl` extension enabled
- `git` installed if using `merge_git_cli.php`
- `proc_open()` allowed if using Git CLI backend
- write permission for:
  - `storage/logs`
  - temporary workspace folder creation

---

# 10. Environment variable examples

## GitHub
```bash
export GITHUB_TOKEN="ghp_xxx"
export GITHUB_OWNER="your-org"
export GITHUB_REPO="your-repo"
export GITHUB_API_BASE="https://api.github.com"
```

## GitLab
```bash
export GITLAB_TOKEN="glpat_xxx"
export GITLAB_PROJECT_ID="123456"
export GITLAB_API_BASE="https://gitlab.com/api/v4"
```

## AI refactor
```bash
export AI_API_URL="https://api.openai.com/v1/chat/completions"
export AI_API_KEY="sk-xxx"
export AI_MODEL="gpt-4.1-mini"
```

---

# 11. Security notes

Important safeguards:

- never expose tokens in the browser
- keep GitHub/GitLab/AI credentials server-side only
- validate all repo, branch, and content inputs before sending to Git or AI
- restrict which repositories may be touched
- audit every merge and refactor action
- consider adding authentication before exposing the endpoints publicly

---

# 12. Recommended next upgrades

Useful next steps:

- add user authentication
- add repository URL and branch fields
- add dry-run vs real-merge toggle
- add streaming logs in the UI
- add SQLite job history
- add syntax/lint/test pipeline before merge
- add branch protection awareness
- add file-level diff viewer
- add Monaco editor for code panes

---

# 13. Known limitations

Current state:

- `merge_git_cli.php` is real and runnable if Git is available
- `merge_github.php` and `merge_gitlab.php` are intentionally scaffolded integration points
- `refactor_ai.php` is live for OpenAI-compatible endpoints if environment variables are set
- the front-end is fully wired, but production rollout should add authentication and tighter backend validation

---

# 14. Quick start recommendation

If you want to test immediately:

1. run the project with PHP
2. use:
   - merge backend = `git_cli`
   - refactor backend = `stub`
3. paste sample PR and CODE-BASE text
4. click:
   - `MERGE`
   - `REFACTOR`
   - `RE-MERGE`

Then switch to:
- `github_api`
- `gitlab_api`
- `ai_api`

after configuring environment variables and inserting your real repository workflows.

---

# 15. Branding

Project title:
- `JO_SHRBOT`

Footer:
- `Another Website by Julius Olatokunbo`

Footer link:
- `https://raiiarcomio.com`

Contact modal:
- `https://raiiarcomio.com/contact2`
