# HATAKITI.com Blueprint

# 04 - UX / WordPress Implementation Guidance

Version : 1.1

---

## 1. UX principle

HATAKITI.com should feel like a personal archive rather than a corporate portal or a complex database.

The top-level experience should make it easy to understand:

1. What is HATAKITI?
2. What has HATAKITI watched, thought about, and learned?
3. What is HATAKITI currently doing?

The site should be simple enough that the author can continue adding content without feeling that every post is data maintenance.

---

# 2. Home page direction

The home page should prioritize recent and meaningful content rather than presenting a generic corporate landing page.

Potential sections:

- HATAKITIとは
- 最新の日々の所感
- 最近の観劇記録
- 最近の映画記録
- 演劇・演技について
- 活動・制作
- Search / Archive

The exact visual design remains open, but the overall impression should be personal and approachable.

StageArt may appear as a modest activity / creation item rather than as the main promotional element of the site.

---

# 3. Theatre viewing form UX

The viewing-record form should be explicit and consistent, while remaining quick to enter.

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

Do not require the author to select or maintain a separate database record for the theatre group or performers unless this becomes genuinely useful.

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

Viewing Method options:

- 劇場
- 配信
- 録画
- その他

Categories should be selectable multiple times.

Cast can initially be entered as ordinary text. A reusable performer database is not required.

---

# 5. Search / archive UX

Search should focus on practical retrieval of the author's own content.

Useful search / archive dimensions include:

- date
- category
- viewing method
- theatre group name
- production title
- venue
- film title
- director
- release year
- film category
- tags
- theatre note topic

The site should support both keyword search and browsable archives.

There is no requirement for a sophisticated relationship browser between every person, work, group, and article.

---

# 6. Theatre notes UX

The theatre section should be navigable as a collection of short, practical ideas.

It should not feel like an academic textbook.

A typical article may simply contain:

- one idea
- an explanation in HATAKITI's own words
- a practical example
- an observation from theatre experience

Topics can gradually grow as the author has something worth saying.

The content should preserve personal perspective and should not be padded merely to create a complete curriculum.

---

# 7. StageArt UX / promotion

StageArt is an independent product and should have a clear external link from HATAKITI.com.

The promotion should be subtle.

Possible locations:

- HATAKITIとは / 活動・制作
- a small StageArt section on the home page
- relevant personal posts
- a footer or other persistent but unobtrusive link

The site should avoid making StageArt the dominant call to action.

A visitor who is interested should be able to move naturally from HATAKITI.com to the independent StageArt site.

No StageArt authentication, API, database, or application infrastructure should be required by HATAKITI.com.

---

# 8. WordPress target

The final site should be implemented in WordPress.

WordPress should be treated as the platform, not as the design source.

The existing WordPress site can be rebuilt around this blueprint rather than preserving an unsuitable information architecture.

Likely implementation building blocks include:

- WordPress Posts for free-form thoughts
- Custom Post Types for theatre viewing and film viewing if they improve the input/archive experience
- Normal Posts with categories for theatre notes, unless a Custom Post Type is clearly better
- Custom Fields for structured viewing data
- Categories and tags for lightweight organization
- Custom archive / search templates
- A custom navigation and visual design

The implementation should favor the smallest number of plugins and the simplest maintainable structure that can deliver the desired experience.

---

# 9. Separation from StageArt

No StageArt authentication, API, database, or domain model should be required for HATAKITI.com.

A clear link from HATAKITI.com to StageArt is expected.

StageArt may be introduced as one of HATAKITI's activities and creations, but it remains a separate product, system, and codebase.

Any future data integration must be an explicit later decision, not an implicit dependency.
