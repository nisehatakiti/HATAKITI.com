# 千葉県民話データ収集

## 目的
千葉県の民話収集を、元資料の掲載レコード単位で管理し、地域別の作業による収集漏れを防ぐ。

## 基準資料
- 資料名: 千葉県立図書館「千葉県の民話リスト」全リスト
- 元ファイル: `chibaken_minwa_AllList_2019.xls`
- 基準URL: https://www.library.pref.chiba.lg.jp/school/chibaken_minwa/index.html
- 測定済み総掲載レコード数: 4,067件
- 題名完全一致ユニーク数: 2,996件
- 同一地域＋同一題名の重複除外後: 3,792件
- 軽微な表記揺れ正規化後の題名候補: 2,975件

## 管理原則
収集完了は市区町村単位ではなく、基準資料の全掲載レコードを母集団として判定する。

各元資料レコードには `source_record_id` を付与する。

例:
- CHB-000001
- CHB-000002
- ...
- CHB-004067

## データ構成（2026-08-26改訂）

```text
data/folktales/chiba/
├── README.md
├── master/
├── registry/
├── batches/
│   └── <municipality>/
└── records/
    └── <municipality>/
```

- `batches/<municipality>/`：調査時点の候補・索引・収集結果を保存する調査ログ。原則として深掘り結果で上書きしない。
- `records/<municipality>/`：1伝承＝1ファイルの正本。サイト・検索・集計は原則こちらを参照する。

### 深掘りの扱い

深掘りは新しい伝承を追加することではなく、同一 `record_id` の情報を更新することとする。

標準的な `research_status`：
- `index_only`：索引等で存在確認のみ
- `source_confirmed`：出典資料を特定
- `text_confirmed`：本文または一次的な記述を確認
- `deep_dived`：複数資料を照合し、出典付きの確認事項を整理

資料に書かれていない内容を伝承本文として補完しない。解釈・仮説を保存する場合は確認済み事実とは別フィールドに明示する。

### 移行方針

既存の自治体別バッチは段階的に `batches/<municipality>/` へ移動する。白井市については既存の深掘り済みデータを優先して `records/shiroi/` に移し、今後の深掘りは既存レコードの更新として扱う。

## registry の status
- `pending`: 未確認
- `researching`: 調査中
- `collected`: 民話JSONとして収録済み
- `duplicate`: 既存収録データと同一伝承として統合
- `related`: 類話・別伝承として既存データと関連付け
- `unavailable`: 現時点で詳細確認不可
- `excluded`: 民話DB対象外

## 市川市の開始方針
最初の作業単位は市川市とする。ただし市川市の作業完了は、市川市に分類された元資料レコードがすべて `pending` 以外になった時点とする。

千葉県全体の第一次収集完了は、全4,067レコードについて `pending` と `researching` が0件になった時点とする。
