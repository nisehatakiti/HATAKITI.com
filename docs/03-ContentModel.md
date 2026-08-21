# HATAKITI.com Blueprint

# 03 - Content Model

Version : 1.6

---

## 1. Content types

HATAKITI.com initially uses three principal content types.

1. DailyThought / BlogPost
2. TheatreViewingRecord
3. FilmViewingRecord

Theatre-related thoughts are not required to use a separate structured content type. They are ordinary posts with tags.

The first planned theatre essay is a general discussion of who a theatre performance belongs to, establishing the author's basic view that a performance is the director's work and that actors serve the work rather than using the stage primarily to make themselves stand out.

The implementation should favor simplicity. Supporting entities may be introduced where they make data entry or retrieval genuinely easier, but HATAKITI.com should not become an unnecessarily complex relational database.

---

# 2. DailyThought / BlogPost

Daily thoughts use a free-form blog format.

### Required / recommended fields

- Title
- Published date/time
- Body
- Categories
- Tags
- Featured image (optional)

There is intentionally no rigid form beyond the normal post structure.

DailyThought / BlogPost content is written and maintained by HATAKITI. ChatGPT may assist when explicitly requested, but it is not the default authoring workflow.

---

# 3. TheatreViewingRecord

Theatre / performance viewing records use a fixed but simple form so that the author's viewing history can be recorded consistently.

### Fields

| Field | Type | Notes |
|---|---|---|
| Theatre Group | text | Group name |
| Production Title | text | Title of the production / performance activity |
| Viewing Date | date | The date the author actually watched it |
| Viewing Method | enum | Theatre / Streaming / Recording / Other |
| Production Start Date | date | Scheduled run start, when known |
| Production End Date | date | Scheduled run end, when known |
| Venue | text | Venue name or location |
| Review / Thoughts | rich text | Free-form response |
| Tags | taxonomy | Optional |

### Important distinction

Viewing Date and Production Date Range are separate fields.

Example:

```text
Production: 2026-08-20 ～ 2026-08-25
Viewing date: 2026-08-23
```

This distinction is useful for preserving the author's actual viewing history.

The viewing record does not need to be automatically connected to every performer, theatre group, venue, or other related record.

TheatreViewingRecord content is written and maintained by HATAKITI.

---

# 4. FilmViewingRecord

Film records use a fixed but simple form.

### Fields

| Field | Type | Notes |
|---|---|---|
| Viewing Date | date | Date the author watched the film |
| Title | text | Film title |
| Director | text | Director name |
| Screenwriter | text | Screenwriter name, when known |
| Release Year | year | Original release year |
| Categories | multi-select taxonomy | Multiple values allowed |
| Cast | text | Main cast, when useful |
| Viewing Method | enum | Theatre / Streaming / Recording / Other |
| Review / Thoughts | rich text | Free-form response |
| Tags | taxonomy | Optional |

Film categories may include:

- Animation
- Horror
- Romantic comedy
- Drama
- Comedy
- Action
- Thriller
- Suspense
- Documentary
- Science fiction

The list can grow naturally as needed.

There is no requirement to maintain a normalized film / performer database merely for the sake of cross-linking.

FilmViewingRecord content is written and maintained by HATAKITI.

---

# 5. Theatre-related thoughts

Theatre-related writing should normally be ordinary posts rather than a separate structured content type.

The author should be able to write thoughts freely, without first deciding whether the article belongs to "Acting", "Voice", "Body", "Script", "Direction", or another predefined section.

Tags are used to organize the material after it has been written.

For example, an article may be tagged with:

- 演技
- セリフ
- 身体
- 感情
- 演出
- 台本
- 稽古

A single article can have multiple tags.

As the number of articles grows, tag archives can naturally form useful collections and reveal recurring themes in HATAKITI's thinking.

This approach is intentionally lightweight. The site does not need a complete theatre curriculum or a rigid hierarchy of topics at the beginning.

