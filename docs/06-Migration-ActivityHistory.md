# HATAKITI.com Blueprint

# 06 - Activity History Migration (nisehatakiti.online → hatakiti.com)

Version : 1.0

---

## 1. What this covers

`nisehatakiti.online` is HATAKITI's older WordPress site, on the same
ConoHa account as hatakiti.com but a fully separate WordPress
install/database. It had already been used once before to migrate 観劇記録
(from category 演劇) and 映画記録 (from category 映画) into hatakiti.com.

This pass migrates HATAKITI's own **活動履歴** (activity history — things
he performed *in*, as opposed to things he watched) out of the "出演イベ
ント" category (10 posts; the union of its two sub-categories 舞台 and
おやぢ) into a new `activity_record` content type on hatakiti.com.

## 2. New content type

`activity_record` (wp-content/plugins/hatakiti-core/includes/cpt-activity-record.php):

- Ordinary WordPress title + editor + featured image + tags (not a
  dedicated fixed form like 観劇記録/映画記録 — this content type is
  intentionally lighter-weight per the request).
- One taxonomy, `activity_type` (活動種別): 出演 / 演出 / 制作 / 脚本 /
  その他, checkbox UI, extensible.
- One custom field, 関連リンク (`hatakiti_related_link`), a plain URL.
- Archive at `/katsudo-rireki/`, single posts under `/katsudo/{slug}/`.
- **Not** in the global nav. Linked from the `HATAKITIとは` page (「活動履歴
  を見る」) instead, per the requested `HATAKITIとは → 活動履歴 / 本棚`
  structure.

## 3. Field mapping (old → new)

| Old (nisehatakiti.online) | New (hatakiti.com `activity_record`) |
|---|---|
| post_title | post_title |
| post_content (Gutenberg paragraph/image blocks) | post_content, copied verbatim — nothing reworded or removed |
| post_date | post_date (preserved exactly, incl. when later published) |
| featured image | downloaded and re-uploaded as a new hatakiti.com attachment, set as featured image |
| post_tag | post_tag, carried over as-is |
| category (舞台 / おやぢ, both under 出演イベント) | `activity_type` = 出演, for all 10 — every post's own text confirms HATAKITI performed in it ("出演させていただいた", "踊ってきました", etc.); 演出/制作/脚本 were not evidenced anywhere so were not guessed |
| (no equivalent field existed) | 関連リンク left blank — none of the 10 source posts contained an external link |

All 10 posts are currently **drafts** on hatakiti.com (matching how the
earlier 観劇記録/映画記録 migration and the 演劇論 essay import both work:
HATAKITI reviews and publishes/schedules each one himself).

## 4. Old URL → new URL map

| Old URL (nisehatakiti.online) | Title | New URL (hatakiti.com, once published) | Status |
|---|---|---|---|
| /archives/2024/07/31/126/ | 朗読劇「夏の思ひ出」 | /katsudo/朗読劇「夏の思ひ出」/ | migrated (draft #253) |
| /archives/2024/08/13/129/ | 芸術文化集会 | /katsudo/芸術文化集会/ | migrated (draft #255) |
| /archives/2024/10/07/330/ | 森田和正プロデュース vol.４「天正のハムレット～夏目漱石推理帳～」 | /katsudo/森田和正プロデュース-vol-４「天正のハムレット～/ | migrated (draft #256) |
| /archives/2024/10/20/334/ | 渋谷ズンチャカ　2024 | /katsudo/渋谷ズンチャカ　2024/ | migrated (draft #258) |
| /archives/2024/11/02/340/ | いちかわ市民まつり　2024 | /katsudo/いちかわ市民まつり　2024/ | migrated (draft #259) |
| /archives/2024/11/16/382/ | Kalon Meraki vol.3 | /katsudo/kalon-meraki-vol-3/ | migrated (draft #260) |
| /archives/2024/12/23/361/ | 「ラッピングカーに父」 | /katsudo/「ラッピングカーに父」/ | migrated (draft #261) |
| /archives/2025/03/02/371/ | 劇団イヴァノヴィッチ第5回公演 「Moscow」 | /katsudo/劇団イヴァノヴィッチ第5回公演-「moscow」/ | migrated (draft #263) |
| /archives/2025/03/26/388/ | 朗読劇「春雷」 | /katsudo/朗読劇「春雷」/ | migrated (draft #265) |
| /archives/2025/03/30/386/ | 里見公園さくら祭り | /katsudo/里見公園さくら祭り/ | migrated (draft #267) |

(Slugs above are shown decoded for readability; the actual URLs are
percent-encoded UTF-8, same as the rest of the site.)

## 5. Redirects

`wp-content/mu-plugins/hatakiti-migration-redirects.php` was added directly
to **nisehatakiti.online** (a must-use plugin — loads automatically, no
activation step, and is trivially removable by deleting the one file). It
301-redirects only the 10 post IDs in the table above to their hatakiti.com
equivalent; every other URL on nisehatakiti.online (including the already-
migrated 映画/演劇 posts) is untouched — verified directly.

**Important caveat:** since the migrated activity records are still drafts,
these redirects currently point to not-yet-public pages. They become
genuinely useful once HATAKITI reviews and publishes the 10 records — until
then, a visitor following an old link gets redirected to a 404 on
hatakiti.com rather than the old (still-live) nisehatakiti.online page.
Recommend publishing these 10 before taking nisehatakiti.online further
offline.

## 6. What was intentionally left alone

- No nisehatakiti.online content was deleted. The 10 source posts, their
  media, and everything else on that site remain exactly as they were.
- StageArt's ConoHa environment (`~/stageart/`) was not touched at any
  point in this migration.
- The 5 categories/post types unrelated to this request (アニメ, おやぢ as
  its own thing beyond 活動履歴, 漫画, xo_event calendar entries, etc.)
  were left as-is — nothing there was migrated.
