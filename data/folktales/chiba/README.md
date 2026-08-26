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

## ディレクトリ構成

```text
data/folktales/chiba/
├── README.md
├── master/
│   └── README.md
├── registry/
│   └── collection_status.csv
└── ichikawa/
    └── README.md
```

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