The content itself should remain personal, practical, and concise. Examples include:

- dialogue comes from emotion
- stage position and body direction can be understood as vectors of emotion
- consciously considering when to stop can make physical movement more expressive
- distance and orientation can express relationships
- voice volume and emotional intensity are different things

These are HATAKITI's own practical viewpoints. They do not need to be presented as universal academic theory.

### Initial essay concept: 演劇公演とは誰のものか

The first planned theatre essay establishes the general principle from which later acting discussions can branch.

Core ideas:

- A theatre performance is the director's work.
- The director gives the performance its overall artistic direction and determines how its elements become one work.
- Actors are parts of the work whose role is to express and realize what the director is creating.
- An actor's job is not to make themselves stand out for its own sake, but to prepare sufficiently for the expression the work requires and use their abilities for the work.
- Making an actor shine is fundamentally part of the directing side's responsibility; an actor should not unilaterally decide to make themselves the focus.
- At the same time, a director cannot know 100% of an actor's expressive possibilities in advance.
- Therefore, actors should actively show their abilities and possibilities during rehearsals.
- Rehearsal is a place to offer possibilities; the director decides what belongs in the final work.
- The basic distinction is: actors can actively present themselves in rehearsal, but in performance they serve the work.

The article should be written as HATAKITI's personal theatre viewpoint, not as an objective universal theory.

### Planned follow-up essay: 演技をしろ＞するな＞しろ

This title intentionally uses the established internet-slang-style structure "しろ＞するな＞しろ".

The concept describes a progression in acting practice:

1. **演技をしろ** — beginners are told to act, and often produce conspicuously theatrical speech, exaggerated expression, and the feeling of "performing acting".
2. **演技をするな** — because this is far from natural dramatic expression, the actor is told not to "act" but simply to be the character.
3. **演技をしろ** — once the actor can genuinely exist as the character, they can then act the character's behavior: not merely showing an emotion, but expressing what that person does in the situation.

The article should preserve this progression and the wording rather than replacing it with a more generic acting-theory title.

These essays form the beginning of the general-to-specific development of HATAKITI's theatre thoughts. Later topics may naturally extend into individual expression, emotion, body expression, movement and stillness, dialogue, timing, and relationships with other actors.

### Authorship / responsibility

The theatre essays are based on HATAKITI's own ideas and spoken / written rough concepts. HATAKITI provides the original thought and viewpoint; ChatGPT organizes and turns those ideas into readable articles.

Every published theatre essay should include a small credit:

> 文責：チャッピー

This indicates that the final wording and article composition are ChatGPT's responsibility, while the underlying ideas and viewpoints originate from HATAKITI.

The credit should not imply that ChatGPT is the source of HATAKITI's theatre philosophy. It is a writing / editorial responsibility marker.

---

# 6. Site-wide footer / co-creation statement

The site footer should contain the following statement on all pages:

> このページは、友達の少ないHATAKITIが、チャッピー（ChatGPT）とともに作成しています。

This is intentionally a light, humorous statement of the site's co-creation style. It should be presented unobtrusively in the footer rather than as a prominent promotional element.

---

# 7. ChatGPT → WordPress draft integration

HATAKITI.com should be designed so that ChatGPT can, when explicitly instructed by HATAKITI, create WordPress content as a **draft** through an authenticated API connection.

The intended workflow is:

```text
HATAKITI
   ↓
ChatGPT
   ↓
ChatGPT Action / authenticated API connection
   ↓
HATAKITI-specific WordPress API endpoint
   ↓
WordPress REST API
   ↓
WordPress draft
   ↓
HATAKITI reviews
   ↓
HATAKITI publishes manually
```

### Authentication / authorization principle

The connection should not give ChatGPT full WordPress administrator access.

Recommended approach:

