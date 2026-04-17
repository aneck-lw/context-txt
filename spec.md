# context.txt — Specification

**Version:** 0.1 (draft)
**Status:** Proposal

---

## What is context.txt?

`context.txt` is a file websites place in their webroot to give AI agents a
machine-readable description of what the site is, what data and APIs it
exposes, and how to present that data visually. It is the entry point for an
AI agent trying to understand and interact with a site programmatically.

Think of it as the AI-facing counterpart to a human-facing homepage.

---

## Design principles

- **Plain text, Markdown content.** The file uses `.txt` as its extension so
  every web server delivers it as `text/plain` without configuration. The
  content is Markdown, which AI models parse naturally and humans can read
  without tooling.
- **Hierarchical and navigable.** A root `context.txt` links to sub-files for
  sections, APIs, and style. An AI agent follows links the same way a human
  navigates HTML pages.
- **Complementary, not competing.** `context.txt` focuses on API discovery,
  data semantics, and rendering guidance. It works alongside `llms.txt`
  (content navigation) and `agent-manifest.txt` (permissions) rather than
  replacing them.
- **Minimal by default.** A valid `context.txt` needs only a title and a short
  description. Everything else is optional and added as the site grows.

---

## Discovery

An AI agent should look for `context.txt` at the root of the site:

```
https://example.com/context.txt
```

For sites where the application lives under a subpath:

```
https://example.com/myapp/context.txt
```

Optionally, a site may also signal the file via an HTML `<link>` tag:

```html
<link rel="context" type="text/plain" href="/context.txt">
```

---

## File format

The file is Markdown inside a `.txt` file, served as `text/plain; charset=utf-8`.

A minimal valid `context.txt`:

```markdown
# Site Name

One paragraph describing what this site is and who it is for.
```

---

## Root context.txt

The root file is the entry point. It describes the site at a high level and
links to sub-files for APIs, sections, and style.

### Structure

```markdown
# Site Name

Short description of what the site is and who it is for.

## What you will find here

Description of the content and data available on this site.

## APIs

- [API Name](api/context.txt) — one-line description
- [Another API](other/api/context.txt) — one-line description

## Site sections

- [Section Name](section/context.txt) — one-line description

## Presentation

- [Style guide](style/context.txt) — colours, typography, and layout defaults

## Private access

Authenticated access is available for account holders.
Credentials required to access further documentation.
Contact: api@example.com
```

### Rules

- The H1 title is required. Everything else is optional.
- Every sub-file linked from the root must link back to the root with a
  back-link line, e.g. `[← Site Name](../context.txt)`.
- The `## Private access` section should contain no paths or structural
  information. It exists only to signal that private APIs are available and
  how to obtain credentials. Paths are revealed only after authentication.

---

## API context.txt

Each API the site exposes gets its own `context.txt` file, typically at
`/api/context.txt` or `/section/api/context.txt`.

### Structure

```markdown
# API Name

Short description of what this API provides.

[← Site Name](../context.txt)

## Base path

- `/api`

## Authentication

- **Required:** yes / no
- **Method:** Bearer token / API key / Basic auth / OAuth 2.0
- **Header:** `Authorization: Bearer <token>`
- **Obtain:** https://example.com/account/tokens

## Endpoints

### `GET /api/resource`

Description of what this endpoint returns.

Parameters:

- `param` — description, accepted values

Response fields:

- `field` — type, description

### `GET /api/resource/{id}`

...

## Error responses

- `400` — description
- `401` — not authenticated
- `403` — authenticated but not authorised
- `404` — resource not found
- `429` — rate limited
- `500` — server error

## Rate limiting

- N requests per minute per IP / token

## Cache behaviour

- Successful responses: `Cache-Control: public, max-age=N`
```

### Authentication block values

| Field | Values |
|---|---|
| Required | `yes` or `no` |
| Method | `Bearer token`, `API key`, `Basic auth`, `OAuth 2.0` |
| Header | The exact header the client must send |
| Obtain | URL where the user can generate or retrieve credentials |

---

## Style context.txt

A site may provide a style guide at `/style/context.txt` to give AI agents a
visual starting point when rendering data as HTML for a human reader. The style
guide only applies when the agent produces HTML output — in plain-text or
Markdown responses there is nothing to apply it to. Users should ask explicitly
for an HTML page when they want styled output. These are defaults — the user
receiving the output is always free to ask for changes.

### Structure

