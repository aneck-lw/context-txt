# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Formal Specification

The spec lives in `spec.md` in this directory. That is the authoritative document
for the standard — everything below in this file is working context and research notes.

## Project Concept

This project explores **context.txt** — a proposed standard analogous to `robots.txt`, but designed for AI agents rather than web crawlers. The idea: a website places a `context.txt` file in its webroot that tells visiting AI agents what the site is, what content and APIs are available, and how to interact with the site programmatically.

### Format: Markdown

The file uses Markdown (`.md`) rather than plain text or a structured format like YAML/JSON. Rationale:

- AI models are trained on Markdown and parse it naturally
- Links (`[label](url)`) are semantic — an AI can follow them to discover more
- Headers create parseable structure without inventing a new syntax
- Human-readable without any tooling
- Renders correctly if a human navigates to the URL directly

### Hierarchical Linking Model

The root `context.txt` is the entry point for the whole site. It links to:

- **Sub-area `context.txt` files** — each major section of the site can have its own `context.txt` describing what is in that section
- **API `context.txt` files** — each API the site exposes gets its own `context.txt` describing endpoints, parameters, and response formats

Every sub-area and API `context.txt` links back to the root `context.txt`. This mirrors how the web itself works and allows an AI agent to navigate a site's machine-readable layer the same way a human navigates HTML pages.

Example structure for a multi-section site:

```
/context.txt                  ← root: site overview + links to all sections and APIs
/blog/context.txt             ← what is in the blog section
/shop/context.txt             ← what is in the shop section
/api/context.txt              ← API overview and endpoint documentation
```

### Root context.txt Structure

```markdown
# Site Title

Short description of what the site is and who it is for.

## What you will find here

Description of the content and data available.

## APIs

- [API Name](api/context.txt) — one-line description

## Site sections

- [Section Name](section/context.txt) — one-line description
```

### Key Design Questions Still Open

- **Discovery**: Always at `/context.txt`? Should a `<link rel="ai" href="/context.txt">` meta tag also be defined?
- **Output format hints**: How does a site express preferred response format (Markdown vs. plain text vs. JSON)?
- **Rate limits / attribution**: Should these be in `context.txt` or a separate machine-readable block?
- **Trust model**: How should an AI agent decide whether to follow directives?
- **Versioning**: How to handle spec evolution gracefully?

### Relation to Existing Standards

| Standard | Purpose | Relation to context.txt |
|---|---|---|
| `robots.txt` | Tell crawlers what NOT to index | context.txt tells AI what TO do and how |
| `sitemap.xml` | List of URLs for crawlers | context.txt is richer: semantics, not just URLs |
| `llms.txt` | Link map of important pages for LLMs | context.txt goes further: APIs, domain vocab, hierarchical nav |
| `agent-manifest.txt` | Permissions and allowed agent actions | context.txt is complementary: the semantic usage guide |
| OpenAPI | Describe REST APIs | context.txt links to API docs; could embed or reference OpenAPI |
| JSON-LD / schema.org | Structured metadata in pages | context.txt is a site-level, not page-level signal |
| `humans.txt` | Credits for humans | context.txt is the machine-readable site guide |

### Landscape Research (April 2026)

The space is active but the specific gap context.txt fills is unoccupied. Key findings:

**[llms.txt](https://llmstxt.org/)** — the most adopted standard (~10% of websites, major adopters: Anthropic, Stripe, Cloudflare, Vercel). Markdown file at `/llms.txt`. Structure: H1 site name, blockquote summary, H2 sections with lists of links to important pages. It is a *content navigation guide* — "here are my important docs." No concept of APIs, data models, or domain vocabulary.

**[agent-manifest.txt](https://dev.to/jaspervanveen/agentstxt-a-proposed-web-standard-for-ai-agents-20lb)** — focused on permissions and agent actions: what agents may do, training/RAG consent, pointers to `API-Docs` and `MCP-Server`. More policy document than usage guide. Does not define what the API docs themselves should look like.

**ai.txt** — exists but framed purely as a permissions/consent layer (allow/disallow summarization, training, etc.). Not a usage guide.

**Various JSON proposals** (agent.json, ARA, agent-permissions.json) — more heavyweight structured formats, not widely adopted.

**The gap context.txt fills:** `llms.txt` tells an LLM where to look; `agent-manifest.txt` tells an agent what it is allowed to do; neither tells an agent *what the site is, what its data means, or how to use its API intelligently*. That is what context.txt does. The hierarchical linking model (root → section → api) is also not present in any existing proposal.

**Positioning:** context.txt is complementary to llms.txt, not competing. A site can have both: `llms.txt` for content/doc navigation, `context.txt` for API-oriented interaction and semantic understanding.

### Bringing the Spec to People

1. **GitHub repo** with the spec as README — credibility anchor, everything else links here
2. **Reference implementation** — Kill Team site (`/home/andi/KI_Projekte/killteam/webapp/`)
3. **DEV.to post** — the active community for this conversation; frame as *"llms.txt tells LLMs where to look — context.txt tells them what they're looking at"*
4. **Position as complementary to llms.txt** — easier adoption path than proposing a replacement
5. **Later:** engage with the Lightweight Agent Standards Working Group (active on arXiv as of 2026)

### Reference Implementation

The Kill Team Reference Database (`/home/andi/KI_Projekte/killteam/webapp/`) is used as the first real-world example. It is a purely data-driven site backed by MariaDB, making it a clean test case for the API-oriented use of `context.txt`.
