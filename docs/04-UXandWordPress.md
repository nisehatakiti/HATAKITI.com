# HATAKITI.com Blueprint

# 04 - UX / WordPress Implementation Guidance

Version : 1.2

---

## 1. UX principle

HATAKITI.com should feel like a personal archive rather than a corporate portal or a complex database.

The top-level experience should make it easy to understand:

1. What is HATAKITI?
2. What has HATAKITI watched, thought about, and learned?
3. What is HATAKITI currently doing?

The site should be simple enough that the author can continue adding content without feeling that every post is data maintenance.

The visual design should be primarily black / very dark, with white or light-gray typography and theatrical lighting as a visual motif. The site should feel closer to a dark theatre space than to a conventional corporate website.

---

# 2. Home page direction

The current home page concept is:

1. Large centered HATAKITI main logo
2. Horizontal navigation menu
3. Short introductory statement
4. Three latest articles as cards
5. A simple entrance to theatre-related writing
6. A modest StageArt introduction / link
7. Footer

### Main navigation

- HATAKITIとは
- 日々の所感
- 演劇について
- 観劇記録
- 映画記録
- StageArt

The logo should be the visual focus at the top of the page.

The introductory text should retain the following tone:

> HATAKITIは演劇をこよなく愛する個人である。
> 
> ここはHATAKITI個人が演劇のことを考えたりする場所である。
> 
> 観てきたものや考えたことが雑多に並べられている場所だと思ってほしい。

The exact typography can be refined during visual design, but the deliberately personal and slightly informal tone should be preserved.

The home page should not be overloaded with category blocks or large corporate-style cards.

---

# 3. Theatre-related writing UX

The theatre section should **not** begin with a detailed predefined curriculum.

The author should simply write about whatever theatre-related thought is worth writing down at the time.

Articles are then organized primarily through tags.

Examples:

- 演技
- セリフ
- 身体
- 感情
- 演出
- 台本
- 稽古

The "演劇について" page may show a simple list or a small number of representative tags, but it should not require a detailed hierarchy.

As content accumulates, tag archives can naturally become the site's de facto index.

This is intentionally more flexible and realistic than deciding the complete classification in advance.

### Coming Soon

If a future topic has not yet been written, it may be shown as `Coming Soon`, but this should be used sparingly. There is no need to manufacture a full table of contents before content exists.

---

# 4. Theatre viewing form UX

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

# 5. Film viewing form UX

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

# 6. Search / archive UX

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

Tags should be especially useful for theatre-related writing, where they serve as the lightweight way to group thoughts after publication.

The site should support both keyword search and browsable archives.

There is no requirement for a sophisticated relationship browser between every person, work, group, and article.

---

# 7. StageArt UX / promotion

StageArt is an independent product and should have a clear external link from HATAKITI.com.

The promotion should be subtle.

Possible locations:

- HATAKITIとは / 活動・制作
- a modest StageArt section on the home page
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
- WordPress Posts with tags for theatre-related writing
- Custom Post Types for theatre viewing and film viewing if they improve the input/archive experience
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
