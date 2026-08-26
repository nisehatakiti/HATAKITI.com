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

「編集結果をWordPressに反映」する手段として、当初は2案（A: 号編集
フォームへの手動コピペ継続 / B: REST endpoint経由でのAI投稿）を挙げ、
判断待ちとしていた。

**2026-08-26の指示書で、案Bとは異なる第3の方式（案C）が明示的に指示され、
実装済みとなった**：WordPress管理画面から「AIで週刊号を作成」を実行する
と、WordPressサーバー自身が指定期間の`occult_news_item`をAI APIへ直接
送信し、応答をその場で`occult_weekly`のdraftへ変換する。§4.2で示した
「サーバー側からAI APIを直接呼び出す自律実行は設計に含めない」という
当初の判断は、この指示書によって明示的に上書きされた——ただし「自律
実行」ではなく「HATAKITIが管理画面から都度手動で起動する」形に限定
されており、docs/03の「AIは明示的指示時のみ動く・必ずdraft」という
原則そのものには反していない。実装の詳細は §11 を参照。

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

## 8. 決定が必要な事項

1. ~~週次AI編集結果のWordPress反映方法~~ → **2026-08-26に指示・実装済み**
   （§11参照、案C：管理画面からの都度手動実行）。
2. **毎日の自動取得の実行方式**：WP-Cronのみで妥協するか、ConoHa側の
   サーバーcron/webcronを追加設定するか（追加設定する場合、StageArtの
   既存cronに影響しないことを本番サーバー上で確認してから行う）。**未決定
   のまま** — 2026-08-26の指示書でも「今回、毎日の自動取得cronは実装しない」
   と明示的に据え置かれている。
3. **週次AI編集自体の自動実行（cron化）**：今回追加された新しい未決定
   事項。「AIで週刊号を作成」は現状、人がボタンを押した時だけ動く。
   将来cron化する場合、(a) どの頻度で実行するか、(b) AI API利用料が
   継続的に発生する点、(c) 生成された下書きを誰がいつ確認するか、を
   先に決める必要がある。

次回、HATAKITI側の指示を受けてから着手する。

## 9. 今回実装した範囲

- PC複数カラムの新聞レイアウト（`.hk-occult-grid`, `.hk-record-list--grid`
  — `wp-content/themes/hatakiti/single-occult_weekly.php`,
  `wp-content/themes/hatakiti/style.css`）。既存のCSSトークン・900px
  ブレークポイントの慣習をそのまま踏襲、データ構造・PHP処理側は無変更。
- 本ドキュメント（設計の記録）。

上記以外（cron登録コード）は、§8の決定を待ってから着手する。

---

## 11. AI週次編集の実装（2026-08-26）

指示書により、§4.2で保留していた「週次AI編集結果のWordPress反映方法」
に案C（管理画面からの都度手動実行・サーバー側から直接AI APIを呼ぶ）が
指示され、実装した。

### 11.1 ファイル

```text
includes/occult-ai.php             AI API呼び出し層（プロバイダー非依存）
includes/occult-weekly-ai-edit.php プロンプト生成・応答処理・管理画面
```

`includes/occult-weekly-admin-form.php` から
`hatakiti_finalize_occult_weekly_groups()`（articles_json保存・ニュース
紐付け・集計値再計算）と `hatakiti_get_occult_weekly_candidates()`
（対象期間のニュース抽出）を共通関数として切り出し、手動編集フォームと
AI経路の両方が同じ保存経路を通るようにした——AIが作ったdraftも、既存の
「号を編集」画面でそのまま続きを手編集できる。

### 11.2 AI編集フロー

```text
管理画面「AIで週刊号を作成」で対象開始日・対象終了日を入力
↓
対象ニュース件数をプレビュー表示（hatakiti_get_occult_weekly_candidates）
↓
「この期間のニュースでAI週刊号を作成する」を実行
↓
対象occult_news_item一覧をプロンプトに整形（id/媒体/タイトル/公開日時/要約/URL）
↓
hatakiti_call_occult_ai_text() でAI APIを呼び出す
↓
応答をJSONとして抽出・検証（hatakiti_extract_json_from_ai_text）
↓
articles_jsonへ変換・保存（既存のhatakiti_finalize_occult_weekly_groups）
↓
occult_weeklyをdraftとして新規作成
↓
「号を編集」画面で人間が確認・修正 → 手動で公開
```

自動公開は一切行わない（`wp_insert_post`の`post_status`は常に`'draft'`
とハードコードしており、AIの応答内容によって変わることはない）。cronに
よる自動実行も未実装 — 人が管理画面のボタンを押した時だけ動く。

### 11.3 入力データ

対象期間内で、まだどの号にも紐付いていない`occult_news_item`
（`hatakiti_get_occult_weekly_candidates()`、手動フォームと共通）。各
項目についてAIへ渡すのは、内部の投稿ID（`id`として）、媒体名、
タイトル、公開日時、RSS要約（`post_content` — 元記事全文ではない）、
元記事URL。

### 11.4 AI出力JSON仕様

