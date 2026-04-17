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
/skills/context.txt         ← reusable task patterns for common queries
/mcp/context.txt            ← MCP server endpoint and available tools
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

## Skills

A `skills/context.txt` describes reusable task patterns an AI agent can follow when working with the site. Each skill defines a trigger — the kind of user request it handles — and a sequence of steps: which API calls to make and how to present the results.

Skills bridge the gap between a natural-language request and the API calls needed to fulfil it, without the user needing to know anything about the API structure.

```
/skills/context.txt    ← named task patterns for common queries
```

The [movie example](example/skills/context.txt) includes two skills: `top-picks` (highest-rated films by genre) and `hidden-gems` (well-regarded but lesser-known films).

---

## MCP

A `mcp/context.txt` advertises an MCP server the site provides. Instead of describing HTTP endpoints for an agent to call manually, it points directly to a structured tool interface — endpoint URL, available tools, and authentication.

```
/mcp/context.txt       ← MCP server URL, tools, and auth
```

For sites that offer both an HTTP API and an MCP server, `context.txt` can list both. The agent chooses the most appropriate interface for the task.

---

## Live demo

A working reference implementation is available at **https://context-txt.onrender.com**.

It is a movie database with a read-only JSON API, a browser list view, and a full style guide — all described via `context.txt`. The source is in the `example/` folder of this repo.

## Example prompt

A user pointing an AI agent at a site with `context.txt` needs no technical knowledge of the API. A natural-language request is enough:

> Read the context.txt at https://context-txt.onrender.com/context.txt — then show me all sci-fi films from the 1980s with an IMDb score above 8.0, rendered as a styled table using the site's own style guide.

The agent reads the root `context.txt` to understand the site, follows the link to `api/context.txt` to learn the available filters, calls the API, then reads `style/context.txt` to render the results in the site's colours and layout — all without the user explaining any of it.

A skill-based request works the same way:

> Read https://context-txt.onrender.com/context.txt and follow the instructions in the skills file to show me hidden gems.

If the agent does not apply the style guide automatically, a more explicit prompt works better:

> Read https://context-txt.onrender.com/context.txt and https://context-txt.onrender.com/style/context.txt. Show me all sci-fi films from the 1980s with an IMDb score above 8.0 as a self-contained HTML page. Use the exact colours, badge design, and table layout from the style guide. Output only the HTML — no explanation or commentary.

---

## Real-world scenario

A museum archive is a typical use case for `context.txt` at full depth. The same standard covers both the public-facing data layer and the private editorial backend.

**Public layer** — visitors, researchers, and journalists interact with the collection through AI:

```
/context.txt                   ← site overview: what the museum holds, what data is available
/collections/api/context.txt   ← search artworks, artefacts, and archive documents
/events/api/context.txt        ← exhibitions, tours, and public programmes
/news/api/context.txt          ← press releases and announcements
/skills/context.txt            ← common tasks: find works by artist, list current exhibitions
/mcp/context.txt               ← MCP server for direct structured access to the collection
/style/context.txt             ← museum visual identity for rendered output
```

**Private layer** (behind authentication) — curators and editors manage content through AI:

```
/private/context.txt               ← 401 without credentials
/private/cms/api/context.txt       ← CMS write API: create, update, publish
/private/skills/context.txt        ← editorial workflows: add exhibition, publish news article
/private/mcp/context.txt           ← MCP server with write access to the CMS
```

A single prompt is enough for either layer:

> Using context.txt at museum.example.com/context.txt, show me all currently running exhibitions with their opening hours.

> Using context.txt at museum.example.com/context.txt, add next month's sculpture exhibition to the events calendar and publish a news announcement.

The agent reads the root context, discovers the right layer based on credentials, loads the relevant skills, and completes the task — without the user knowing anything about the underlying APIs or CMS.

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
| `example/skills/context.txt` | Skills — top-picks by genre, hidden gems |
| `example/style/context.txt` | Style guide — colours, badges, and table layout |
