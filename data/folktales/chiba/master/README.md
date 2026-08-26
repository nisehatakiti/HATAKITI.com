# 千葉県民話収集マスター

このディレクトリは、千葉県民話収集の母集団を保持するための場所です。

基準となる `chibaken_minwa_AllList_2019.xls` の各掲載レコードを、将来的にCSVまたはJSONへ変換して保存します。

想定カラム:

```text
source_record_id
region
municipality
title
source_book
original_row
```

`source_record_id` は元資料の掲載レコード単位で一意とし、千葉県については `CHB-000001` から `CHB-004067` までを採番する予定です。

> 注意: GitHubには元のExcelバイナリをそのまま登録せず、収集管理に必要な構造化データへ変換して登録する。
