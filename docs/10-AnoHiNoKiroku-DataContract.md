# あの日の記録：AI情報収集データ連携仕様

## 1. 目的

本書は、「あの日の記録」において、チャッピーが収集・整理した歴史情報を、Claudeが実装したHATAKITI.comの登録機能へ安全かつ機械的に受け渡すための共通データ形式（Data Contract）を定義する。

役割分担は以下とする。

- チャッピー：資料収集、海外資料の確認、翻訳、照合、重複整理、構造化データ作成
- Claude：データ仕様に対応したWordPress側のインポート・下書き保存・表示機能の実装
- HATAKITI：必要に応じて内容確認および公開判断

重要なのは、Claude側が個別の文章形式を解釈しなくても、決められた構造のデータを読み込めることとする。

---

## 2. 標準受け渡し形式

標準形式は **JSON** とする。

理由：

- AIが構造化して出力しやすい
- WordPress/PHP/JavaScriptで扱いやすい
- 配列形式で複数の出来事を一括投入できる
- 出典を複数保持できる
- 将来的にAPIや自動インポートへ拡張しやすい

補助形式としてCSVを使用することは可能だが、複数出典・人物・場所などの多対多構造があるため、正式な連携仕様はJSONとする。

---

## 3. 受け渡しの基本単位

基本単位は「1日＝1データファイル」とする。

例：

```text
anohi/
  1945/
    08/
      1945-08-01.json
      1945-08-02.json
      ...
    09/
      1945-09-13.json
```

ただし、実際のWordPress実装では、Claudeがこのディレクトリ構成を必須とする必要はない。

重要なのは、JSONの内容が日付単位で独立しており、再インポート可能であることとする。

---

## 4. 日付データの基本構造

```json
{
  "schema_version": "1.0",
  "project": "anohi-no-kiroku",
  "date": "1945-08-01",
  "timezone": "local_event_time",
  "research_status": "draft",
  "events": []
}
```

### 必須項目

- schema_version
- project
- date
- research_status
- events

### research_status

以下の値を使用する。

- `draft`：AIが収集した初稿
- `review`：確認待ち
- `approved`：内容確認済み
- `published`：WordPress公開済み

WordPressへの初期投入は原則として `draft` または `review` とし、自動公開しない。

---

## 5. Event（出来事）データ

各出来事は以下の構造を基本とする。

```json
{
  "event_id": "1945-08-01-001",
  "title": "出来事の短い名称",
  "summary": "いつ、どこで、何が起きたかを日本語で簡潔に記録する。",
  "category": "war_politics",
  "subcategories": [],
  "date_start": "1945-08-01",
  "date_end": null,
  "time_local": null,
  "time_precision": "day",
  "places": [],
  "regions": [],
  "countries": [],
  "people": [],
  "organizations": [],
  "sources": [],
  "translation_notes": null,
  "fact_notes": null,
  "confidence": "medium"
}
```

### event_id

原則：`YYYY-MM-DD-連番`

例：

- 1945-08-01-001
- 1945-08-01-002

同一出来事を複数資料から確認した場合でも、Eventは1件に統合し、sourcesを複数持たせる。

---

## 6. カテゴリー定義

Claude側では以下のコードを正式カテゴリーとして扱う。

### war_politics

- 戦争
- 軍事
- 政治
- 外交
- 条約
- 革命
- 内戦

### society

- 社会
- 災害
- 事故
- 生活

### culture

- 映画
- 演劇
- 音楽
- 文学
- 美術
- 娯楽

### science_technology

- 科学
- 技術
- 発明
- 産業

### sports

- スポーツ

### other

上記に分類できないもの。

必要に応じて `subcategories` に細分類を設定する。

例：

```json
"category": "culture",
"subcategories": ["film"]
```

---

## 7. 場所データ

出来事には複数の場所を設定できる。

```json
"places": [
  {
    "name": "場所名",
    "country": "国名",
    "region": "地域名",
    "latitude": null,
    "longitude": null,
    "precision": "city"
  }
]
```

precisionの例：

- exact
- facility
- city
- region
- country
- unknown

緯度経度が不明な場合は無理に推定せずnullとする。

---

## 8. 国・地域の扱い

出来事が複数国・複数地域に関係する場合に対応する。

```json
"regions": ["東アジア", "太平洋"],
"countries": ["日本", "アメリカ"]
```

国名は表示名として日本語を使用する。

将来的に国コードが必要になった場合に備え、Claude側は内部IDまたはISOコードを追加可能な設計とする。

---

## 9. 人物・組織

```json
"people": [
  {
    "name": "人物名",
    "role": "出来事との関係"
  }
],
"organizations": [
  {
    "name": "組織名",
    "role": "出来事との関係"
  }
]
```

役割が不明な場合は無理に推定しない。

---

## 10. Source（出典）データ

1つのEventは複数のSourceを持てるものとする。

