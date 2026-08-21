# HATAKITI.com Blueprint

# 04 - UX / WordPress Implementation Guidance

Version : 1.0

---

## 1. UX principle

HATAKITI.com should feel like a personal archive rather than a corporate portal.

The top-level experience should make it easy to answer two questions:

1. What is HATAKITI?
2. What has HATAKITI watched, thought about, and learned?

Structured records should be visually simple to enter and pleasant to read afterward.

---

# 2. Home page direction

The home page should prioritize recent and meaningful content rather than presenting a generic corporate landing page.

Potential sections:

- HATAKITIとは
- 最新の日々の所感
- 最近の観劇記録
- 最近の映画記録
- 演劇の読み物 / 教本
- Search / Archive

The exact visual design remains open.

---

# 3. Theatre viewing form UX

The viewing-record form should be explicit and consistent.

Suggested input order:

1. 劇団名
2. 公演タイトル
3. 観劇日
4. 観劇方法
5. 公演日程
6. 会場
7. 感想
8. タグ

Viewing Method options initially:

- 劇場
- 配信
- 録画
- その他

The form should make clear that 観劇日 is the date the author viewed the work, while 公演日程 is the scheduled run.

---

# 4. Film viewing form UX

Suggested input order:

1. 視聴日
2. タイトル
3. 監督
4. 脚本
5. 公開年
6. カテゴリ
7. 出演者
8. 視聴方法
9. 感想

Viewing Method options initially:

- 劇場
- 配信
- 録画
- その他

Categories should be selectable multiple times.

Cast should allow multiple performer tags.

---

# 5. Search / archive UX

Search should eventually support combinations such as:

- theatre group
- production title
- viewing date
- viewing method
- venue
- film title
- director
- screenwriter
- release year
- film category
- performer
- theatre knowledge topic
- free-form tags

The site should support both keyword search and browsable archives.

Examples of useful future queries:

- Films featuring a specific actor
- Horror films watched by HATAKITI
- Theatre productions seen in a particular year
- Works by a particular theatre group
- Articles about acting / voice / direction

---

# 6. Theatre textbook UX

The theatre section should be navigable as a learning resource.

A topic page may contain:

- explanation
- examples
- related concepts
- links to other theatre articles
- links to relevant viewing records
- links to personal observations

The knowledge section should not be forced into a strict academic tone. It should preserve HATAKITI's own experience and practical perspective.

---

# 7. WordPress target

The final site should be implemented in WordPress.

However, the implementation should treat WordPress as the platform, not as the design source.

The existing WordPress site should be ignored during the redesign unless individual content is intentionally migrated later.

Claude should be instructed to build the site from this repository's blueprint rather than reverse-engineering the existing site's information architecture.

Likely implementation building blocks include:

- WordPress Posts for free-form thoughts
- Custom Post Types for theatre viewing, film viewing, and theatre knowledge
- Custom Fields for structured record data
- Taxonomies for categories, tags, people, theatre groups, genres, etc.
- Custom archive / search templates
- A custom navigation and visual design

The precise plugins and implementation stack should be decided during implementation after reviewing the current WordPress environment, plugin constraints, and performance requirements.

---

# 8. Separation from StageArt

No StageArt authentication, API, database, or domain model should be required for HATAKITI.com.

A simple link from HATAKITI.com to StageArt is allowed, and StageArt may be referenced in HATAKITI's activity/about content.

Any future data integration must be an explicit later decision, not an implicit dependency.
