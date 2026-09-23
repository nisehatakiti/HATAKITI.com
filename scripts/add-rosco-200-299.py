import json
from pathlib import Path

p = Path("wp-content/themes/hatakiti/assets/data/gel-colors.json")
source_url = "https://us.rosco.com/en/lee-rosco-equivalents"
source_pdf = "https://emea.rosco.com/sites/default/files/content/resource/2016-10/RoscoUKFilmmakingNo3.pdf"
accessed = "2026-09-24"

items = [
("200","Double C.T. Blue",["色温度変換"]),
("201","Full C.T. Blue",["色温度変換"]),
("202","1/2 C.T. Blue",["色温度変換"]),
("203","1/4 C.T. Blue",["色温度変換"]),
("204","Full C.T. Orange",["色温度変換"]),
("205","1/2 C.T. Orange",["色温度変換"]),
("206","1/4 C.T. Orange",["色温度変換"]),
("207","CTO +.3 Neutral Density",["色温度変換","ND"]),
("208","CTO +.6 Neutral Density",["色温度変換","ND"]),
("209",".3 Neutral Density",["ND"]),
("210",".6 Neutral Density",["ND"]),
("211",".9 Neutral Density",["ND"]),
("212","L.C.T. Yellow",["色温度変換"]),
("213","White Flame Green",["補正"]),
("214","Full Tough Spun",["ディフュージョン"]),
("215","1/2 Tough Spun",["ディフュージョン"]),
("216","White Diffusion",["ディフュージョン"]),
("217","Blue Diffusion",["ディフュージョン"]),
("218","1/8 C.T. Blue",["色温度変換"]),
("219","Fluorescent Green",["蛍光灯補正"]),
("220","White Frost",["ディフュージョン","フロスト"]),
("221","Blue Frost",["ディフュージョン","フロスト"]),
("223","1/8 C.T.O.",["色温度変換"]),
("224","Daylight Blue Frost",["ディフュージョン","フロスト"]),
("225","Lee N.D. Frost",["ND","ディフュージョン","フロスト"]),
("226","Lee U.V.",["補正"]),
("228","Brushed Silk",["ディフュージョン"]),
("229","1/4 Tough Spun",["ディフュージョン"]),
("241","Lee Fluor 5700K",["蛍光灯補正"]),
("242","Lee Fluor 4300K",["蛍光灯補正"]),
("243","Lee Fluor 3600K",["蛍光灯補正"]),
("244","Lee Plus Green",["補正"]),
("245","1/2 Plus Green",["補正"]),
("246","1/4 Plus Green",["補正"]),
("247","Lee Minus Green",["補正"]),
("248","1/2 Minus Green",["補正"]),
("249","1/4 Minus Green",["補正"]),
("250","1/2 White Diffusion",["ディフュージョン"]),
("251","1/4 White Diffusion",["ディフュージョン"]),
("252","1/8 White Diffusion",["ディフュージョン"]),
("253","Hampshire Frost",["ディフュージョン","フロスト"]),
("255","Hollywood Frost",["ディフュージョン","フロスト"]),
("256","1/2 Hampshire Frost",["ディフュージョン","フロスト"]),
("257","1/4 Hampshire Frost",["ディフュージョン","フロスト"]),
("258","1/8 Hampshire Frost",["ディフュージョン","フロスト"]),
("269","Heat Shield",["反射・遮光"]),
("270","Scrim",["反射・遮光"]),
("271","Mirror Silver",["反射・遮光"]),
("272","Soft Gold Reflector",["反射・遮光"]),
("273","Soft Silver Reflector",["反射・遮光"]),
("274","Mirror Gold",["反射・遮光"]),
("275","Black Scrim",["反射・遮光"]),
("278","Eighth Plus Green",["補正","蛍光灯補正"]),
("279","Eighth Minus Green",["補正","蛍光灯補正"]),
("281","3/4 C.T. Blue",["色温度変換"]),
("285","3/4 C.T. Orange",["色温度変換"]),
("298",".15 Neutral Density",["ND"]),
("299","1.2 Neutral Density",["ND"]),
]

data = json.loads(p.read_text(encoding="utf-8"))
records = data.setdefault("records", [])
existing = {(str(r.get("manufacturer","")).strip(), str(r.get("series","")).strip(), str(r.get("color_number","")).strip()) for r in records}
added = 0

for no, name, uses in items:
    key = ("Rosco", "E-Colour+", no)
    if key in existing:
        continue
    official = f"{source_url}#{no}"
    record = {
        "id": f"rosco-e-colour-{no}",
        "product": {
            "manufacturer": "Rosco",
            "series": "E-Colour+",
            "color_number": no,
            "color_name": name
        },
        "manufacturer_data": {
            "official_product_url": source_url,
            "transmission": {"measurements": []},
            "source": {
                "url": source_url,
                "title": "Rosco — Lee to Rosco Equivalents",
                "accessed_at": accessed
            },
            "source_type": "manufacturer_official"
        },
        "reference_color": {
            "rgb": None,
            "hex": None,
            "source": {"type": "unknown", "url": "", "title": "", "accessed_at": ""},
            "method": "",
            "confidence": "unverified"
        },
        "hatakiti": {
            "uses": uses,
            "tags": [],
            "notes": "製品名・シリーズはRosco公式資料に基づく。用途はHATAKITI編集分類。"
        },
        "verification": {
            "status": "verified",
            "last_verified_at": accessed,
            "verified_fields": [
                "product",
                "manufacturer_data.official_product_url",
                "manufacturer_data.source",
                "hatakiti.uses"
            ],
            "notes": "Rosco公式のLee to Rosco EquivalentsおよびE-Colour+資料で製品番号・名称を確認。透過率は未登録。"
        },
        "manufacturer": "Rosco",
        "series": "E-Colour+",
        "color_number": no,
        "color_name": name,
        "transmission": None,
        "transmission_display": "",
        "reference_rgb": None,
        "reference_hex": None,
        "reference_color_source": "未登録",
        "reference_color_method": "",
        "uses": uses,
        "notes": "Rosco公式資料に基づくE-Colour+フィルター。",
        "detail_url": official
    }
    records.append(record)
    added += 1

p.write_text(json.dumps(data, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")
print(f"added={added}")
