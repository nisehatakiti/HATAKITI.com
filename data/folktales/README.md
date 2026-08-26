# 日本民話データ

このディレクトリは、HATAKITI.com「日本民話」データベースの構造化データ原本を管理する場所です。

## ディレクトリ構成

都道府県ごとにサブディレクトリを作成します。

```text
data/folktales/
├── aomori/
│   ├── batch-001.json
│   └── batch-002.json
├── iwate/
│   └── batch-001.json
└── README.md
```

## データ形式

各JSONファイルは、WordPress側の日本民話JSONインポート機能と同じData Contractを使用します。

- `schema_version`
- `record_id`
- `title`
- `sources`

は必須です。

`record_id`を一意の識別子として使用し、再インポート時は既存データを更新します。

## 役割分担

- GitHub内JSON：民話データの原本
- WordPress：公開・閲覧用データベース

今後、都道府県単位で民話データを収集し、バッチ単位のJSONとしてこのディレクトリに追加します。
