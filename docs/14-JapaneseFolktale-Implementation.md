# HATAKITI.com Blueprint

# 14 - Japanese Folktale Database Implementation

Version : 1.0

Implements docs/12-JapaneseFolktale-DataContract.md and
docs/13-JapaneseFolktale-CollectionOperations.md. Read those first — this
document only explains how the WordPress side maps onto that contract and
how to operate it; it does not restate the data model.

---

## 1. Where it lives

Everything is in `wp-content/plugins/hatakiti-core`, per the instruction to
keep data logic out of the theme:

```text
includes/cpt-folktale.php           CPT + 3 taxonomies + prefecture/being-type
                                     constants + meta registration
includes/folktale-meta-boxes.php    Native wp-admin meta boxes
includes/folktale-json-import.php   The JSON importer (民話 → JSONインポート)
```

Theme side is display-only:

```text
single-folktale.php     公演タイトル → 地域/場所/分類/登場人物・存在 →
                         概要 → 関連する民話 → 出典・参考資料
archive-folktale.php    "日本の民話" listing + 都道府県/テーマ/存在 filters
```

## 2. Why the admin screen looks different from 観劇記録/映画記録/活動履歴

Those three CPTs replace WordPress's native title/editor screen entirely
with a dedicated form, because their fields are all flat scalars a fixed
form suits well. 日本民話's data is not flat — it has five different
repeating structured arrays (locations, characters, beings, sources,
related_records) — and both docs/12 §19 and the instruction itself ask
for the *normal* wp-admin screen to remain usable alongside JSON import,
explicitly warning against over-building the admin UI. So folktale kept
`title` + `editor` (title = 民話タイトル, editor = 民話の概要) and just
adds meta boxes:

- **民話ID・基本情報** — record_id, title_normalized, confidence,
  schema_version (read-only).
- **地域** — a `<select>` of all 47 prefectures (region.prefecture is a
  single value per record per docs/12 §8, not a list, so this is a plain
  field, not a taxonomy) plus historical_province/municipality/area_name/
  source_description text inputs.
- **場所・登場人物・登場する存在・出典・関連民話（JSON）** — one JSON
  textarea per array. This is the deliberate "JSONインポートで登録、
  WordPressでは必要箇所を修正でも構いません" compromise from the
  instructions: HATAKITI can hand-fix one entry without a bespoke
  add/remove-row UI. Invalid JSON in a textarea is rejected on save
  (previous value kept) rather than silently corrupting the field.

テーマ (folktale_theme) and 分類 (folktale_story_type) are genuinely flat
string lists (docs/12 §12/§13), so those use WordPress's own default
tag-style taxonomy box — no custom code needed, and it already scales to
a large, growing vocabulary via autocomplete.

## 3. 登場する存在 (beings) — the one taxonomy driven by JSON, not edited directly

`folktale_being` is a real taxonomy (needed so "妖怪から民話を探す" is a
normal WP_Query/archive, and so a being's own page can later list every
folktale that mentions it — docs/12 §23's map connection). But each
beings[] entry also carries `type`, `attributes`, `source_ids` that a
bare taxonomy term can't hold per-record. So:

- The taxonomy term = the being's `normalized_name` (its cross-record
  identity — "河童" is one term no matter how many records mention it).
- `type` (yokai/monster/ghost/deity/buddha/animal/supernatural_entity/
  other) is stored as **term meta** (`folktale_being_type`) on that term
  — the AI's classification of that being in general, updated whenever
  a newer import provides one.
- The full per-record detail (raw `name` as written in the source,
  `attributes`, `source_ids`) stays in `hatakiti_folktale_beings_json` on
  the post — the taxonomy is a queryable projection of that JSON, not an
  independent copy.

`hatakiti_sync_folktale_being_terms( $post_id )` rebuilds a post's
folktale_being terms from its current beings JSON every time the post is
saved — whether that save came from JSON import or a manual edit of the
JSON textarea — so there is exactly one source of truth.

## 4. Future map connection (docs/12 §23, §9 of the collection-ops doc)

Nothing UI-level was built for 日本妖怪生息地図 yet (not required this
pass). What this implementation guarantees for that future work:

- Every being a folktale mentions is reachable as a `folktale_being` term
  → `get_posts()` for that term → each post's `hatakiti_folktale_locations_json`
  → `latitude`/`longitude`/`precision` when present. That chain (存在 →
  民話 → 場所 → 座標) is exactly what docs/12 §23 asks to keep
  "機械的に取り出せる" — it works today via plain `WP_Query` + `json_decode()`,
  no extra schema needed.
- `being_location_relationship` (docs/12 §9) was intentionally **not**
  built — the instructions call it future-only, UI not required this
  pass. If it's needed later, it's a derived index over the data that
  already exists (beings × locations per post), not a change to how
  records are stored now.

## 5. JSON import behavior

`民話 → JSONインポート` in wp-admin. Accepts a file upload, pasted JSON
text, or both (file wins if both given). Three JSON shapes are accepted:
a single record object, a bare array of record objects (a collection
batch per docs/13), or `{"records": [...]}`.

Per record:

1. `schema_version` must be present and `"1.0"` — anything else is
   rejected with a per-row error, not a fatal stop (the rest of the
   batch still processes).
2. `record_id`, `title`, and a non-empty `sources` array are required
   (docs/12 §15: sources are the one field the contract calls
   "最重要"/required).
3. Looked up by `hatakiti_folktale_record_id` meta. No match → new
   **draft** post (regardless of a `"status":"publish"` in the JSON —
   see below). Match → the existing post is updated in place; **its
   post_status is never touched by an update**, specifically so a
   record HATAKITI has already reviewed and published can't be silently
   reverted to draft by a later re-import of the same batch.
4. `summary` (string or `{text, based_on_source_ids}`) becomes the post's
   `post_content`. Region, locations, characters, beings, sources,
   related_records, ai_processing all map to the meta keys in
   §2 above. themes/story_type become taxonomy term assignments
   (auto-creating new terms as needed — this is how the vocabulary is
   meant to grow, per docs/12 §12).

`hatakiti_folktale_allow_auto_publish` (a plugin filter, default `false`)
is the seam for eventually letting `"status":"publish"` in the JSON
actually publish on first import. Nothing in this pass enables it — per
the instruction, initial implementation never auto-publishes.

## 6. Navigation

日本民話 was added to the global nav fallback (functions.php), between
HATAKITIとは and 日々の所感 — the instruction listed 日本民話 alongside
the existing menu items as a placement candidate, and adding one item
doesn't restructure what's already there. If a different position (or
keeping it out of the global nav the way 活動履歴 is) is wanted instead,
that's a one-line change in `hatakiti_fallback_menu()` in
`wp-content/themes/hatakiti/functions.php` (or in the real nav menu in
wp-admin, once one exists there).

## 7. Known limitations of this pass

- No dedicated UI for 地図から民話を探す yet — out of scope per the
  instructions ("地図そのものの実装は必須ではありません").
- 神社データベース / 妖怪生息地図 downstream connections are conceptual
  only until those projects themselves are implemented; this pass only
  guarantees the data shape they'll need is already present.
- The prefecture filter on the archive only lists prefectures that
  already have at least one **published** folktale — by design, so the
  filter never implies data exists where it doesn't.
