# 日本民話データベース BluePrint / Data Contract

## 1. 目的

日本各地に残る民話・昔話・伝説・伝承を、地域・場所・登場人物・妖怪・神仏・テーマ・出典で横断検索できるデータベースとして蓄積する。

本データベースは単なる民話記事集ではなく、後続コンテンツの基礎データとして利用する。

将来的な接続先：

- 日本妖怪生息地図
- 神社データベース
- 地域別民話一覧
- 妖怪・怪異データベース
- 地図コンテンツ
- オカルト関連記事

基本原則は以下とする。

> **AIが民話を創作するのではなく、資料に存在する伝承を収集・整理・構造化する。**

---

## 2. 役割分担

### チャッピー

- 資料探索
- 出典確認
- 民話候補抽出
- 内容要約
- 地域・場所抽出
- 登場人物・妖怪・神仏抽出
- テーマ分類
- 類似伝承の関連付け
- 複数資料の照合
- 構造化JSON生成

### WordPress / HATAKITI.com

- JSON受信
- データ保存
- 民話ページ生成
- 地域・テーマ・登場存在による検索
- 地図表示
- 下書き・公開管理

### HATAKITI

- 必要に応じた確認
- 公開判断

---

## 3. 基本単位

1つの民話・伝承を1つの `FolktaleRecord` とする。

ただし同じ話が別地域・別資料に存在する場合、安易に1件へ統合しない。

原則：

> **地域・資料ごとの伝承差を残す。**

「同系統の話」と判断できる場合は、別レコードとして保存した上で `related_records` に関連を記録する。

---

## 4. Data Contract

チャッピーからClaude / WordPressへ渡す正式形式はJSONとする。

ファイル例：

```text
folktales/
  aomori/
    aomori-0001.json
    aomori-0002.json
  iwate/
    iwate-0001.json
```

基本構造：

```json
{
  "schema_version": "1.0",
  "record_id": "JP-FOLK-AOM-0001",
  "status": "draft",
  "title": "資料上の民話タイトル",
  "title_normalized": "検索用正規化タイトル",
  "summary": "資料内容を改変せず要約した日本語概要",
  "region": {},
  "locations": [],
  "characters": [],
  "beings": [],
  "themes": [],
  "story_type": [],
  "related_records": [],
  "sources": [],
  "ai_processing": {},
  "confidence": "high"
}
```

---

## 5. record_id

重複登録防止のため、すべての民話に固定IDを付与する。

形式：

```text
JP-FOLK-{PREF}-{NUMBER}
```

例：

```text
JP-FOLK-AOM-0001
JP-FOLK-IWT-0042
```

WordPressインポート時は `record_id` を一意キーとする。

同じIDが存在する場合は新規投稿ではなく更新候補として扱う。

---

## 6. title

タイトルは以下を分ける。

```text
title
資料に記載されているタイトル

title_normalized
検索・表示統一のための正規化タイトル
```

AIは原題を勝手に変更しない。

資料に題名がない場合のみ、内容を識別する仮タイトルを作成し、`title_origin` に `generated_for_identification` を記録する。

---

## 7. summary

概要はAIによる要約とする。

制約：

- 資料にない展開を追加しない
- 登場人物を勝手に補完しない
- 結末を創作しない
- 複数資料を無断で混合しない
- 不明な点は不明として残す

必要に応じて以下を持つ。

```json
"summary": {
  "text": "要約本文",
  "based_on_source_ids": ["SRC-001"]
}
```

---

## 8. 地域情報

地域は可能な限り構造化する。

```json
"region": {
  "prefecture": "青森県",
  "historical_province": null,
  "municipality": "○○市",
  "area_name": "○○地区",
  "source_description": "資料上の地域表記"
}
```

地域が旧国名のみの場合は旧国名を保存し、現代行政区分へ無理に変換しない。

---

## 9. 場所情報

伝承地・舞台となる場所は配列で管理する。

```json
"locations": [
  {
    "name": "○○川",
    "location_type": "river",
    "description": "資料に記載された場所情報",
    "latitude": null,
    "longitude": null,
    "precision": "region",
    "source_ids": ["SRC-001"]
  }
]
```

位置精度：

- exact
- approximate
- region
- unknown

場所が特定できない場合、AIが推測で座標を付与しない。

---

## 10. 登場人物

人間・歴史上の人物・固有名を持つ登場人物を管理する。

```json
"characters": [
  {
    "name": "太郎",
    "role": "村人",
    "attributes": [],
    "source_ids": ["SRC-001"]
  }
]
```

資料に名前がない場合は、資料上の表現をそのまま使用する。

例：

```text
老人
娘
村人
猟師
```

---

## 11. 妖怪・怪異・神仏・動物

後続の妖怪地図などに接続できるよう、人物とは別に `beings` として管理する。

```json
"beings": [
  {
    "name": "河童",
    "normalized_name": "河童",
    "type": "yokai",
    "attributes": ["水辺"],
    "source_ids": ["SRC-001"]
  }
]
```

type例：

- yokai
- monster
- ghost
- deity
- buddha
- animal
- supernatural_entity
- other

資料上の存在とAIによる一般分類は区別できるようにする。

---

## 12. テーマ分類

民話を横断検索できるようテーマを付与する。

例：

