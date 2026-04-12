# Brand SEO & AEO Audit Guidelines

## Table of Contents
1. [Overview](#overview)
2. [SEO Audit Checklist](#seo-audit-checklist)
3. [AEO (Answer Engine Optimization) Audit](#aeo-audit)
4. [Gap Analysis Framework](#gap-analysis-framework)
5. [Conclusions & Improvement Plan](#conclusions--improvement-plan)
6. [AI-Prompt Templates for Claude Code](#ai-prompt-templates)

---

## Overview

This guide provides a structured framework for auditing any brand's SEO (Search Engine Optimization) and AEO (Answer Engine Optimization) performance. Use this as a checklist-driven methodology to identify weaknesses, benchmark against competitors, and generate actionable improvement recommendations.

**Audit Scope:**
- On-page SEO elements
- Technical SEO infrastructure
- Content quality & keyword targeting
- E-E-A-T signals (Experience, Expertise, Authoritativeness, Trustworthiness)
- Local SEO (if applicable)
- Brand authority & backlink profile
- AEO readiness (featured snippets, People Also Ask, AI Overviews)

---

## SEO Audit Checklist

### 1. Technical SEO

| Element | What to Check | Pass/Fail | Priority |
|---|---|---|---|
| **Crawlability** | Can search bots access all key pages? Check robots.txt, XML sitemap | ☐ | High |
| **Site Speed** | Core Web Vitals (LCP < 2.5s, CLS < 0.1, INP < 200ms) | ☐ | High |
| **Mobile-Friendliness** | Responsive design, no layout shifts on mobile | ☐ | High |
| **HTTPS** | All pages served over HTTPS with valid certificate | ☐ | High |
| **Canonical Tags** | Proper canonical URL usage, no duplicate content issues | ☐ | Medium |
| **Schema Markup** | Structured data implemented for relevant content types | ☐ | High |
| **Redirects** | No chains (301→301→301), no broken redirects | ☐ | Medium |
| **404 Pages** | Custom 404 page exists, no excessive 404s in crawl | ☐ | Low |
| **URL Structure** | Clean, descriptive URLs (no dynamic parameters if avoidable) | ☐ | Medium |
| **Hreflang Tags** | Language/regional targeting if multi-market | ☐ | Low |

### 2. On-Page SEO

| Element | What to Check | Pass/Fail | Priority |
|---|---|---|---|
| **Title Tags** | Unique, keyword-rich, under 60 characters | ☐ | High |
| **Meta Descriptions** | Compelling, keyword-optimized, under 160 characters | ☐ | High |
| **Header Hierarchy** | Single H1 per page, logical H2-H6 structure | ☐ | High |
| **Internal Linking** | Logical link structure, anchor text optimized | ☐ | High |
| **Image Alt Text** | Descriptive alt attributes for all images | ☐ | Medium |
| **Content Length** | Sufficient depth (500+ words for pillar pages) | ☐ | Medium |
| **Keyword Usage** | Primary/secondary keywords naturally distributed | ☐ | High |
| **NLP/RTF** | Natural language flow, not over-optimized | ☐ | Medium |

### 3. Content Quality

| Element | What to Check | Pass/Fail | Priority |
|---|---|---|---|
| **Topical Authority** | Does the site cover topics comprehensively? | ☐ | High |
| **Content Freshness** | Are pages updated regularly? | ☐ | Medium |
| **Duplicate Content** | No plagiarized or thin/rewritten content | ☐ | High |
| **User Intent Alignment** | Content matches searcher intent (informational, transactional, etc.) | ☐ | High |
| **Readability** | Flesch-Kincaid score, short paragraphs, scannable formatting | ☐ | Medium |
| **Media Integration** | Images, videos, infographics enhance content | ☐ | Medium |
| **Citations/References** | Backing claims with authoritative sources | ☐ | Medium |

### 4. Off-Page SEO / Authority

| Element | What to Check | Pass/Fail | Priority |
|---|---|---|---|
| **Backlink Profile** | Domain Authority, referring domains, toxic links | ☐ | High |
| **Brand Mentions** | Unlinked brand mentions that could be converted to links | ☐ | Medium |
| **Competitor Backlinks** | Identify competitor link-building opportunities | ☐ | Medium |
| **Social Signals** | Social media presence and engagement | ☐ | Low |
| **Local Citations** | NAP (Name, Address, Phone) consistency for local brands | ☐ | Medium |

### 5. E-E-A-T Signals

| Element | What to Check | Pass/Fail | Priority |
|---|---|---|---|
| **Author Bio Pages** | Clear author expertise displayed | ☐ | High |
| **About Page** | Company expertise, team credentials, history | ☐ | High |
| **Contact Information** | Physical address, phone, email visible | ☐ | High |
| **Reviews/Testimonials** | Customer reviews on site or Google Business Profile | ☐ | Medium |
| **Trust Badges** | Security certifications, industry awards | ☐ | Medium |
| **Content Review Process** | Fact-checking, editorial calendar in place | ☐ | Medium |

---

## AEO (Answer Engine Optimization) Audit

AEO focuses on capturing featured snippets, "People Also Ask" boxes, and AI Overview placements — the increasingly dominant real estate on search results.

### 1. Featured Snippet Opportunities

| Check | Status | Notes |
|---|---|---|
| Does the brand rank in **position 1-10** for target queries? | ☐ | Required for snippet eligibility |
| Are there existing snippets for target keywords that competitors own? | ☐ | Identify gaps |
| Does content answer the query in **under 60 words** at the start? | ☐ | Snippet formatting |
| Is content structured with **numbered/bulleted lists** where applicable? | ☐ | Listicle queries |
| Are **table structures** used for comparison queries? | ☐ | Comparison queries |

### 2. People Also Ask (PAA) Analysis

| Check | Status | Notes |
|---|---|---|
| Brand appears in PAA boxes for brand-related queries? | ☐ | |
| Content addresses the sub-questions within PAA clusters? | ☐ | |
| PAA questions are answered in **conversational, direct style**? | ☐ | |

### 3. AI Overview Compatibility

| Check | Status | Notes |
|---|---|---|
| Content is structured in **concise paragraphs (40-90 words)**? | ☐ | Ideal chunk size for LLM extraction |
| **"How-to" and step-by-step** content is well-formatted? | ☐ | |
| Content includes **FAQ sections** that map to direct questions? | ☐ | |
| Content cites **authoritative, recent sources**? | ☐ | AI values recency and authority |
| **"About" / entity clarity** — clear what the brand/author is? | ☐ | Entity optimization |
| Topic coverage is **comprehensive** (not thin)? | ☐ | AI favors depth |

### 4. Structured Data for AEO

| Schema Type | Implemented? | Quality |
|---|---|---|
| FAQPage / FAQQuestion | ☐ | /5 |
| HowTo | ☐ | /5 |
| Article / NewsArticle | ☐ | /5 |
| Organization | ☐ | /5 |
| Person (authors) | ☐ | /5 |
| BreadcrumbList | ☐ | /5 |
| VideoObject (if applicable) | ☐ | /5 |
| Product / Offer (if e-commerce) | ☐ | /5 |

---

## Gap Analysis Framework

### Step 1: Identify Baseline Metrics

Collect the following data for the brand vs. **3-5 competitors**:

```
┌─────────────────────────┬──────────┬──────────┬──────────┬──────────┐
│ Metric                  │  Brand   │ Comp #1  │ Comp #2  │ Comp #3  │
├─────────────────────────┼──────────┼──────────┼──────────┼──────────┤
│ Domain Authority        │          │          │          │          │
│ Organic Traffic (est.)  │          │          │          │          │
│ Total Indexed Pages     │          │          │          │          │
│ Backlink Count          │          │          │          │          │
│ Referring Domains       │          │          │          │          │
│ Top 10 Rankings Count   │          │          │          │          │
│ Featured Snippets Owned │          │          │          │          │
│ PAA Appearances         │          │          │          │          │
│ Core Web Vitals (Pass)  │          │          │          │          │
│ Keyword Cluster Coverage│          │          │          │          │
└─────────────────────────┴──────────┴──────────┴──────────┴──────────┘
```

### Step 2: Keyword Gap Analysis

**Target Keywords by Intent:**

| Intent Type | Brand Rankings | Competitor Rankings | Gap? |
|---|---|---|---|
| **Informational** — "what is [topic]" | __/10 | __/10 | ☐ |
| **Navigational** — "[brand] login" | __/10 | N/A | ☐ |
| **Transactional** — "[topic] pricing" | __/10 | __/10 | ☐ |
| **Commercial Investigation** — "[topic] reviews" | __/10 | __/10 | ☐ |

### Step 3: Content Gap Analysis

- Which competitor content pieces rank but the brand has no equivalent?
- Which topics has the brand covered but ranks below competitors?
- Where does the brand have **thin content** (under 300 words) that competitors outrank?

### Step 4: Technical Gap Analysis

- Page speed percentile vs. competitors
- Schema markup count vs. competitors
- Index coverage vs. competitors
- Core Web Vitals pass rate

### Step 5: Authority Gap Analysis

- Backlink count ratio (brand vs. top competitor)
- Guest post / PR coverage vs. competitors
- Industry citation and mention volume
- Social following and engagement ratio

---

## Conclusions & Improvement Plan

### Summary Score Card

| Category | Score (0-100) | Key Finding |
|---|---|---|
| Technical SEO | __/100 | |
| On-Page SEO | __/100 | |
| Content Quality | __/100 | |
| E-E-A-T | __/100 | |
| Off-Page / Authority | __/100 | |
| AEO Readiness | __/100 | |
| **Overall SEO Score** | **__/100** | |

### Top 3 Strengths
1.
2.
3.

### Top 3 Weaknesses
1.
2.
3.

### Priority Improvement Roadmap

| Priority | Action Item | Expected Impact | Timeline |
|---|---|---|---|
| **P0 — Critical** | | | |
| | | | |
| | | | |
| **P1 — High** | | | |
| | | | |
| | | | |
| **P2 — Medium** | | | |
| | | | |
| | | | |
| **P3 — Low** | | | |
| | | | |
| | | | |

---

## AI-Prompt Templates for Claude Code

Use these ready-to-use prompts with Claude Code to automate parts of the audit.

### Prompt 1: Full Brand SEO Audit (General)

```
You are an SEO and AEO auditor. Conduct a comprehensive SEO and AEO audit
for the brand [BRAND NAME] based at [WEBSITE URL].

For SEO: Evaluate technical SEO, on-page SEO, content quality, E-E-A-T signals,
off-page authority, and backlink profile.

For AEO: Check featured snippet eligibility, PAA presence, AI Overview
compatibility, and structured data implementation.

Provide:
1. Score card (0-100) for each category
2. Top 3 strengths and top 3 weaknesses
3. A detailed gap analysis comparing against the top 3 competitors:
   [COMPETITOR 1], [COMPETITOR 2], [COMPETITOR 3]
4. Prioritized improvement roadmap with estimated impact
5. Specific action items for improving AEO (featured snippets, schema)

Use bullet points, tables, and clear section headers. Be specific and
data-driven — avoid generic advice.
```

### Prompt 2: Technical SEO Audit

```
Perform a technical SEO audit for [WEBSITE URL].

Check and report on:
1. Core Web Vitals (LCP, CLS, INP) — benchmark against industry standards
2. Crawlability and indexation issues
3. Page speed analysis and recommendations
4. Mobile usability issues
5. Schema markup coverage and validation
6. Redirect chains and 404 errors
7. HTTPS implementation
8. URL structure quality

For each issue found, provide:
- Severity: Critical / High / Medium / Low
- Recommendation with specific fix
- Estimated impact on rankings if fixed

Format as a prioritized action list.
```

### Prompt 3: AEO Audit — Featured Snippet Opportunity

```
Analyze [WEBSITE URL] for AEO (Answer Engine Optimization) readiness.

For each target keyword in this list:
[keyword 1]
[keyword 2]
[keyword 3]
[keyword 4]
[keyword 5]

For each keyword:
1. Is the brand currently in the top 10 results?
2. Who currently owns the featured snippet (if any)?
3. Does the brand's existing content use the optimal format to capture
   the snippet (paragraph, list, table)?
4. What specific changes are needed to the content to target the snippet?

Then provide a ranked list of:
- High opportunity snippets to target (brand already ranks 1-10)
- Medium opportunity snippets (brand ranks 11-30)
- New snippet opportunities the brand has no content for

Format in a table with keyword, current rank, snippet owner, content gap,
and required action.
```

### Prompt 4: Competitor Gap Analysis

```
Conduct a competitor gap analysis for [BRAND NAME] ([WEBSITE URL]) against:
[COMPETITOR 1]
[COMPETITOR 2]
[COMPETITOR 3]

Analyze:
1. Keyword overlap — which keywords do competitors rank for that the brand
   does not? Categorize by search intent.
2. Content depth — compare average word count, header structure, and media
   usage between the brand and competitors for top 10 shared keywords.
3. E-E-A-T signals — compare author credentials, citations, schema
   implementation, and trust signals between the brand and competitors.
4. Backlink gap — identify backlink sources competitors have that the brand
   does not. Prioritize by domain authority.
5. AEO presence — compare featured snippet ownership and PAA appearances
   between the brand and competitors.

Provide the output as:
- A comparison table across all four brands
- A ranked gap list with recommended actions
- A prioritized content creation roadmap based on the gaps found
```

### Prompt 5: E-E-A-T Audit

```
Perform an E-E-A-T (Experience, Expertise, Authoritativeness, Trustworthiness)
audit for [WEBSITE URL].

Evaluate:
1. EXPERIENCE — Does the site demonstrate first-hand experience?
   - Contact/author bios with real photos
   - "About Us" with company history and team details
   - Customer testimonials and reviews
   - Behind-the-scenes or case study content
2. EXPERTISE — Does the content demonstrate subject matter expertise?
   - Author credentials and industry background
   - References to certifications, degrees, publications
   - Content depth and accuracy
   - Internal and external citations
3. AUTHORITATIVENESS — Is the brand recognized as an authority?
   - Backlink profile and citation count
   - Industry awards and press coverage
   - Guest post and thought leadership presence
   - Social media authority
4. TRUSTWORTHINESS — Does the site signal reliability?
   - HTTPS, privacy policy, terms of service
   - Contact information and physical address
   - Review platform presence (Google, Trustpilot, etc.)
   - Clear editorial and fact-checking processes

Score each category 0-100 and provide specific improvement recommendations
for each E-E-A-T dimension.
```

### Prompt 6: Content Audit for AEO

```
Audit the content at [WEBSITE URL] for AI Overview and featured snippet
compatibility.

For each page, evaluate:
1. Chunk optimization — Are paragraphs 40-90 words with clear topic
   sentences? Is content scannable for LLM extraction?
2. Query-target alignment — Does the H1 and opening paragraph directly
   answer the target search query?
3. FAQ integration — Are common sub-questions answered within the content?
4. Schema implementation — Is structured data (FAQPage, HowTo, Article)
   properly implemented and validated?
5. Recency — When was the content last updated? Is it current enough for
   AI preference?
6. Entity clarity — Is the brand/author entity clearly defined in the
   content and schema?

Provide a page-by-page report with:
- Page URL and target keyword
- Current snippet position (if any)
- Content score for AEO (0-100)
- Specific improvement recommendations
- Priority ranking for content refreshes
```

### Prompt 7: Local SEO Audit (for local brands)

```
Conduct a local SEO audit for [BRAND NAME] at [WEBSITE URL] in [LOCATION].

Check:
1. Google Business Profile — Completeness, categories, photos, reviews,
   posts, Q&A
2. NAP Consistency — Is Name, Address, Phone identical across:
   - Website
   - Google Business Profile
   - Facebook
   - Yelp
   - Industry directories (Yellow Pages, etc.)
3. Local Citations — Are citations present on top 50 local directories?
4. Review Profile — Volume, recency, response rate, sentiment
5. Local Content — Does the site have location-specific landing pages or
   blog content targeting local keywords?
6. Geo-targeted Schema — LocalBusiness schema implementation

Provide a completeness score, gaps found, and prioritized action items.
```

---

## Appendix: Quick Reference Tools

| Purpose | Tool |
|---|---|
| SEO / Backlinks | Ahrefs, Semrush, Moz |
| Technical Audit | Screaming Frog, Sitebulb, Google PageSpeed Insights |
| Schema Validation | Google's Rich Results Test, Schema.org validator |
| AEO / Snippet Tracking | Semrush (Position Tracking), Ahrefs (SERP Features), AlsoAsked |
| Competitive Analysis | SimilarWeb, SpyFu, BuzzSumo |
| Core Web Vitals | Google PageSpeed Insights, Lighthouse, CrUX |
| Content Optimization | Surfer SEO, Clearscope, MarketMuse |
| Local SEO | BrightLocal, Whitespark, Google Business Profile |

---

*Generated for brand SEO/AEO audit use. For custom implementation, feed your specific brand data into the AI-prompt templates above.*