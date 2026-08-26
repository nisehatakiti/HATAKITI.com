# HATAKITI.com Blueprint

# 16 - 週刊オカルト新聞 Automation Design

Version : 1.0

docs/07-OccultWeekly.md は構想メモ、docs/15-OccultWeekly-Implementation.md
は「手動運用できる状態」の実装記録。本ドキュメントはその続きとして、
毎日の自動収集と、週次のAI編集処理を「実際に実装できる状態」まで設計する。

本ドキュメントは**設計の記録**であり、実装は既存Blueprintと矛盾しない
範囲・小さな単位でのみ進める（下記「決定が必要な事項」を参照）。

日本民話データベース（docs/12, docs/13）とは完全に別プロジェクトであり、
本ドキュメントの変更は民話側のデータ・処理に一切影響しない。

---

## 1. 現状（docs/15までで実装済み）

| 項目 | 状態 |
|---|---|
| CPT: occult_weekly / occult_news_item / occult_news_source | 実装済み |
| occult_category タクソノミー（17件シード） | 実装済み |
| RSS取得（Webムー・TOCANA、`fetch_feed()`） | 実装済み・手動ボタンのみ |
| 重複判定（original_url → content_hash） | 実装済み |
| 号編集フォーム（対象期間・記事選択・扱い・グループ化・本文編集） | 実装済み・全て手動入力 |
| 公開ページ（大見出し／注目情報／小記事／出典一覧） | 実装済み |
| 過去号アーカイブ | 実装済み |
| PC複数カラムレイアウト | **今回追加**（`.hk-occult-grid` / `.hk-record-list--grid`、900px以上で2カラム、以下は自然に1カラム） |
| 毎日の自動取得（cron） | 未実装 |
| AIによるクラスタリング・重要度判定・記事編集の自動化 | 未実装（意図的に見送り継続、下記§4参照） |

## 2. 情報取得データ構造（既存のまま・変更なし）

`occult_news_item` の meta（`includes/occult-cpt.php`）:

```text
hatakiti_occult_source_post_id   取得元 occult_news_source の投稿ID
hatakiti_occult_source_name      媒体名（表示用にコピー保持）
hatakiti_occult_original_url     元記事URL（重複判定の第一キー）
hatakiti_occult_published_at     元記事の公開日時（取得できた場合）
hatakiti_occult_fetched_at       HATAKITI.com側の取得日時
hatakiti_occult_content_hash     sha256(title) — 重複判定の第二キー
hatakiti_occult_issue_post_id    現在どの号に使われているか（未使用なら空）
```

post_title = 元タイトル、post_content = RSS要約のみ（`content:encoded` 本文は保存しない）。
既に指示書§8の要件（媒体名／元タイトル／元URL／公開日を必ず保持）を満たしている。追加の設計変更は不要。

## 3. 重複判定方式（既存のまま・変更なし）

`hatakiti_save_occult_news_item()` が (1) `original_url` 完全一致 → (2)
`content_hash` の順でチェックし、どちらか一致でスキップする。日次自動化
後も同じ関数をそのまま呼ぶだけなので、この方式に変更は不要。

## 4. 週間データの保存方式・AI編集処理・クラスタリング・重要度判定 — まとめて設計

指示書は「AIが週1回全データを解析し、クラスタリング・重要度判定・記事
編集を行う」ことを求めている。ここが唯一、既存Blueprintの原則と直接
関わる部分なので、設計の前提を明示する。

### 4.1 既存Blueprintにある「AIの働き方」の原則

docs/03-ContentModel.md §6・§8 に、このサイト全体でのChatGPT/AIの
位置づけが既に定義されている：

> ChatGPT may assist when explicitly requested, but HATAKITI is the
> content owner.
>
> HATAKITI provides the original thought and viewpoint; ChatGPT
> organizes and turns those ideas into readable articles.
>
> The initial integration is draft-only. HATAKITI remains responsible
> for final publication.

