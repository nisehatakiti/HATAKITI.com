# 市川・浦安民話調査 進捗記録

更新日: 2026-08-26

## 現在の到達点

市川市民話の深掘り調査は、**バッチ7まで完了**。
次回は **市川バッチ8の先頭から再開**する（新規テーマは未選定。千葉県立図書館の市川市民話索引 https://www.library.pref.chiba.lg.jp/school/chibaken_minwa/ichikawa.html を再確認し、バッチ1〜7・records/ichikawa/で未着手の題名から選定する）。

## 完了済みバッチ

- バッチ1〜5: 深掘り・整理済み（バッチ1・2は2026-08-26に重複解消、下記参照）
- バッチ6: 全7件を一巡完了（重複解消済み、下記参照）
- バッチ7: 全13件を一巡完了（印内の重右衛門伝承群の個別話、下記参照）

## バッチ7の処理内容（2026-08-26実施）

千葉県立図書館の市川市民話索引を直接確認し（WebFetchで取得、推測なし）、既存の調査ハブrecord（`chiba-ichikawa-innai-juemon-traditions`、「個別話は統合せず共通タグで横断接続する」と定義済み）に対応する個別話13件を新規登録した。

| record_id | タイトル | 状態 |
|---|---|---|
| chiba-ichikawa-innai-no-jiimu-san | 印内のじいむさん | partial（重右衛門との関係は未確定のため断定せず） |
| chiba-ichikawa-juemon-uma-no-suki-na-aomon | 印内の重右衛門　馬のすきな青いもんのこと | partial |
| chiba-ichikawa-juemon-sannenkan-mo-isoro | 印内の重右衛門　三年間も居候したこと | partial |
| chiba-ichikawa-innee-no-juemudon | いんねえのじゅえむどんの話 | partial |
| chiba-ichikawa-juemon-9wa-01〜09 | 印内の重右衛門ばなし九話　その一〜その九（計9件） | partial |

全件、本文未確認・出典（房総の民話／千葉県の民話　続／読みがたり千葉のむかし話）と索引上の題名のみを記録し、題名からの筋・オチの推測補完は行っていない。九話シリーズおよび2話は`related_records`（relationship: same_tradition）でハブrecordへ接続した。

**対象外として保留した2件**（バッチ7ファイル内`out_of_scope_notes`に記録）：
- 生食と七次の八幡溜：出典が白井市の資料で、既に`records/shiroi/ikezuki-nanatsugi-hachimandame.json`としてcanonical化済み。市川側に重複recordは作成しない。
- 池和田落城の哀話：「池和田」は市原市の地名の可能性が高く、市川市の伝承と断定できないため今回は対象外。将来、市原市収集時に別途確認する。

## バッチ6と浦安市一次収集の重複解消（2026-08-26実施）

市川バッチ6（`ichikawa/2026-08-batch-006-waterfront-urayasu-connection.json`）は「市川・浦安の水辺接続」テーマで浦安市域の伝承も深掘りしていたが、これとは独立に進んでいた浦安市の一次収集（`urayasu/2026-08-batch-001`, `-002`, `-008`）が同じ千葉県立図書館索引（市川市・浦安市）を別々に拾っており、確認の結果、以下の重複が判明した。

**record_id完全衝突（→ 市川バッチ6側に統合、浦安側はdeduplicated_records化）**

| record_id | 統合先 |
|---|---|
| `chiba-urayasu-hebi-no-hashi`（蛇の橋） | ichikawa/batch-006を正本とし、urayasu/batch-001の重複recordを削除・`deduplicated_records`に記録 |
| `chiba-urayasu-hebi-no-awatori`（蛇の泡とり） | 同上 |

判断根拠：両ファイルとも`research_status: partial`で優劣がつけにくかったため、(1) 先に作成されたのはichikawa/batch-006（2026-08-26 10:41、urayasu/batch-001は11:04）、(2) 内容もichikawa/batch-006側がsources件数・research_missing・verification_locations等でより詳細、の2点から市川側を正本とした。地点情報（locations）自体は両者で矛盾なし。

**同一タイトル・別record_id（本文未照合のため統合せず、related_records: variant_candidateで相互接続のみ）**

| タイトル | 市川側ID | 浦安側ID |
|---|---|---|
| 蛇の目玉に手え突っこんだ | `chiba-ichikawa-hebi-no-medama-ni-tee-tsukkonda` | `chiba-urayasu-hebi-no-medama`（batch-001） |
| 白蛇の恩返し | `chiba-urayasu-hakushebi-no-ongaeshi` | `chiba-urayasu-white-snake-return`（batch-002） |
| 大正六年の大津波（津波から守った鎮守さま） | `chiba-urayasu-taisho6-otsunami-tsunami-chinju` | `chiba-urayasu-taisho6-tsunami-04`（batch-001、連作四番目） |

**索引段階の軽微な重複（浦安側の未着手スタブに参照ノートを追記のみ）**

- 古井戸の障り：`urayasu/2026-08-batch-008`のインデックス行（record_id無し）に、市川側`chiba-ichikawa-furui-do-no-sawari`への参照ノートを追加。

