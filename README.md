# context.txt

A proposed standard for AI-readable website descriptions.

`context.txt` is a plain text file — written in Markdown — that a website places in its webroot to tell visiting AI agents what the site is, what APIs it exposes, and how to present its data visually. It is the entry point for an AI agent trying to understand and interact with a site programmatically.

---

## The problem

When an AI agent visits a website on behalf of a user, it has to figure out everything by itself: what the site is about, whether an API exists, how to call it, what the data means, and how to format the output. There is no standard way for a site owner to communicate any of this.

`robots.txt` tells crawlers what not to touch. `llms.txt` links to important pages. Neither describes how to *use* the site as a machine.

---

## The idea

Place a `context.txt` file at your webroot:

```
https://yoursite.com/context.txt
```

An AI agent reading it immediately knows what your site is, where your APIs are, how to authenticate, and how to render your data in a way that matches your brand — without the user having to explain any of it.

---

## Minimal example

```markdown
# Movie Database

A curated database of 80 well-known films spanning science fiction, action,
crime, comedy, horror, animation, and drama.

## APIs

- [Movie API](api/context.txt) — search and retrieve films by genre, decade, director, score, or tag
```

And `api/context.txt`:

```markdown
# Movie Database API

Read-only JSON API for film data.

[← Movie Database](../context.txt)

## Base path

- `/api`

## Authentication

- **Required:** no

## Endpoints

### `GET /api/movies`

Returns a list of films. Supports `?genre=`, `?decade=`, `?director=`, `?min_score=`, `?tag=`, `?language=`, and `?q=` filters.

### `GET /api/movies/{slug}`

Returns full detail for one film including synopsis, tags, runtime, and IMDb score.

## Rate limiting

- 60 requests per minute per IP
```

---

## How it works

The root `context.txt` is a navigation hub. It links to sub-files for APIs, site sections, and visual style. An AI agent follows exactly the links it needs for the task at hand — the same way a human navigates a website.

```
/context.txt                ← start here: what is this site
/api/context.txt            ← how to query the data
/style/context.txt          ← how to render the results
/blog/context.txt           ← what is in the blog section
```

Every sub-file links back to the root. No central registry, no configuration — just files and links.

---

## Private APIs

For sites where even the API structure is sensitive, `context.txt` itself can be placed behind authentication. The public root signals that private APIs exist without revealing anything about them. The server returns a standard HTTP 401 when an agent requests a protected file without credentials.

```
/context.txt                    ← public: describes the site, hints at private access
/private/context.txt            ← 401 without credentials
/private/api/context.txt        ← full API description, also protected
```

---

## Style guide

A `style/context.txt` gives AI agents a visual starting point when rendering data for a human. Colours, typography, badge design, table layout — the site owner defines the defaults, and the user can always ask for changes.

This means a user asking *"show me all sci-fi films from the 80s"* gets a table that looks like it belongs on the site, not whatever default styles the AI invents.

---

## Example prompt

A user pointing an AI agent at a site with `context.txt` needs no technical knowledge of the API. A natural-language request is enough:

> Read context.txt at http://example.com/context.txt, then show me all sci-fi films from the 1980s with an IMDb score above 8, styled as a table using the site's own style guide.

The agent reads the root `context.txt` to understand the site, follows the link to `api/context.txt` to learn the available filters, calls the API, then reads `style/context.txt` to render the results in the site's colours and layout — all without the user explaining any of it.

The same pattern works for more open-ended requests:

> Using context.txt at http://example.com/context.txt, find me the highest-rated animated films and present them nicely.

---

## Relation to existing standards

| Standard | Purpose | How context.txt relates |
|---|---|---|
| `robots.txt` | Tell crawlers what not to index | context.txt tells AI what to do and how |
| `llms.txt` | Link map of important pages for LLMs | context.txt goes further: APIs, auth, domain vocab, rendering |
| `agent-manifest.txt` | Permissions and allowed agent actions | Complementary — context.txt is the usage guide |
| OpenAPI | Full REST API description | context.txt is a lightweight entry point; can reference an OpenAPI spec |

context.txt is **complementary** to these standards, not a replacement. A site can have `llms.txt` for content navigation and `context.txt` for API-oriented interaction.

---

## Status

This is an early draft proposal. The [full specification](spec.md) covers:

- File format and discovery
- Root, API, and style file structure
- Authentication (public APIs and protected context files)
- Multiple APIs on one site
- Relation to existing standards

Feedback, issues, and pull requests are welcome.

---

## Files in this repo

| File | Description |
|---|---|
| `spec.md` | Full specification |
| `example/movies.json` | Dataset — 80 films with genre, tags, score, and synopsis |
| `example/api/index.php` | JSON API — genre, decade, director, score, tag, and full-text filters |
| `example/index.php` | Browser list view — filterable, sortable film table |
| `example/movie.php` | Browser detail view — single film page |
| `example/context.txt` | Root context file for the example site |
| `example/api/context.txt` | API context file describing all endpoints and filters |
| `example/style/context.txt` | Style guide — colours, badges, and table layout |
