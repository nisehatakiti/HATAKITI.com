# HATAKITI.com Blueprint

# 05 - Implementation Notes

Version : 1.0

---

## 1. What this adds

This repository now contains a working WordPress implementation of the
blueprint (docs/01–04), under `wp-content/`:

```text
wp-content/
├─ themes/hatakiti/        HATAKITI.com theme (black / theatrical design,
│                          front page, article templates, archives, search)
└─ plugins/hatakiti-core/  観劇記録 / 映画記録 custom post types, structured
                           fields, film_genre taxonomy, least-privilege
                           ChatGPT-draft REST endpoint, WP-CLI essay importer
```

Nothing in `docs/01-Vision.md` through `04-UXandWordPress.md` was
simplified away or replaced with a different concept; where a decision was
genuinely unspecified (StageArt's URL), it was made configurable rather
than guessed. No discrepancy between the blueprint and the implementation
was found — this is a first implementation, not a rebuild.

## 2. How each blueprint requirement was implemented

| Blueprint requirement | Implementation |
|---|---|
| 黒基調デザイン、劇場照明のモチーフ | `themes/hatakiti/style.css` — dark palette + warm/cool spotlight gradients echoing the logo |
| ロゴ中央 → グローバルメニュー → 紹介文 → 最新記事3件 | `header.php` + `front-page.php` |
| グローバルメニュー統一レイアウト | `header.php` renders one `wp_nav_menu( 'primary' )`; every page shares `header.php`/`footer.php` |
| 最新記事カードは自動更新 | `hatakiti_latest_content_query()` — live `WP_Query` across `post` + both CPTs, no manual curation |
| 日々の所感 / 演劇について = 通常投稿 | Ordinary WordPress Posts, distinguished by category slug `nikki` / `engeki` |
| 演劇論はタグで整理、細分化しない | No sub-taxonomy; `post_tag` only. Starting tag set (演技/セリフ/身体/感情/演出/台本/稽古/視線/間/距離/熱量) is pre-seeded on plugin activation, but nothing stops new tags |
| 文責：チャッピー を必ず表示 | Preserved verbatim in the 8 existing essays. Theme visually re-styles that exact line (`hatakiti_style_credit_line`) and — only as a safety net for a *future* draft that omits it — auto-appends the same badge (`hatakiti_render_credit_badge_if_missing`). Existing article text is never rewritten |
| 予約投稿できる構造。実装と公開日時を分離 | Uses WordPress's native post scheduling as-is — no custom scheduling logic was built (or was needed). Every essay is imported as a **draft**; HATAKITI sets each one's date individually in wp-admin |
| 観劇記録：劇団名/タイトル/観劇日/公演期間/劇場/観劇方法/感想/タグ、観劇日≠公演期間 | CPT `theatre_record`, fields as distinct post meta (`hatakiti_troupe`, `hatakiti_viewing_date`, `hatakiti_run_start`, `hatakiti_run_end`, `hatakiti_venue`, `hatakiti_method`), 感想 = post editor, タグ = `post_tag` |
| 観劇記録一覧ページ | `archive-theatre_record.php`, sorted by 観劇日 |
| 映画記録：鑑賞日/タイトル/監督/脚本/公開年/ジャンル/出演者/鑑賞方法/感想/タグ | CPT `film_record`, same pattern; ジャンル = new `film_genre` taxonomy (non-hierarchical, open-ended, seeded with the blueprint's starter list) |
| 映画記録一覧ページ | `archive-film_record.php`, sorted by 鑑賞日 |
| StageArtへの導線、統合しない | Home page teaser + nav item link out only; Customizer setting `hatakiti_stageart_url` (empty by default → shows "Coming Soon" rather than a guessed URL) |
| Coming Soon は必要な箇所にとどめる | `hatakiti_coming_soon()` — used only for genuinely empty archives/pages, not manufactured everywhere |
| フッター文言 | `footer.php`, `.hk-footer-credit` (small, muted styling) |
| WordPress実装、複雑なDB構造を避ける | Two CPTs + plain post meta + two category slugs + one open taxonomy. No ACF or other fields plugin |
| ChatGPT → WordPress 下書き連携 | `wp-content/plugins/hatakiti-core/includes/rest-draft-endpoint.php`: `POST /wp-json/hatakiti/v1/draft`, draft-only (status is hardcoded, never taken from the request), gated behind a dedicated `hatakiti_create_draft` capability, not the full REST API |

## 3. Setup steps on the real WordPress install

1. Copy `wp-content/themes/hatakiti` and `wp-content/plugins/hatakiti-core`
   into the target WordPress install's `wp-content/`, then activate both
   in wp-admin.
2. Create two categories: **日々の所感** (slug `nikki`) and **演劇について**
   (slug `engeki`). Any other categories are up to HATAKITI.
3. Create the `HATAKITIとは` page (slug `about`), and, if wanted, two child
   pages `HATAKITIについて` / `活動・制作` per docs/02-SiteMap.md. Empty
   pages automatically show "Coming Soon" until written.
4. Set up Appearance → Menus: a "グローバルメニュー" menu assigned to the
   *primary* location with the six blueprint items (HATAKITIとは / 日々の
   所感 / 演劇について / 観劇記録 / 映画記録 / StageArt). Until this menu
   exists, the header falls back to the same six links automatically.
5. Set Settings → Reading → "front page displays" to your choice; either
   setting works because `front-page.php` is used regardless.
6. Appearance → Customize → HATAKITI 外部リンク: enter the StageArt URL
   once it exists. Leaving it blank keeps the home page teaser on
   "Coming Soon".
7. Import the 8 existing theatre essays as drafts:
   ```text
   wp hatakiti import_theatre_essays --dir=/path/to/content/theatre
   ```
   (If this repo's checkout *is* the WordPress `wp-content`'s parent
   directory, the default path already resolves correctly with no
   `--dir` needed. Note it's `--dir`, not `--path` — wp-cli reserves
   `--path` globally for the WordPress install location.) Every essay is
   created as a **draft** in 演劇について.
   Open each one in wp-admin, add whichever tags fit, and set its own
   publish date/time — using WordPress's built-in 予約投稿 — independent of
   this deployment. A `--dry-run` flag previews titles without writing
   anything.
8. For the future ChatGPT integration: create a WordPress user (e.g.
   `chatgpt-editor`) with the `hatakiti_chatgpt_editor` role installed by
   this plugin (read + edit_posts + upload_files + `hatakiti_create_draft`
   only — no publish, no others' content, no site settings), then issue
   that user an Application Password. Nothing else needs to change on the
   ChatGPT side is built here; only the receiving endpoint exists.

## 3.1. Later additions

- `activity_record` CPT (活動履歴) + `activity_type` taxonomy + a
  ブクログ-backed 本棚 page were added afterward, migrating HATAKITI's
  activity history off `nisehatakiti.online`. See
  `docs/06-Migration-ActivityHistory.md` for that migration's field
  mapping, URL map, and redirect setup — it is not repeated here.

## 4. Known follow-ups (not blockers for going live)

- No live WordPress instance was available in this environment, so this
  pass was implemented and reviewed by careful reading rather than
  click-tested in wp-admin. Do a real run-through after deployment (STEP
  10 in the working plan) before announcing the site.
- `HATAKITIとは` page content (bio, 活動・制作) is intentionally left blank
  for HATAKITI to write — inventing that content was out of scope.
- StageArt's URL is unset by design; add it via the Customizer once ready.