- 妖怪
- 河童
- 鬼
- 狐
- 蛇
- 山
- 海
- 川
- 禁忌
- 約束
- 恩返し
- 復讐
- 婚姻
- 死者
- 宝
- 起源

JSON例：

```json
"themes": [
  "妖怪",
  "水辺",
  "約束"
]
```

テーマはAIによる整理情報であり、原典の表現ではない場合があることを明示可能とする。

---

## 13. story_type

民話の性質を複数分類可能とする。

例：

- 昔話
- 民話
- 伝説
- 地域伝承
- 妖怪伝承
- 神話的伝承
- 由来譚
- 信仰伝承

AIが判断できない場合は無理に分類しない。

---

## 14. related_records

類似伝承は統合せず、関連として接続する。

```json
"related_records": [
  {
    "record_id": "JP-FOLK-IWT-0012",
    "relationship": "similar_theme",
    "note": "河童との約束という共通要素"
  }
]
```

relationship例：

- same_tradition
- regional_variant
- similar_theme
- same_being
- related_place

---

## 15. Source（最重要）

すべてのレコードには出典を必須とする。

```json
"sources": [
  {
    "source_id": "SRC-001",
    "title": "資料名",
    "publisher": "発行者・機関名",
    "author": "著者名",
    "publication_date": null,
    "source_type": "digital_archive",
    "url": "https://...",
    "accessed_date": "YYYY-MM-DD",
    "relevant_section": "該当箇所",
    "language": "ja"
  }
]
```

source_type例：

- government
- university
- museum
- library
- digital_archive
- book
- academic_paper
- local_history
- other

公開ページでは、利用条件に問題がない範囲で出典を明示し、可能な場合は原資料へのリンクを表示する。

---

## 16. AI処理情報

原典情報とAI処理を区別するため、以下を記録する。

```json
"ai_processing": {
  "summary_generated": true,
  "sources_merged": false,
  "translation_used": false,
  "notes": null
}
```

複数資料を統合している場合は明示する。

原則として、内容が異なる伝承を1件の民話として融合しない。

---

## 17. confidence

資料・地域・内容の確実性を表す。

- high
- medium
- low
- uncertain

特に以下の場合はconfidenceを下げる。

- 原資料が確認できない
- 地域が曖昧
- 伝承内容が二次資料のみ
- 複数資料で大きな差異がある

---

## 18. チャッピーによる収集フロー

### 地域指定収集

例：

```text
青森県の民話を20件収集
```

処理：

```text
資料探索
↓
候補抽出
↓
原典・掲載資料確認
↓
内容要約
↓
地域・場所抽出
↓
登場人物・存在抽出
↓
テーマ分類
↓
重複確認
↓
JSON生成
```

### テーマ指定収集

例：

```text
全国の河童伝承を収集
```

地域横断で収集し、同じ伝承と地域差を区別する。

---

## 19. WordPressインポート仕様

Claudeが実装するインポート機能は以下を満たす。

1. JSONファイルを読み込む
2. schema_versionを検証する
3. record_idで既存データを検索する
4. 存在しなければ新規作成
5. 存在すれば更新候補として処理
6. 原則として下書きで登録
7. 出典を投稿データとして保存
8. 地域・テーマ・存在を検索可能な分類として保存
9. locationsを将来の地図表示用データとして保存

AIが生成したデータを公開状態へ直接登録するかは、WordPress側で設定可能とする。

初期設定は `draft` とする。

---

## 20. WordPress上の表示構造

民話ページの基本構成：

```text
民話タイトル

地域
場所
分類
登場人物・存在

────────────

民話の概要

────────────

関連する民話

────────────

出典・参考資料
```

「AIによる要約」である場合は、その旨を明示できる。

---

## 21. データ収集の優先順位

初期収集では以下を優先する。

1. 国立・公的機関のデジタルアーカイブ
2. 国立図書館等の公開資料
3. 自治体・郷土資料
4. 博物館・文化施設
5. 大学・研究機関
6. 信頼できる民話資料

ブログやまとめサイトは原典確認の手掛かりとして利用しても、原則として唯一の出典にしない。

---

## 22. 最初の収集単位

一度に全国すべてを収集しない。

推奨単位：

- 1都道府県
- 1地域
- 1テーマ
- 10〜30件程度

例：

```text
青森県の民話 20件
岩手県の河童伝承 15件
全国の狐に関する民話 25件
```

データ品質を確認しながら段階的に蓄積する。

---

## 23. 将来的な接続

民話DBを基礎データとし、将来的に以下へ接続する。

### 日本妖怪生息地図

```text
FolktaleRecord
↓
beings
↓
locations
↓
Map Marker
```

### 神社データベース

```text
FolktaleRecord
↓
神仏・伝承地
↓
Shrine / Deity Database
```

### 地域ページ

```text
Prefecture
↓
Folktales
↓
Yokai
↓
Places
```

---

## 24. 基本原則

本データベースでは以下を厳守する。

> **AIが民話を創作しない。**

> **資料にない情報を補完しない。**

> **地域差・異伝を無理に統合しない。**

> **すべての民話に出典を持たせる。**

> **原典情報とAIによる要約・分類を区別する。**

最終目標は、地域・妖怪・テーマ・場所から自由に日本各地の民話をたどることができる、HATAKITI.comの基幹データベースを構築することである。