つまりこのサイトの設計原則は「AIが裏側で自律的に判断・生成・公開する」
のではなく、「HATAKITIが明示的に指示したときにAIが編集を手伝い、結果は
必ずdraftとしてHATAKITIが確認してから公開する」という一貫した形。
実際、`includes/rest-draft-endpoint.php`（`/wp-json/hatakiti/v1/draft`）
はまさにこの形で既に実装済み（ChatGPTが認証付きAPI経由でdraftを作成し、
公開は必ず手動、という流れ）。

日本民話DB・あの日の記録などの他コンテンツも、AI（ChatGPTとの対話）が
外部で構造化データを作り、それをHATAKITI.com側はJSONインポートや
専用フォームで**受け取るだけ**という一貫したパターンを取っている。
WordPress側からOpenAI/Anthropic等のAPIを直接呼び出す実装は、この
リポジトリのどこにも存在しない。

### 4.2 設計方針（既存原則に合わせる）

上記の一貫性を踏まえ、「週次AI編集処理」は次のように設計する：

```text
[毎日: 機械的]
RSS取得 → 重複除外 → occult_news_item蓄積
（WordPress内で完結、AI判断なし）

[週次: HATAKITIが明示的に開始]
HATAKITIがChatGPT（またはClaude）に「今週のオカルトニュースを編集して」
と指示
   ↓
その週の occult_news_item 一覧をAIに渡す
   ↓
AIがクラスタリング・重要度判定・大/中/小記事への編集を行う
（このやりとり自体はWordPressの外＝チャット上で行う）
   ↓
編集結果を occult_weekly の号としてWordPressに反映
   ↓
必ず下書き（draft）として保存 → HATAKITIが確認して公開
```

「編集結果をWordPressに反映」する手段は2案あり、どちらも既存の
draft-only原則を守るが、実装コスト・自動化の度合いが異なる：

| 案 | 方法 | 長所 | 短所 |
|---|---|---|---|
| A（現状） | 号編集フォームにHATAKITIが手でコピー＆ペースト | 追加実装ゼロ、既に動作確認済み | 手作業が残る |
| B | `/wp-json/hatakiti/v1/draft` と同型の専用REST endpoint（`/wp-json/hatakiti/v1/occult-issue-draft` 案）を新設し、AIが構造化JSON（articles配列）をPOSTしてdraft号を直接作成 | コピペ不要、docs/03§8の既存パターンをそのまま踏襲 | 新しい書き込み系認証エンドポイントを1つ増やす（Application Password発行等の運用設定が要る） |

**これは今回勝手に決めず、判断待ちとする（§8「決定が必要な事項」参照）。**
案Bは技術的には小さな追加（既存の`rest-draft-endpoint.php`とほぼ同じ形の
コードで済む）だが、「新しい外部書き込み経路を増やす」という運用上の
判断はHATAKITI側で決めるべき事項と考える。

いずれの案でも、**WordPressサーバー側から生成AI APIを直接呼び出す自律
実行は設計に含めない** — これは docs/03 の原則（明示的指示・draft-only）
から外れ、かつ指示書の以前の版でも「AI自動化は今回要求しない」と
明記されていたため、指示なく仕様を拡張しない。

### 4.3 週間データの保存方式

追加の中間ストレージは不要。`occult_news_item` の `hatakiti_occult_published_at`
（無ければ `hatakiti_occult_fetched_at`）を使い、号編集フォーム側で
既に「対象期間内の投稿一覧を表示する」処理が実装済み
（`occult-weekly-admin-form.php`）。「1週間分を蓄積する」は既存の
occult_news_item蓄積＋期間フィルタで既に満たされている。

## 5. 週刊号生成・WordPress投稿・アーカイブ（既存のまま）

`hatakiti_save_occult_weekly_articles()` が選択済み記事から
`articles_json` を構築し、`hatakiti_order_occult_weekly_archive()` が
`issue_date` 降順で過去号アーカイブを提供する。案A/Bどちらでも、この
保存経路自体に変更は不要（案Bはこの関数を呼ぶ入口を増やすだけ）。

