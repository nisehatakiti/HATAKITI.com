<?php
/**
 * Virtual page: 演劇用語集
 *
 * Wiki-style index for theatre terminology used throughout the textbook.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
get_header();

$terms = array(
    '演劇' => array('slug'=>'theatre','category'=>'基本','summary'=>'人が人物や出来事を表現し、それを他の人が受け取る表現。劇場の有無だけで演劇を決めることはできません。'),
    '俳優' => array('slug'=>'actor','category'=>'基本','summary'=>'舞台などで人物を演じ、身体や声を使って観客に表現を届ける人。'),
    '観客' => array('slug'=>'audience','category'=>'基本','summary'=>'演劇を見て、聞いて、受け取る人。演じる側との関係の中で舞台が成立します。'),
    '劇場' => array('slug'=>'theatre-building','category'=>'劇場・空間','summary'=>'演劇などを上演し、観客が見るために整えられた空間や建物。時代や地域によって形が異なります。'),
    '祭り' => array('slug'=>'festival','category'=>'古代・儀式','summary'=>'地域や社会で行われる行事。古代の演劇は祭りや社会的行事と深く結びついていました。'),
    '儀式' => array('slug'=>'ritual','category'=>'古代・儀式','summary'=>'宗教や社会において、一定の意味や手順をもって行われる行為。演劇と重なる表現を持つ場合があります。'),
    '悲劇' => array('slug'=>'tragedy','category'=>'ジャンル','summary'=>'古代ギリシャで発展し、人間の選択や苦しみ、運命などを扱った演劇のジャンル。'),
    '喜劇' => array('slug'=>'comedy','category'=>'ジャンル','summary'=>'笑いを通して人間や社会を描く演劇のジャンル。古代ギリシャでも発展しました。'),
    '合唱隊' => array('slug'=>'chorus','category'=>'古代・演技','summary'=>'古代ギリシャ演劇などで、歌や言葉、動きによって作品に参加する集団。'),
    '仮面' => array('slug'=>'mask','category'=>'古代・演技','summary'=>'顔を覆う道具。古代演劇などで人物の表現や視覚的な識別に用いられました。'),
    '演出' => array('slug'=>'direction','category'=>'近代・演出','summary'=>'作品を舞台上でどのように成立させるかを考え、俳優、空間、時間、音、光などをまとめる仕事や考え方。'),
    'リアリズム' => array('slug'=>'realism','category'=>'近代・演劇論','summary'=>'現実の人間や生活を舞台上でどう表現するかを重視する近代演劇の重要な考え方。'),
    '演じる' => array('slug'=>'acting','category'=>'基本','summary'=>'自分ではない人物や立場を、身体・声・行動などを使って表現すること。'),
    '役' => array('slug'=>'role','category'=>'基本','summary'=>'俳優が舞台上で演じる人物や立場。作品の中で固有の目的や関係を持つ。'),
    '役割' => array('slug'=>'role-function','category'=>'基本','summary'=>'ある人物や集団が作品の中で担っている働きや立場。'),
    '模倣' => array('slug'=>'imitation','category'=>'基本','summary'=>'人や物事の特徴をまねること。演技の基本的な出発点の一つ。'),
    '表現' => array('slug'=>'expression','category'=>'基本','summary'=>'考え、感情、人物、出来事などを、身体・声・言葉・空間などで外に表すこと。'),
    '再現' => array('slug'=>'reproduction','category'=>'基本','summary'=>'実際に起きたことや現実の状態を、舞台上で別の形として表すこと。'),
    '物語' => array('slug'=>'story','category'=>'基本','summary'=>'人物や出来事が時間の中で関係しながら進んでいくまとまり。'),
    '上演' => array('slug'=>'performance','category'=>'基本','summary'=>'演劇作品を俳優やスタッフが観客の前で実際に行うこと。'),
    '舞台' => array('slug'=>'stage','category'=>'劇場・空間','summary'=>'俳優が演技し、観客がその出来事を見るための上演の場。'),
    '客席' => array('slug'=>'audience-seats','category'=>'劇場・空間','summary'=>'観客が座り、舞台上の上演を見るための空間。'),
    '舞台空間' => array('slug'=>'stage-space','category'=>'劇場・空間','summary'=>'俳優、装置、照明、音などが配置され、演技が行われる空間。'),
    '上演空間' => array('slug'=>'performance-space','category'=>'劇場・空間','summary'=>'演劇が実際に行われる場所全体。劇場の舞台に限らない。'),
    '舞台と客席' => array('slug'=>'stage-and-audience','category'=>'劇場・空間','summary'=>'演じる場所と見る場所の関係。演劇の形式によってその境界は変化する。'),
    '野外劇' => array('slug'=>'open-air-theatre','category'=>'劇場・空間','summary'=>'劇場建物の外、広場や屋外施設などで行われる演劇。'),
    '広場劇' => array('slug'=>'square-theatre','category'=>'劇場・空間','summary'=>'広場など人々が集まる公共空間を使って行う演劇。'),
    '劇場外演劇' => array('slug'=>'off-site-theatre','category'=>'劇場・空間','summary'=>'通常の劇場ではなく、街、学校、建物、屋外などを上演場所として使う演劇。'),
    '観客参加' => array('slug'=>'audience-participation','category'=>'劇場・空間','summary'=>'観客が見るだけでなく、選択・発言・移動・行動などを通して上演に関わること。'),
    '参加型演劇' => array('slug'=>'participatory-theatre','category'=>'劇場・空間','summary'=>'観客の参加や交流を作品の重要な要素として組み込む演劇。'),
    'ディオニュソス祭' => array('slug'=>'dionysia','category'=>'古代演劇','summary'=>'古代アテナイでディオニュソス神をたたえる祭りとして行われた大規模な行事。'),
    '古代ギリシャ演劇' => array('slug'=>'ancient-greek-theatre','category'=>'古代演劇','summary'=>'古代ギリシャで発展した悲劇や喜劇などの演劇。'),
    '古代ローマ演劇' => array('slug'=>'ancient-roman-theatre','category'=>'古代演劇','summary'=>'古代ローマ社会で上演され、ギリシャ演劇の影響も受けながら発展した演劇。'),
    '常設劇場' => array('slug'=>'permanent-theatre','category'=>'古代演劇','summary'=>'一定の場所に継続して設けられた劇場。演劇を行う空間が固定されることで上演形式も発展した。'),
    'コロス' => array('slug'=>'chorus-greek','category'=>'古代演劇','summary'=>'古代ギリシャ演劇で歌や踊りなどを通して舞台に参加する合唱隊。'),
    '神話' => array('slug'=>'myth','category'=>'古代演劇','summary'=>'神々や英雄などに関する物語。古代演劇の重要な題材となった。'),
    '運命' => array('slug'=>'fate','category'=>'古代演劇','summary'=>'人間の力だけでは変えられないものとして物語の中で扱われる定め。悲劇の重要な主題の一つ。'),
    '英雄' => array('slug'=>'hero','category'=>'古代演劇','summary'=>'神話や物語などで特別な力や立場を持ち、重要な行動をする人物。'),
    '中世演劇' => array('slug'=>'medieval-theatre','category'=>'中世演劇','summary'=>'ヨーロッパ中世に、宗教や民衆の生活と結びつきながら発展した演劇。'),
    '宗教劇' => array('slug'=>'religious-drama','category'=>'中世演劇','summary'=>'キリスト教の物語や教えなどを題材として上演された演劇。'),
    '教会劇' => array('slug'=>'church-drama','category'=>'中世演劇','summary'=>'教会やその周辺で、宗教的な物語を表現するために行われた劇。'),
    '聖史劇' => array('slug'=>'mystery-play-bible','category'=>'中世演劇','summary'=>'聖書や聖人に関する物語を題材にした中世の宗教的な劇。'),
    '奇跡劇' => array('slug'=>'miracle-play','category'=>'中世演劇','summary'=>'聖人の奇跡などを題材にした中世ヨーロッパの宗教劇。'),
    '神秘劇' => array('slug'=>'mystery-play','category'=>'中世演劇','summary'=>'聖書の物語を題材にした中世ヨーロッパの宗教劇。'),
    '道徳劇' => array('slug'=>'morality-play','category'=>'中世演劇','summary'=>'善や悪、徳などを人物として登場させ、人間の生き方を描いた中世の劇。'),
    '世俗劇' => array('slug'=>'secular-drama','category'=>'中世演劇','summary'=>'宗教的な目的から離れ、日常生活や社会、笑いなどを題材にした劇。'),
    '民衆演劇' => array('slug'=>'popular-theatre','category'=>'中世演劇','summary'=>'特定の劇場だけでなく、人々の生活や地域社会の中で行われる演劇。'),
    '能' => array('slug'=>'noh','category'=>'日本演劇','summary'=>'日本の中世に成立し発展した伝統芸能。謡、舞、音楽、仮面などを組み合わせて演じる。'),
    '狂言' => array('slug'=>'kyogen','category'=>'日本演劇','summary'=>'能とともに発展した日本の伝統芸能。会話や動きを中心に人間や社会を滑稽に描く。'),
    'ルネサンス演劇' => array('slug'=>'renaissance-theatre','category'=>'近世演劇','summary'=>'ヨーロッパのルネサンス期に、古典文化の再発見などを背景に発展した演劇。'),
    '近代劇場' => array('slug'=>'modern-theatre','category'=>'近世演劇','summary'=>'近代以降に発展した、舞台と客席を明確に分けた劇場形式やその空間。'),
    '舞台装置' => array('slug'=>'stage-set','category'=>'近世演劇','summary'=>'舞台上に置き、場所や環境を示したり演技を支えたりする道具や構造物。'),
    '遠近法' => array('slug'=>'perspective','category'=>'近世演劇','summary'=>'奥行きや距離を平面上に表現する方法。舞台美術の見え方にも大きな影響を与えた。'),
    'プロセニアム' => array('slug'=>'proscenium','category'=>'近世演劇','summary'=>'舞台と客席の境界にある額縁状の構造。舞台を一枚の絵のように見せる形式と結びつく。'),
    'コメディア・デラルテ' => array('slug'=>'commedia-dellarte','category'=>'近世演劇','summary'=>'16世紀以降のイタリアで発展した、職業俳優による即興性の強い喜劇。'),
    '職業俳優' => array('slug'=>'professional-actor','category'=>'近世演劇','summary'=>'演技を専門の仕事として継続的に行う俳優。'),
    '劇場産業' => array('slug'=>'theatre-industry','category'=>'近世演劇','summary'=>'劇場、俳優、興行、観客などが結びつき、演劇を継続的に提供する仕組み。'),
    'グローブ座' => array('slug'=>'globe-theatre','category'=>'近世演劇','summary'=>'シェイクスピアの劇団などが活動したロンドンの劇場。'),
    '歌舞伎' => array('slug'=>'kabuki','category'=>'日本演劇','summary'=>'江戸時代に発展した日本の演劇。俳優の演技、音楽、舞台装置、衣裳などを組み合わせる。'),
    '近代演劇' => array('slug'=>'modern-drama','category'=>'近代演劇','summary'=>'19世紀以降、社会や人間を新しい視点から描きながら発展した演劇。'),
    '写実' => array('slug'=>'realistic-representation','category'=>'近代演劇','summary'=>'現実の人間や生活を、現実らしく見える形で舞台に表そうとする考え方。'),
    '自然主義' => array('slug'=>'naturalism','category'=>'近代演劇','summary'=>'人間を社会環境や生活条件などとの関係から捉え、現実を細かく描こうとする芸術上の考え方。'),
    '演出家' => array('slug'=>'director','category'=>'近代演劇','summary'=>'作品を舞台上でどのように成立させるかを考え、俳優や舞台要素を統合する役割を担う人。'),
    '舞台美術' => array('slug'=>'stage-design','category'=>'近代演劇','summary'=>'舞台上の空間や装置、背景などをデザインし、作品の世界を視覚的につくる仕事や分野。'),
    '新劇' => array('slug'=>'shingeki','category'=>'近代演劇','summary'=>'明治以降の日本で、西洋近代演劇の影響を受けながら成立・発展した演劇の流れ。'),
    '戯曲' => array('slug'=>'dramatic-text','category'=>'近代演劇','summary'=>'演劇として上演することを想定して書かれた作品や脚本。'),
    '解釈' => array('slug'=>'interpretation','category'=>'近代演劇','summary'=>'作品や人物に書かれている情報を読み取り、意味や関係を考えること。'),
    '演技システム' => array('slug'=>'acting-system','category'=>'近代演劇','summary'=>'俳優が役をつくり演じるための原理や訓練方法を体系化した考え方。'),
    '現代演劇' => array('slug'=>'contemporary-theatre','category'=>'現代演劇','summary'=>'20世紀以降に発展した多様な演劇。従来の劇場や物語の形式を広げる作品も含む。'),
    '異化' => array('slug'=>'verfremdung','category'=>'現代演劇','summary'=>'見慣れた出来事をあえて距離を置いて見せ、観客に考えさせるための表現方法。'),
    '身体の演劇' => array('slug'=>'theatre-of-the-body','category'=>'現代演劇','summary'=>'台詞や物語だけでなく、俳優の身体そのものを重要な表現手段として扱う演劇。'),
    '不条理演劇' => array('slug'=>'theatre-of-the-absurd','category'=>'現代演劇','summary'=>'世界や人間の存在の不条理さを、因果関係の崩れた状況や反復などを使って表現する演劇。'),
    '不条理' => array('slug'=>'absurdity','category'=>'現代演劇','summary'=>'人間の存在や世界が合理的な意味を持たないように感じられる状態や考え方。'),
    '実験演劇' => array('slug'=>'experimental-theatre','category'=>'現代演劇','summary'=>'既存の演劇形式にとらわれず、新しい表現方法や上演形式を試みる演劇。'),
    '非言語表現' => array('slug'=>'nonverbal-expression','category'=>'現代演劇','summary'=>'言葉を使わず、身体、表情、動き、空間などによって意味を伝える表現。'),
    '演劇の多様化' => array('slug'=>'diversification-of-theatre','category'=>'現代演劇','summary'=>'劇場、作品形式、俳優、観客との関係などが一つの型に限定されなくなっていくこと。'),
    '想像力' => array('slug'=>'imagination','category'=>'演技・想像','summary'=>'目の前にないものを思い浮かべ、それを具体的な感覚や身体につなげる力。'),
    'イメージ' => array('slug'=>'image','category'=>'演技・想像','summary'=>'頭の中に思い浮かべた人物、場所、物、状態などの像。'),
    '五感' => array('slug'=>'five-senses','category'=>'演技・想像','summary'=>'視覚、聴覚、嗅覚、味覚、触覚の五つの感覚。想像したものを具体化する手がかりになる。'),
    '視覚' => array('slug'=>'vision','category'=>'演技・感覚','summary'=>'見ることによって得られる感覚。場所や物、人の姿を捉える手がかりになる。'),
    '聴覚' => array('slug'=>'hearing','category'=>'演技・感覚','summary'=>'音を聞くことによって得られる感覚。声、環境音、距離などを感じ取る手がかりになる。'),
    '嗅覚' => array('slug'=>'smell','category'=>'演技・感覚','summary'=>'においを感じ取る感覚。記憶や場所のイメージとも結びつきやすい。'),
    '味覚' => array('slug'=>'taste','category'=>'演技・感覚','summary'=>'味を感じ取る感覚。食べ物や身体の経験を想像するときの手がかりになる。'),
    '触覚' => array('slug'=>'touch','category'=>'演技・感覚','summary'=>'触れたときの硬さ、温度、表面などを感じる感覚。身体と物との関係を捉える手がかりになる。'),
    '身体化' => array('slug'=>'embodiment','category'=>'演技・感覚','summary'=>'頭の中で考えたイメージや人物像を、実際の身体の姿勢や動きに変えること。'),
    '感覚' => array('slug'=>'sensation','category'=>'演技・感覚','summary'=>'身体や周囲で起きていることを受け取り、気づく働き。'),
    '身体感覚' => array('slug'=>'bodily-sensation','category'=>'演技・感覚','summary'=>'身体の内部や外部で起きている変化を感じ取る感覚。'),
    '反応' => array('slug'=>'reaction','category'=>'演技・感覚','summary'=>'相手や出来事、感覚などを受け取った結果として身体や声、行動に変化が起きること。'),
    '刺激' => array('slug'=>'stimulus','category'=>'演技・感覚','summary'=>'身体や心に変化を起こすきっかけとなる外部または内部からの働きかけ。'),
    '感情' => array('slug'=>'emotion','category'=>'演技・感情','summary'=>'状況や相手との関係などによって生まれ、身体や行動にも影響する心の動き。'),
    '感情表現' => array('slug'=>'emotional-expression','category'=>'演技・感情','summary'=>'感情を身体、表情、声、言葉、行動などを通して外に表すこと。'),
    '感情の変化' => array('slug'=>'emotional-change','category'=>'演技・感情','summary'=>'出来事や相手との関係によって、人物の感情が別の状態へ移っていくこと。'),
    '感情と行動' => array('slug'=>'emotion-and-action','category'=>'演技・感情','summary'=>'感じていることと、実際に人物が何をするかとの関係。'),
    '人物' => array('slug'=>'character','category'=>'演技・人物','summary'=>'演劇作品の中で、ある立場や生活を持って行動する存在。'),
    '人物像' => array('slug'=>'character-image','category'=>'演技・人物','summary'=>'台本に書かれた情報や想像から組み立てる、その人物についての具体的なイメージ。'),
    '人物造形' => array('slug'=>'characterization','category'=>'演技・人物','summary'=>'人物の過去、価値観、身体、行動などを考え、舞台上の人物として具体化すること。'),
    'キャラクター' => array('slug'=>'character','category'=>'演技・人物','summary'=>'作品の中で設定された人物。性格だけでなく、その人物の背景や関係も含めて捉えることができる。'),
    '人物設定' => array('slug'=>'character-setting','category'=>'演技・人物','summary'=>'人物の年齢、生活、過去、価値観、立場などを具体的に考えた設定。'),
    '価値観' => array('slug'=>'values','category'=>'演技・人物','summary'=>'何を大切だと考え、何を選ぶかを左右する、その人物の考え方や基準。'),
    '人物の過去' => array('slug'=>'character-past','category'=>'演技・人物','summary'=>'舞台上の現在に至るまでに人物が経験してきた出来事や生活。'),
    '性格' => array('slug'=>'personality','category'=>'演技・人物','summary'=>'人物の考え方や感じ方、行動の傾向。'),
    '長所' => array('slug'=>'strength','category'=>'演技・人物','summary'=>'人物の中で強みとして働く性質や能力。'),
    '短所' => array('slug'=>'weakness','category'=>'演技・人物','summary'=>'人物の弱点や苦手な部分。行動や人間関係に影響することがある。'),
    '状況' => array('slug'=>'situation','category'=>'演技・状況','summary'=>'人物が置かれている時間、場所、相手、出来事、周囲の環境などの条件。'),
    '舞台状況' => array('slug'=>'stage-situation','category'=>'演技・状況','summary'=>'舞台上で人物が置かれている具体的な時間、場所、人間関係、出来事などの条件。'),
    '時間' => array('slug'=>'time','category'=>'演技・状況','summary'=>'場面がいつ起きているのかという条件。時刻、季節、時代などを含む。'),
    '場所' => array('slug'=>'place','category'=>'演技・状況','summary'=>'人物がどこにいるのかという条件。場所によって行動や関係も変化する。'),
    '出来事' => array('slug'=>'event','category'=>'演技・状況','summary'=>'人物の行動や感情、関係に変化を起こす、場面で起きたこと。'),
    '周囲の環境' => array('slug'=>'environment','category'=>'演技・状況','summary'=>'人物の周りにある物、音、温度、明るさ、人など、その場を構成する条件。'),
    '前史' => array('slug'=>'backstory','category'=>'演技・状況','summary'=>'現在の場面が始まる前に起きた出来事や人物の経験。'),
    '目的' => array('slug'=>'objective','category'=>'演技・目的','summary'=>'人物がその場面で何をしようとしているのか、何を達成しようとしているのか。'),
    '動機' => array('slug'=>'motivation','category'=>'演技・目的','summary'=>'人物がある行動を起こす理由やきっかけ。'),
    '意図' => array('slug'=>'intention','category'=>'演技・目的','summary'=>'人物が行動や言葉を通して何をしようとしているのかという狙い。'),
    '目標' => array('slug'=>'goal','category'=>'演技・目的','summary'=>'人物が到達したい具体的な状態や得たい結果。'),
    '行動' => array('slug'=>'action','category'=>'演技・目的','summary'=>'人物が目的や欲求に向かって実際に行うこと。'),
    '障害' => array('slug'=>'obstacle','category'=>'演技・目的','summary'=>'人物が目的や欲求を達成することを妨げるもの。'),
    '目的と感情' => array('slug'=>'objective-and-emotion','category'=>'演技・目的','summary'=>'人物が何をしたいのかという目的と、何を感じているのかという感情を分けて考えること。'),
    '欲求' => array('slug'=>'desire','category'=>'演技・欲求','summary'=>'人物の内側にある、何かを求めたり得たりしたいという強い動き。'),
    '内的欲求' => array('slug'=>'inner-desire','category'=>'演技・欲求','summary'=>'人物の内側から生まれ、行動を動かす欲求。'),
    '欲望' => array('slug'=>'desire-want','category'=>'演技・欲求','summary'=>'何かを強く求める気持ち。演技では人物の行動を動かす要素として扱える。'),
    '恐れ' => array('slug'=>'fear','category'=>'演技・欲求','summary'=>'人物が失いたくないものや避けたいものに対して抱く感情。'),
    '葛藤' => array('slug'=>'conflict','category'=>'演技・欲求','summary'=>'人物の中で異なる欲求や考えがぶつかり、簡単に決められない状態。'),
    '欲求の衝突' => array('slug'=>'conflicting-desires','category'=>'演技・欲求','summary'=>'一人の人物の中、または複数の人物の間で異なる欲求がぶつかること。'),
    '関係性' => array('slug'=>'relationship','category'=>'演技・関係','summary'=>'人物同士がどのような立場や距離、感情、力関係にあるのかというつながり。'),
    '相手役' => array('slug'=>'scene-partner','category'=>'演技・関係','summary'=>'ある人物と場面を共有し、相互に働きかけながら演じる相手の役。'),
    '相互作用' => array('slug'=>'interaction','category'=>'演技・関係','summary'=>'一方の人物の行動や反応が相手に影響し、その相手の変化がさらに自分へ返ってくる関係。'),
    '交流' => array('slug'=>'interaction-exchange','category'=>'演技・関係','summary'=>'人物同士が言葉や行動、視線などを通して互いに働きかけること。'),
    '対話' => array('slug'=>'dialogue','category'=>'演技・関係','summary'=>'二人以上の人物が言葉を交わしながら場面を進めること。'),
    '距離' => array('slug'=>'distance','category'=>'演技・関係','summary'=>'人物同士が身体的・心理的にどれくらい離れているかという関係の要素。'),
    '関係の変化' => array('slug'=>'relationship-change','category'=>'演技・関係','summary'=>'出来事や行動によって人物同士の立場や感情、距離などが変わること。'),
    '対立' => array('slug'=>'opposition','category'=>'演技・関係','summary'=>'人物同士の目的、欲求、価値観などがぶつかっている状態。'),
    '場面づくり' => array('slug'=>'scene-making','category'=>'演技・関係','summary'=>'相手とのやり取りや行動を積み重ねて、その場面そのものを成立させていくこと。'),

);

$groups = array();
foreach ( $terms as $name => $term ) {
    $groups[ $term['category'] ][ $name ] = $term;
}
ksort( $groups );
?>
<main class="hk-container hk-glossary">
    <header class="hk-textbook-hero">
        <p class="hk-textbook-kicker">HATAKITI 演劇の教科書｜Wiki</p>
        <h1>演劇用語集</h1>
        <p>演劇の歴史、演技、舞台スタッフなどで出てくる言葉を、必要なときに調べられるように整理していきます。</p>
    </header>

    <nav class="hk-chapter-nav" aria-label="演劇用語集ナビゲーション">
        <a href="<?php echo esc_url( home_url( '/theatre-textbook/history/chapter-1/' ) ); ?>">← 演劇の歴史</a>
        <a href="<?php echo esc_url( home_url( '/theatre-textbook/' ) ); ?>">演劇の教科書</a>
        <span></span>
    </nav>

    <section class="hk-section">
        <div class="hk-section-head">
            <h2>演劇用語を探す</h2>
            <p>用語をクリックすると、その言葉の詳しいページへ移動します。今後、演劇の歴史だけでなく演技・演出・舞台技術の用語も追加していきます。</p>
        </div>
        <label class="hk-glossary-search">
            <span>用語を検索</span>
            <input type="search" id="hk-glossary-search" placeholder="例：劇場、悲劇、リアリズム">
        </label>
    </section>

    <section class="hk-section" id="glossary-list">
        <?php foreach ( $groups as $category => $items ) : ?>
            <div class="hk-glossary-group">
                <h2><?php echo esc_html( $category ); ?></h2>
                <div class="hk-glossary-grid">
                    <?php foreach ( $items as $name => $term ) : ?>
                        <a class="hk-glossary-card" data-term="<?php echo esc_attr( $name . ' ' . $term['summary'] . ' ' . $category ); ?>" href="<?php echo esc_url( home_url( '/theatre-textbook/glossary/' . $term['slug'] . '/' ) ); ?>">
                            <span><?php echo esc_html( $category ); ?></span>
                            <h3><?php echo esc_html( $name ); ?></h3>
                            <p><?php echo esc_html( $term['summary'] ); ?></p>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
        <p id="hk-glossary-empty" hidden>該当する用語はありません。</p>
    </section>
</main>
<style>
.hk-glossary .hk-textbook-hero{max-width:760px;margin:56px auto 48px;text-align:center}
.hk-glossary .hk-textbook-hero h1{font-family:var(--hk-font-serif);font-size:36px}
.hk-glossary .hk-textbook-hero>p:last-child{color:var(--hk-fg-dim);line-height:2}
.hk-glossary .hk-chapter-nav{max-width:900px;margin:0 auto 48px}
.hk-glossary-search{display:block;max-width:700px;margin:0 auto}
.hk-glossary-search span{display:block;font-size:13px;color:var(--hk-fg-dim);margin-bottom:8px}
.hk-glossary-search input{width:100%;box-sizing:border-box;padding:14px 16px;border:1px solid var(--hk-border);border-radius:8px;background:var(--hk-bg-elevated);color:var(--hk-fg);font:inherit}
.hk-glossary-group{margin:0 0 48px}
.hk-glossary-group>h2{font-size:22px;border-bottom:1px solid var(--hk-border);padding-bottom:12px;margin-bottom:18px}
.hk-glossary-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px}
.hk-glossary-card{display:block;padding:22px;border:1px solid var(--hk-border);background:var(--hk-bg-elevated);color:var(--hk-fg);transition:border-color .2s}
.hk-glossary-card:hover{border-color:var(--hk-accent-warm);text-decoration:none}
.hk-glossary-card>span{font-size:11px;color:var(--hk-accent-warm);letter-spacing:.08em}
.hk-glossary-card h3{font-family:var(--hk-font-serif);font-size:22px;margin:8px 0 10px}
.hk-glossary-card p{margin:0;color:var(--hk-fg-dim);font-size:14px;line-height:1.8}
@media(max-width:900px){.hk-glossary-grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:600px){.hk-glossary-grid{grid-template-columns:1fr}.hk-glossary .hk-textbook-hero h1{font-size:29px}}
</style>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('hk-glossary-search');
    const cards = Array.from(document.querySelectorAll('.hk-glossary-card'));
    const empty = document.getElementById('hk-glossary-empty');
    if (!input) return;
    input.addEventListener('input', function () {
        const q = input.value.trim().toLowerCase();
        let visible = 0;
        cards.forEach(function (card) {
            const match = !q || card.dataset.term.toLowerCase().includes(q);
            card.hidden = !match;
            if (match) visible++;
        });
        empty.hidden = visible !== 0;
    });
});
</script>
<?php get_footer(); ?>