```json
{
  "source_id": "src-001",
  "title": "資料名またはページ名",
  "publisher": "提供機関・発行者",
  "source_type": "official_archive",
  "language": "en",
  "url": "https://example.org/source",
  "accessed_at": "2026-08-25",
  "relevant_summary": "この資料が裏付ける事実を短く記録する。",
  "primary_source": true
}
```

### source_type

- primary_document
- official_archive
- government
- museum
- university
- research_institute
- newspaper
- news_agency
- book_reference
- secondary_reference
- other

### primary_source

一次資料または一次資料に直接基づく主要資料である場合はtrue。

---

## 11. 海外資料の翻訳情報

翻訳そのものを長文で保存することを標準仕様とはしない。

Eventのsummaryは日本語で記録する。

必要な場合のみ、翻訳上の注意を `translation_notes` に保存する。

例：

```json
"translation_notes": "原資料の軍事用語は、日本語では一般的な訳語を使用。"
```

原文を長文転載することは避ける。

---

## 12. 事実の不確実性と資料差異

資料間で日付・時刻・場所などに差異がある場合、AIが勝手に確定しない。

`fact_notes` に差異を記録する。

例：

```json
"fact_notes": "開始時刻について資料Aと資料Bで差異あり。本文では日付のみを確定情報として扱う。"
```

confidenceは以下を使用する。

- high：複数の高信頼資料または一次資料で確認
- medium：信頼できる資料で確認
- low：資料は存在するが追加確認が望ましい

`low` の情報はWordPress側で確認対象として視認できるようにすることが望ましい。

---

## 13. 重複統合ルール

チャッピー側では、同じ出来事を複数回登録しない。

以下が一致または強く関連する場合、同一Event候補として検討する。

- 日付
- 場所
- 関係人物・組織
- 出来事の内容

同一Eventと判断した場合は、Eventを統合し、sourcesを追加する。

Claude側のインポート機能は `event_id` をキーとして、再インポート時に重複投稿を作らず更新できる設計とする。

---

## 14. WordPress側へのインポート要件

Claudeは以下を実装対象とする。

### インポート

- JSONファイルのアップロード
- JSON内容のバリデーション
- schema_version確認
- 必須項目確認
- Event単位の登録
- 出典データの紐づけ

### 再インポート

同じevent_idが存在する場合は、新規作成ではなく更新または差分確認を行う。

### 初期公開状態

インポート直後は原則としてWordPressの下書き状態とする。

### エラー処理

不正なJSONや必須項目不足の場合、どのEvent・どの項目でエラーになったかを管理画面に表示する。

---

## 15. WordPressに求める表示構造

日付ページでは、チャッピーが渡すJSONのカテゴリーをそのまま表示できるようにする。

例：

# 1945年8月1日

## 今日の世界

地域一覧・地図。

## 戦争・政治

Event一覧。

## 社会

Event一覧。

## 映画・演劇・文化

Event一覧。

## 科学・技術

Event一覧。

## スポーツ

Event一覧。

## 出典

各Eventに紐づくSourceを表示し、外部資料への導線を設ける。

---

## 16. チャッピーからClaudeへ渡す完成形

最終的な受け渡しは、原則として以下の3点とする。

### ① 日付単位JSON

WordPressへ直接インポート可能な構造化データ。

例：

`1945-08-01.json`

### ② 収集メモ（必要な場合）

JSONに入れるべきではない調査上の注意点。

例：

- 追加資料の候補
- 資料間の大きな差異
- 今後確認したい事項

### ③ インポート指示

Claudeへの短い指示。

例：

> 添付したJSONを「あの日の記録」の1945年8月1日としてインポートしてください。既存のevent_idがある場合は重複登録せず更新してください。インポート後はすべて下書き状態にしてください。

---

## 17. 運用フロー

```text
HATAKITIが対象日付を指定
        ↓
チャッピーが資料収集
        ↓
海外資料を含めて確認・翻訳
        ↓
事実を抽出
        ↓
出典を紐づけ
        ↓
重複整理
        ↓
Data Contract準拠JSONを作成
        ↓
ClaudeがJSONをインポート
        ↓
WordPress下書き生成
        ↓
HATAKITIが確認
        ↓
公開
```

---

## 18. 最初の実証

最初の実証データは **1945年8月1日** とする。

チャッピーは本仕様に従って1日分のJSONを作成する。

ClaudeはそのJSONをエラーなく取り込み、WordPress上で日付ページとEvent・Sourceを正しく表示できることを確認する。

この1日分をData Contractの実証とし、必要な修正を行った後、1945年8月1日〜9月13日の44日間へ拡張する。

---

## 19. 基本原則

この仕様の目的は、AI同士が自然言語の説明を解釈し続けなくても連携できる状態を作ることである。

したがって、今後の連携では、原則として次の考え方を維持する。

> **チャッピーは事実と出典を構造化する。Claudeは構造を解釈せず、そのまま登録・表示できるようにする。**

これにより、調査方法やAIモデルが変わっても、「あの日の記録」のデータ資産を継続して利用できる状態を目指す。