```json
{
  "issue_title": "号タイトル案",
  "editorial_summary": "編集後記（2〜4文）",
  "articles": [
    {
      "headline": "見出し",
      "importance": "headline | major | minor",
      "body": "編集記事本文（300〜600字目安、複数ソースの要約・整理）",
      "source_item_ids": [123, 456]
    }
  ]
}
```

`importance`は指示書が使った呼称（headline/major/minor）をAIとの
やりとりでは使い、保存時に既存の`HATAKITI_OCCULT_TIERS`キー
（large/medium/small）へ変換する——AI側の語彙と既存実装のtier語彙を
分離し、既存の管理画面・公開テンプレートのコードは一切変更不要にした。

`source_item_ids`はプロンプトで提示した`id`のみを許可する
ホワイトリスト方式で検証し、存在しないid（AIの幻覚等）は黙って除外
する。見出しまたは有効な出典idを持たない記事は、記事ごと破棄する
（壊れた記事をdraftに残さないため）。

### 11.5 クラスタリング・重要度判定・記事生成のルール（プロンプト）

指示書の要求をシステムプロンプトへ翻訳して実装：

- 単純なタイトル類似での統合を禁止。人物・場所・日付・事件内容・
  固有名詞・発生経緯を考慮させる。
- 判断に自信が持てない場合は統合しない（誤統合より別記事化を優先）
  ことを明記。
- 重要度判定基準（社会的注目度・規模・情報源数・続報性・オカルト
  ジャンルとしての話題性・特異性・読者の興味）を明記。
- 元記事の長文コピー・引用を禁止し、複数情報の要約・比較・整理に
  よる独自記事を書くよう指示。
- 「報じられている」等、確認事実と未確認情報を区別する表現を使う
  よう指示。

### 11.6 AI呼び出し方式・APIキー管理

`includes/occult-ai.php`が唯一の呼び出し窓口
（`hatakiti_call_occult_ai_text()`）。Anthropic・OpenAIの2アダプターを
実装済みで、プロバイダー切り替えはコード変更不要（設定のみ）。

APIキー・モデル名・プロバイダーは、優先順に

1. `wp-config.php`の定数（`HATAKITI_OCCULT_AI_PROVIDER` /
   `HATAKITI_OCCULT_AI_MODEL` / `HATAKITI_OCCULT_AI_API_KEY`） — サーバー
   上にのみ存在し、GitHubには一切含まれない
2. wp-admin「週刊オカルト新聞 → AI設定」で入力するWordPressオプション
   （`autoload=no`、フォーム上もキー自体は再表示しない）

から読み込む。コード中にAPIキーを直書きした箇所はない。

### 11.7 テスト状況・既知の制限

**重要な制限**：本番のConoHaサーバーには現時点でAnthropic/OpenAIいずれ
のAPIキーも設定されていない（`env`・`wp-config.php`とも確認済み、
2026-08-26時点）。そのため、実際の外部AI API呼び出しそのものは
**今回のセッションでは実行・検証できていない**。

代わりに、AI応答を受け取った後の処理（JSON抽出・id検証・
クラスタ→articles_json変換・draft作成・公開ページ表示）を、
`hatakiti_process_occult_ai_response()`（API呼び出し部分から分離した
関数）に対して、想定される構造のAI応答を模したテキストを直接与える
形で実機テストした：

- テストデータ5件（うち2件は同一事件想定）を投入
- 合成AI応答（2件を1つのheadline記事に統合、他3件を独立記事に）を
  `hatakiti_process_occult_ai_response()`へ渡す
- 結果：draft作成・tier変換（headline→large等）・出典2件の紐付け・
  存在しないid（99999）の混入を検知して自動除外・全記事の
  news_item_ids紐付け・source_count/main_topic_count再計算・
  generated_at記録、いずれも正常動作を確認
- 一時的に公開して公開ページのレイアウト（大見出し・主要記事・情報源
  リンク・編集後記）も確認 → 正常表示
- `hatakiti_extract_json_from_ai_text()`について、素のJSON／
  \`\`\`json フェンス付き／JSON抽出不能な文章の3パターンをテストし、
  いずれも想定通り動作

**そのため、「実際のAI（Claude/GPT等）が本物のニュースを正しく
クラスタリング・執筆できるか」自体は未検証**。プロンプトは指示書の
要求を反映して作成したが、実際のAPI呼び出しによるチューニング・
調整は行っていない。HATAKITI側でAPIキーを設定次第、実データでの
検証を行う必要がある。

### 11.8 今後のcron自動化について

今回は実装しない（指示書の明示的な要求）。将来cron化する場合の想定：

- `hatakiti_generate_occult_weekly_draft_via_ai( $week_start, $week_end )`
  は既に単体の関数として存在するため、WP-CronまたはConoHa側cronから
  そのまま呼び出せる（管理画面のUIコードに変更は不要）。
- ただし、cron化はAI API利用料が定期的に発生することを意味する。
  頻度・コスト許容度をHATAKITI側で決める必要がある。
- 生成されたdraftを人間がいつ確認するかの運用（通知の要否等）も、
  cron化とあわせて設計する必要がある。
- §8-2（毎日のRSS自動取得）と同様、この決定も今回は保留する。