1. Create a dedicated WordPress user for the integration, e.g. `chatgpt-editor`.
2. Grant only the minimum capabilities required for creating and editing drafts.
3. Use WordPress Application Passwords for the integration credential.
4. Keep the connection HTTPS-only.
5. Do not grant permission to manage users, plugins, themes, site settings, or other administrator functions.
6. Do not grant automatic publishing permission in the initial implementation.

The goal is a least-privilege integration in which ChatGPT can prepare content but HATAKITI retains final publication control.

### HATAKITI-specific API layer

Rather than exposing the entire WordPress REST API to ChatGPT, the preferred design is a small custom WordPress plugin that provides a limited endpoint such as:

```text
/wp-json/hatakiti/v1/draft
```

The endpoint should accept only the fields HATAKITI.com actually needs, for example:

- content type
- title
- body
- tags
- categories
- optional featured image information

The plugin then validates the request, adds content-specific defaults, and creates a WordPress draft through WordPress's own REST / application APIs.

For theatre essays, the integration should automatically append or otherwise ensure the required:

> 文責：チャッピー

credit.

### Content-specific workflow

**Theatre essays**

```text
HATAKITI gives rough idea
   ↓
ChatGPT writes / organizes article
   ↓
ChatGPT adds 文責：チャッピー
   ↓
Create WordPress draft
   ↓
HATAKITI reviews
   ↓
Publish manually
```

**Theatre viewing records / film records / daily thoughts**

HATAKITI remains the content owner and normal updater. ChatGPT may assist with structuring or data entry when explicitly requested, but publication remains under HATAKITI's control.

### Security principle

The initial integration must be **draft-only** from ChatGPT's perspective. Publishing remains a deliberate human action until there is a future reason to expand permissions.

The exact ChatGPT Action configuration, OpenAPI schema, custom WordPress plugin implementation, user capabilities, and credential handling should be finalized during implementation after the actual WordPress environment is available.

---

# 8. Supporting data

Categories and tags should be used where they make browsing or searching easier.

Tags are especially important for theatre-related thoughts because they allow the site to grow organically before a clear classification is known.

Named entities such as theatre groups, performers, directors, or venues may remain ordinary text fields unless there is a clear benefit to making them reusable data.

The implementation should avoid introducing a separate entity model simply because it is technically possible.

---

# 9. Relationship principle

The primary relationship is between HATAKITI and each piece of content.

```text
HATAKITI
  ├─ 日々の所感（HATAKITIが更新）
  ├─ 演劇についての文章（HATAKITI原案＋チャッピー文章化／文責：チャッピー）
  ├─ 観劇記録（HATAKITIが更新）
  ├─ 映画記録（HATAKITIが更新）
  └─ 活動・制作
```

Content may be cross-linked where useful, but relationships are optional rather than mandatory.

The site should prioritize easy creation, easy reading, and easy future retrieval over building a complex knowledge graph.

---

# 10. WordPress implementation direction

The final implementation target is WordPress.

The expected implementation pattern is:

- Normal WordPress Posts for DailyThought / BlogPost
- Normal WordPress Posts with tags for theatre-related thoughts
- Custom Post Types for TheatreViewingRecord and FilmViewingRecord where this improves input and archive UX
- Custom Fields for structured viewing data
- Categories / tags for lightweight organization
- Custom archive / search templates where useful
- Site-wide footer containing the co-creation statement
- Theatre essay template containing the "文責：チャッピー" credit
- Dedicated integration user with least-privilege capabilities
- WordPress Application Password for the integration credential
- HATAKITI-specific REST endpoint for draft creation
- ChatGPT Action / authenticated API connection to the dedicated endpoint
- Draft-only permission for the initial integration

The exact plugins and implementation stack should be decided during implementation after reviewing the current WordPress environment, plugin constraints, and performance requirements.

The guiding principle is to use the simplest WordPress structure that provides the required user experience while keeping the ChatGPT integration narrow, auditable, and safe.