いずれもタイトルからの本文推測・内容の統合は行っていない。詳細はhatakiti.comとのチャット記録、および該当ファイルの`research_notes`/`related_records`を参照。

## 市川バッチ1・2の重複解消（2026-08-26追加実施）

上記の確認作業中に、`ichikawa/2026-08-batch-001-researching.json`（7件）と`ichikawa/2026-08-batch-002.json`（4件）が、いずれも既に`data/folktales/chiba/records/ichikawa/`へ**別のrecord_idで**canonical record化済みであるにもかかわらず、バッチ側に完全な重複recordとして残っていたことが判明した（バッチ5は既にdeduplication済みだったが、バッチ1・2は未処理だった）。

内容を突き合わせ、canonical record側がバッチ側の情報を全て含む（またはそれ以上に詳しい）ことを確認した上で、バッチ1・2側の全11件を`deduplicated_records`化し、`records`は空配列とした。バッチ側にしかない独自情報の消失はない。

| バッチ側タイトル | バッチ側ID | canonical ID |
|---|---|---|
| 印内の重右衛門伝承群（調査ハブ） | chiba-ichikawa-innai-juemon-group | chiba-ichikawa-innai-juemon-traditions |
| お稲荷さんと子どもたち | chiba-ichikawa-gyotoku-inari-children | chiba-ichikawa-oinari-san-to-kodomo-tachi |
| かまたきとキツネ | chiba-ichikawa-gyotoku-kamataiki-kitsune | chiba-ichikawa-kamataki-to-kitsune |
| 新場のキツネ | chiba-ichikawa-gyotoku-shinba-kitsune | chiba-ichikawa-shinba-no-kitsune |
| 拾ったきつねの子 | chiba-ichikawa-gyotoku-picked-fox-cub | chiba-ichikawa-hirota-kitsune-no-ko |
| おなつギツネとわたうり | chiba-ichikawa-gyotoku-onatsu-fox-watauri | chiba-ichikawa-onatsu-fox-watauri |
| おんぶされたムジナ | chiba-ichikawa-gyotoku-carried-mujina | chiba-ichikawa-onbu-sareta-mujina |
| お月さまがべーえ | chiba-ichikawa-otsukisama-ga-bee | chiba-ichikawa-otsukisama-ga-bee（同一） |
| サルとりょうし | chiba-ichikawa-saru-to-ryoshi | chiba-ichikawa-saru-to-ryoshi（同一） |
| 清水がにと、蛙の恩返し | chiba-ichikawa-shimizu-gani-to-kaeru | chiba-ichikawa-shimizu-gani-to-kaeru-no-ongaeshi |
| 女化のキツネ | chiba-ichikawa-onnabake-no-kitsune | chiba-ichikawa-nyoke-no-kitsune |

バッチ3（八幡不知森伝承群）・バッチ4（八幡拡張）は個別に確認し、canonical recordとの重複なし（record自体がそのままcanonical）。バッチ6は上記の通り解消済み。これでバッチ1〜6はすべて重複なしの状態になった。

## 現在の管理方針

### 1伝承1正本

同一伝承と判断できるものは、リポジトリ全体で1つのcanonical recordとして管理する。
後続バッチには重複した完全recordを残さず、必要に応じてdeduplicated_records等の履歴から既存のcanonical recordを参照できるようにする。

### 本文未確認時の扱い

本文を直接確認できない伝承は、題名から内容を補完しない。
確認できた以下の情報だけを記録する。

- 題名
- 出典資料
- 地域根拠・伝承地
- 索引上の分類
- 関連候補（同一話とは断定しない）

本文未確認のrecordは `research_status: partial` とし、`research_missing` に本文確認等の未処理事項を残す。

## バッチ6の処理状況

| 伝承 | 状態 |
|---|---|
| お大師様の米びつ | 題名・出典・相之川の舟渡場を確認。本文未確認。 |
| 古井戸の障り | 出典・地域根拠のみ保持。本文未確認。 |
| 蛇の目玉に手え突っこんだ | 出典・新井／相之川等の地域根拠のみ保持。本文未確認。 |
| 白蛇の恩返し | 『浦安の昔ばなし』収録を確認。類題「蛇の恩返し」とは本文照合前に同一視しない。 |
| 蛇の橋 | 複数資料への同題収録と地域根拠を確認。本文未確認。 |
| 蛇の泡とり | 複数資料への収録と当代島・堀江等の地域根拠を確認。本文未確認。 |
| 大正六年の大津波（津波から守った鎮守さま） | 出典・猫実／堀江／当代島の地域根拠のみ保持。本文未確認。 |

## 次回再開ポイント

**市川バッチ7**をGitHubから読み込み、未処理recordの先頭から同じ方針で深掘りを再開する。

## 注意事項

- 既調査recordを重複して再調査しない。
- 同一人物・同一存在・同一地点の伝承群は、本文確認前に安易に同一話へ統合しない。
- 関連性がある場合は、タグや関連リンクで接続する。
- 本文未確認の場合は「不明」を残し、AIが推測で物語を生成した内容を史料情報として保存しない。