```markdown
# Site Name — Presentation Style

Short note that these are defaults and the user may adapt them.

[← Site Name](../context.txt)

## Colours

| Role | Hex |
|---|---|
| Page background | `#xxxxxx` |
| Surface | `#xxxxxx` |
| Border | `#xxxxxx` |
| Primary text | `#xxxxxx` |
| Muted text | `#xxxxxx` |
| Accent | `#xxxxxx` |

## Typography

- Font: ...
- Base size: ...

## Badges / tags

Description of how to render categorical values as visual badges.

## Standard table layout

Column order and styling for common data tables.
```

---

## Private context.txt (authenticated access)

For sites where the data topology itself is sensitive, the `context.txt` and
its linked API descriptions can be placed behind HTTP authentication. An AI
agent receives a standard HTTP 401 response and must obtain credentials before
it can read any structural information about the private APIs.

### How it works

1. The public `/context.txt` may contain a `## Private access` section
   indicating that authenticated APIs exist, along with a contact address or
   credential URL. It must not reveal any private paths or data structure.

2. Private context files live under a protected path, e.g.:

   ```
   /private/context.txt
   /private/api/context.txt
   ```

   Or on a separate authenticated subdomain:

   ```
   https://api.example.com/context.txt
   ```

3. When an AI agent requests a protected `context.txt` without credentials,
   the server returns:

   ```
   HTTP/1.1 401 Unauthorized
   WWW-Authenticate: Bearer realm="example.com", scope="context"
   Content-Type: application/json

   {"error": "authentication_required", "obtain": "https://example.com/account/tokens"}
   ```

4. The AI agent surfaces this to the user, who provides credentials. The agent
   then retries with the appropriate header:

   ```
   Authorization: Bearer <token>
   ```

5. The protected `context.txt` follows the same format as the public one.
   Its `## APIs` section links to further protected `api/context.txt` files,
   all served behind the same authentication.

### Security note

Protecting `context.txt` itself prevents an unauthenticated AI agent from
learning anything about the site's private data structure, endpoint paths,
parameter names, or response shapes. This is appropriate for regulated
industries, multi-tenant platforms, and any case where the data topology is
itself considered sensitive.

---

## Multiple APIs on one site

A site with multiple datasets or topics should give each its own `api/context.txt`,
grouped by section. The root `context.txt` links to all of them.

Example structure for a multi-section site:

```
/context.txt                        ← root
/style/context.txt                  ← shared visual style

/products/context.txt               ← products section
/products/api/context.txt           ← products API (public)

/orders/context.txt                 ← orders section
/orders/api/context.txt             ← orders API (requires auth)

/private/context.txt                ← private root (401 without credentials)
/private/customers/api/context.txt  ← customer data API (sensitive)
/private/reports/api/context.txt    ← reporting API (sensitive)
```

The public root `/context.txt` describes the public sections and signals that
private access exists. It does not link to or name the private paths.

---

## Relation to existing standards

| Standard | Purpose | Relation to context.txt |
|---|---|---|
| `robots.txt` | Tell crawlers what not to index | context.txt tells AI what to do and how |
| `llms.txt` | Link map of important pages for LLMs | context.txt goes further: APIs, domain vocabulary, auth, rendering |
| `agent-manifest.txt` | Permissions and allowed agent actions | context.txt is complementary: the semantic usage guide |
| `sitemap.xml` | List of URLs for crawlers | context.txt is richer: semantics, not just URLs |
| OpenAPI | Describe REST APIs in full detail | context.txt is a lightweight entry point; it can reference an OpenAPI spec |
| JSON-LD / schema.org | Structured metadata in pages | context.txt is site-level, not page-level |

---

---

## Minimal example

The smallest useful `context.txt` for a site with one public API:

```markdown
# My Recipe Site

A database of vegetarian recipes with nutritional information.

## APIs

- [Recipe API](api/context.txt) — search and retrieve recipes by ingredient, cuisine, or dietary tag
```

And the matching `api/context.txt`:

```markdown
# Recipe API

Read-only JSON API for recipe data.

[← My Recipe Site](../context.txt)

## Base path

- `/api`

## Authentication

- **Required:** no

## Endpoints

### `GET /api/recipes`

Returns a list of recipes. Supports `?cuisine=`, `?tag=`, and `?ingredient=` filters.

### `GET /api/recipes/{slug}`

Returns full detail for one recipe including ingredients, steps, and nutrition.

## Rate limiting

- 60 requests per minute per IP

## Cache behaviour

- `Cache-Control: public, max-age=3600`
```

---

## Changelog

| Version | Date | Notes |
|---|---|---|
| 0.1 | 2026-04-17 | Initial draft |
