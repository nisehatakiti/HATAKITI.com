# HATAKITI.com Blueprint

# 03 - Content Model

Version : 1.2

---

## 1. Content types

HATAKITI.com initially uses three principal content types.

1. DailyThought / BlogPost
2. TheatreViewingRecord
3. FilmViewingRecord

Theatre-related thoughts are not required to use a separate structured content type. They are ordinary posts with tags.

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

---

# 6. Supporting data

Categories and tags should be used where they make browsing or searching easier.

Tags are especially important for theatre-related thoughts because they allow the site to grow organically before a clear classification is known.

Named entities such as theatre groups, performers, directors, or venues may remain ordinary text fields unless there is a clear benefit to making them reusable data.

The implementation should avoid introducing a separate entity model simply because it is technically possible.

---

# 7. Relationship principle

The primary relationship is between HATAKITI and each piece of content.

```text
HATAKITI
  ├─ 日々の所感
  ├─ 演劇についての文章（タグで整理）
  ├─ 観劇記録
  ├─ 映画記録
  └─ 活動・制作
```

Content may be cross-linked where useful, but relationships are optional rather than mandatory.

The site should prioritize easy creation, easy reading, and easy future retrieval over building a complex knowledge graph.

---

# 8. WordPress implementation direction

The final implementation target is WordPress.

The expected implementation pattern is:

- Normal WordPress Posts for DailyThought / BlogPost
- Normal WordPress Posts with tags for theatre-related thoughts
- Custom Post Types for TheatreViewingRecord and FilmViewingRecord where this improves input and archive UX
- Custom Fields for structured viewing data
- Categories / tags for lightweight organization
- Custom archive / search templates where useful

The exact plugins and implementation stack should be decided during implementation after reviewing the current WordPress environment, plugin constraints, and performance requirements.

The guiding principle is to use the simplest WordPress structure that provides the required user experience.