## 6. 毎日の自動取得（cron）

### 6.1 選択肢

| 方式 | 概要 | 信頼性 | 実装コスト |
|---|---|---|---|
| WP-Cron | `wp_schedule_event()` でdaily登録、`hatakiti_fetch_all_occult_sources()`を呼ぶだけ | アクセスがある日しか発火しない（docs/07も既にこの弱点を指摘） | 最小 |
| ConoHa側の実行（サーバーcron / webcron） | サーバー側から `wp cron event run` または直接関数を叩くURL/コマンドを定時実行 | 確実 | サーバー設定（crontab等）の追加が必要 — 本番共有環境への変更 |

### 6.2 方針

WP-Cronのイベント登録自体（`wp_schedule_event()` を呼ぶPHPコード）は
安全でリバーシブルな変更なので今回実装する（§9）。ただし前述の通り
WP-Cronはアクセス依存のため、確実な毎日実行にはConoHa側のサーバー
cron（またはwebcron）が必要——これは本番共有サーバーの設定変更に
あたるため、**今回は実装せず、コマンド案のみここに記録し、実行前に
HATAKITI側の確認を取る**（StageArtが同じConoHaアカウント上にあり、
既存のcron設定に影響を与えないことを事前に確認する必要があるため）。

実行案（確認後に設定する場合の想定コマンド）：

```bash
# ConoHa側 crontab 例（毎日 6:00 に実行、実際の設定はHATAKITI確認後）
0 6 * * * $HOME/bin/wp --path=$HOME/public_html/hatakiti.com eval \
  'hatakiti_fetch_all_occult_sources();' >> $HOME/hatakiti-com/rss-fetch.log 2>&1
```

## 7. AIの役割（指示書の記述を実装可能な形に翻訳）

| 指示書の項目 | 実装可能な形 |
|---|---|
| テーマ分類 | occult_category タクソノミー（既存・手動 or AIが分類名を提案してHATAKITIが選ぶ） |
| 同一案件の判定・類似案件のクラスタリング | 号編集フォームの「グループ」欄（既存）— 同じ扱い＋同じグループ名の記事が1本に統合される。AIが「この5件は同一事件」と判断した結果を、この欄に反映する形 |
| 重要度判定 | 「扱い」欄（大見出し／主要記事／小記事、既存）— AIの判定結果をここに反映する形 |
| 記事編集 | 本文編集欄（既存・手書き/貼り付け） |
| タグ生成 | 現状タグ機能なし（occult_weeklyにタグタクソノミーは未実装）— 必要なら別途追加を検討、今回のスコープには含めない |

## 8. 決定が必要な事項（今回は仕様を勝手に拡張しない）

1. **週次AI編集結果のWordPress反映方法**：案A（手動フォーム入力を継続）
   か 案B（`/wp-json/hatakiti/v1/occult-issue-draft` のような専用REST
   endpointを新設し、AIが直接draft号を作成できるようにする）か。
2. **毎日の自動取得の実行方式**：WP-Cronのみで妥協するか、ConoHa側の
   サーバーcron/webcronを追加設定するか（追加設定する場合、StageArtの
   既存cronに影響しないことを本番サーバー上で確認してから行う）。

いずれも技術的な実装自体は小さく、既存のBlueprint・実装パターンと
矛盾しない範囲で対応可能。次回、HATAKITI側の指示を受けてから着手する。

## 9. 今回実装した範囲

- PC複数カラムの新聞レイアウト（`.hk-occult-grid`, `.hk-record-list--grid`
  — `wp-content/themes/hatakiti/single-occult_weekly.php`,
  `wp-content/themes/hatakiti/style.css`）。既存のCSSトークン・900px
  ブレークポイントの慣習をそのまま踏襲、データ構造・PHP処理側は無変更。
- 本ドキュメント（設計の記録）。

上記以外（cron登録コード、REST endpoint新設）は、§8の決定を待ってから
着手する。
