# HATAKITI.com Blueprint

# 03 - Content Model

Version : 1.0

---

## 1. Content types

HATAKITI.com initially uses four principal content types.

1. DailyThought / BlogPost
2. TheatreViewingRecord
3. FilmViewingRecord
4. TheatreKnowledgeArticle

Additional supporting entities such as TheatreGroup, Performer, FilmDirector, Screenwriter, Venue, Category, Tag, and Work may be introduced where useful for search and relationships.

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
- Related records (optional)

There is intentionally no rigid form beyond the normal post structure.

---

# 3. TheatreViewingRecord

Theatre / performance viewing records use a fixed form so that records can be compared, searched, and archived consistently.

### Fields

| Field | Type | Notes |
|---|---|---|
| Theatre Group | text / related entity | Group name | 
| Production Title | text | Title of the production / performance activity |
| Viewing Date | date | The date the author actually watched it |
| Viewing Method | enum | Theatre / Streaming / Recording / Other |
| Production Start Date | date | Scheduled run start |
| Production End Date | date | Scheduled run end |
| Venue | text / related entity | Venue name or other location |
| Review / Thoughts | rich text | Free-form response |
| Tags | taxonomy | Optional |

### Important distinction

Viewing Date and Production Date Range are separate fields.

Example:

```text
Production: 2026-08-20 ～ 2026-08-25
Viewing date: 2026-08-23
```

This distinction is required because the author may watch one performance within a longer production run.

---

# 4. FilmViewingRecord

Film records use a fixed form.

### Fields

| Field | Type | Notes |
|---|---|---|
| Viewing Date | date | Date the author watched the film |
| Title | text | Film title |
| Director | text / person tag | Director name |
| Screenwriter | text / person tag | Screenwriter name |
| Release Year | year | Original release year |
| Categories | multi-select taxonomy | Multiple values allowed |
| Cast | multiple person tags | Multiple performers allowed |
| Viewing Method | enum | Theatre / Streaming / Recording / Other |
| Review / Thoughts | rich text | Free-form response |
| Tags | taxonomy | Optional |

### Film categories

Categories are multi-select. Examples include:

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

The initial list is expandable.

### Cast

Cast members should be stored as reusable person tags rather than a single free-text field where practical. This enables future queries such as:

> Show me films featuring a specific performer.

---

# 5. TheatreKnowledgeArticle

The theatre section is a structured knowledge base / textbook rather than a simple blog category.

Articles should be organized by topic and learning path.

Initial topics may include:

- What is theatre?
- Acting
- Voice and breathing
- Body and movement
- Script / playwriting
- Direction
- Rehearsal
- Stagecraft
- Lighting
- Sound
- Costume
- Scenic design
- Small-theatre practice

Articles may link to related articles, viewing records, and daily thoughts.

The knowledge section should support gradual growth. It does not need to be completed before launch.

---

# 6. Supporting reusable data

Where practical, repeated data should be normalized or implemented as reusable taxonomies / reference records.

Examples:

- Theatre groups
- Performers
- Venues
- Film directors
- Screenwriters
- Film performers
- Categories
- Tags
- Genres / themes

The implementation should favor easy data entry while still making later search and archive functions possible.

---

# 7. Relationship examples

```text
TheatreViewingRecord
  ├─ TheatreGroup
  ├─ Production
  ├─ Venue
  ├─ ViewingMethod
  └─ Tags

FilmViewingRecord
  ├─ Film
  ├─ Director
  ├─ Screenwriter
  ├─ Cast[*]
  ├─ Category[*]
  └─ ViewingMethod

TheatreKnowledgeArticle
  ├─ Topic[*]
  ├─ RelatedArticle[*]
  ├─ RelatedViewingRecord[*]
  └─ RelatedBlogPost[*]
```

---

# 8. WordPress implementation direction

The final implementation target is WordPress, but this document describes the content model rather than a specific plugin implementation.

The expected implementation pattern is:

- Normal WordPress Posts for DailyThought / BlogPost
- Custom Post Types for TheatreViewingRecord, FilmViewingRecord, and TheatreKnowledgeArticle
- Custom taxonomies / fields for structured attributes
- Reusable person / group / venue terms or entities where appropriate
- Search / archive screens built around the structured data

The existing WordPress site should not constrain this model. The implementation may be rebuilt from the current design rather than preserving the existing information architecture.
