# 市川市民話収集

千葉県民話収集の最初の作業単位。

市川市に分類された基準資料レコードを対象に調査・JSON化する。

## 完了条件
市川市に属するすべての `source_record_id` が `pending` または `researching` 以外になること。

## データ配置
収集済みデータはバッチ単位のJSONとして、このディレクトリに追加する。

例:

```text
batch-001.json
batch-002.json
```

各民話JSONには、元資料との対応を追跡できるよう `source_record_id` または複数の `source_record_ids` を保持する。
