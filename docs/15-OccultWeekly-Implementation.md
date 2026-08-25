# HATAKITI.com Blueprint

# 15 - 週刊オカルト新聞 Implementation

Version : 1.0

Implements docs/07-OccultWeekly.md (concept blueprint) as instructed —
this document explains the WordPress mapping and how to operate it.

---

## 1. Where it lives

All in `wp-content/plugins/hatakiti-core`:

```text
includes/occult-cpt.php               3 CPTs + occult_category taxonomy
includes/occult-meta-boxes.php        Native meta boxes for source/news item
includes/occult-rss-fetch.php         RSS fetch (fetch_feed()) + admin page
includes/occult-weekly-admin-form.php Dedicated 号編集 form (like 観劇記録 etc.)
```

Theme side is display-only: `single-occult_weekly.php`,
`archive-occult_weekly.php`, plus small additions to
`inc/template-tags.php` (card/label/list-row support,
`hatakiti_render_occult_article()`, `hatakiti_render_occult_sources()`).

## 2. Content types

- **occult_news_source** (ニュースソース) — `source_name` (title),
  `rss_url`, `website_url`, `enabled`. Native title + one meta box.
  Not public.
- **occult_news_item** (個々のニュース) — raw fetched material.
  `post_title` = original title, `post_content` = RSS summary **only**
  (never the feed's full `content:encoded`) — 指示書 §14. Meta:
  source_post_id, source_name, original_url, published_at, fetched_at,
  content_hash, issue_post_id (which occult_weekly, if any, currently
  uses this item). Categorized via `occult_category` (tag-style
  taxonomy, seeded with the 17 example categories from 指示書 §9, open
  to more). Not public — this is working material, not a page HATAKITI.
  com publishes on its own (docs/07: not a reprint of the source).
- **occult_weekly** (週刊号) — public. `issue_id`, `week_start`,
  `week_end`, `issue_date`, `articles_json`, `source_count`,
  `article_count`, `main_topic_count`. No native title/editor — see §3.

`status` from 指示書 §6 is WordPress's own draft/publish
(`post_status`), not a separate field — adding a second parallel status
concept would only create a place for the two to disagree.

## 3. Why occult_weekly has no native editor

Same reasoning as 観劇記録/映画記録/活動履歴: its real content — a set
of articles, each merging one or more source news items under a
headline and body — is a structured, repeating thing, not freeform
text. `includes/occult-weekly-admin-form.php` replaces the native
screen with one purpose-built page.

## 4. Issue-building workflow (指示書 §17, §19)

1. Set 対象期間 (week_start/week_end) and save.
2. A table of every `occult_news_item` published in that range (plus
   anything already linked to this issue) appears below, one row each:
   include? / 扱い (大見出し・主要記事・小記事) / グループ (free text) /
   並び順.
3. Saving builds/replaces `articles_json`: rows sharing the same 扱い +
   同じグループ text merge into **one** article citing every one of
   their source news items (指示書 §10's "複数ソースの統合" — done by
   HATAKITI naming the shared group, not by automatic clustering, since
   automatic AI clustering is explicitly out of scope this pass). An
   empty グループ means that row stays its own single-source article.
4. Once articles exist, a 本文編集 section appears with one textarea per
   article — nothing generates this text automatically; paste in
   whatever text (hand-written or produced elsewhere, e.g. ChatGPT) is
   meant to run for that article. Save again to store it.
5. Changing an item's 扱い/グループ later creates a *new* group key —
   any body text already written for its old grouping is not
   auto-migrated. Documented limitation, not a bug: re-grouping after
   text has been written means re-writing that text. Acceptable for an
   initial pass; a real merge-tracking UI would be the fix later.

`source_count`/`article_count`/`main_topic_count` are recomputed from
`articles_json` on every save.

## 5. Deduplication (指示書 §8)

Checked in order on every fetched feed item: (1) exact match on
`hatakiti_occult_original_url`, (2) match on `hatakiti_occult_content_hash`
(`sha256(title)`, catching a re-published story under a slightly
different URL). Either match skips the item; neither creates a new
occult_news_item post.

## 6. RSS sources actually verified (指示書 §16 — never guess a feed URL)

| Source | RSS URL | How confirmed |
|---|---|---|
| Webムー | `https://web-mu.jp/feed/` | Fetched directly — 200, valid WordPress-style RSS 2.0 XML |
| TOCANA | `https://tocana.jp/index.xml` | Found via the site's own `<link rel="alternate" type="application/rss+xml">` autodiscovery tag, then fetched directly — 200, valid RSS 2.0 XML |

Neither site's robots.txt disallows its feed path. Both were seeded as
occult_news_source posts, enabled, during setup.

## 7. Public page layout (指示書 §11, docs/07)

```text
週刊オカルト新聞 / 号タイトル
issue_id ・ 対象期間 ・ 発行日
HATAKITI OCCULT WEEKLY
────────────
今週の大見出し   (large tier articles, each with inline 情報源)
────────────
今週の注目情報   (medium tier)
────────────
その他の奇妙な話 (small tier, compact list)
────────────
出典一覧         (all sources, deduplicated, additional to the inline ones)
文責：チャッピー ＋ 注意書き
```

Every article — including 小記事 — shows its own 情報源 (source name +
original title, linked to the actual article URL, not the outlet's
homepage) inline, per docs/07's explicit "新聞全体の末尾に媒体名だけを
列挙する方式では不十分". The bottom 出典一覧 is additional, not a
replacement.

## 8. Automatic execution

Not built this pass — 指示書 §15 and docs/07 both explicitly defer daily/
weekly automatic collection and AI editing. RSS fetch is manual only
("最新ニュースを取得" button, 週刊オカルト新聞 → RSS取得). When that's
wanted, the natural place to hook in is `hatakiti_fetch_all_occult_sources()`
— it already exists as a plain function, callable from WP-Cron or a
ConoHa cron endpoint without changing its logic.

## 9. Navigation

Added "週刊オカルト新聞" to the global nav fallback, after 日本民話 —
same reasoning as that addition: one more item, not a restructuring of
what's already there.

## 10. Known limitations

- No AI-driven clustering, categorization, importance ranking, or text
  generation — 指示書 explicitly does not require these this pass.
  Grouping is manual (shared "グループ" label); body text is
  hand-entered or pasted in.
- No automatic daily/weekly execution (see §8).
- Re-grouping an item after its article already has body text does not
  migrate that text (see §4.5).
- Only two sources verified/seeded (Webムー, TOCANA) — more can be
  added as occult_news_source posts once their own RSS URLs are
  similarly verified; none should ever be guessed.
