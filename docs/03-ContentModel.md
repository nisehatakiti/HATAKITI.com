# HATAKITI.com Blueprint

# 03 - Content Model

Version : 2.0

---

## 1. Content model

HATAKITI.com uses a simple content structure.

- DailyThought / BlogPost
- TheatreViewingRecord
- FilmViewingRecord
- Theatre-related thoughts as ordinary posts with tags

The site should favor simple creation and retrieval over unnecessary normalization or a rigid knowledge structure.

### Content ownership

- 日々の所感：HATAKITIが更新
- 観劇記録：HATAKITIが更新
- 映画記録：HATAKITIが更新
- 演劇論：HATAKITIが原案・思想を提供し、ChatGPTが文章化・構成を担当。公開記事には「文責：チャッピー」を付ける。

---

## 2. DailyThought / BlogPost

Free-form post.

Fields:

- Title
- Published date/time
- Body
- Categories
- Tags
- Featured image (optional)

ChatGPT may assist when explicitly requested, but HATAKITI is the content owner.

---

## 3. TheatreViewingRecord

Simple structured viewing record.

Fields:

| Field | Type | Notes |
|---|---|---|
| Theatre Group | text | Group name |
| Production Title | text | Production title |
| Viewing Date | date | Actual viewing date |
| Viewing Method | enum | Theatre / Streaming / Recording / Other |
| Production Start Date | date | Run start, when known |
| Production End Date | date | Run end, when known |
| Venue | text | Venue / location |
| Review / Thoughts | rich text | Free-form response |
| Tags | taxonomy | Optional |

Viewing Date and Production Date Range must remain separate.

---

## 4. FilmViewingRecord

Fields:

| Field | Type | Notes |
|---|---|---|
| Viewing Date | date | Actual viewing date |
| Title | text | Film title |
| Director | text | Director |
| Screenwriter | text | When known |
| Release Year | year | Original release year |
| Categories | multi-select taxonomy | Multiple values allowed |
| Cast | text | Main cast, when useful |
| Viewing Method | enum | Theatre / Streaming / Recording / Other |
| Review / Thoughts | rich text | Free-form response |
| Tags | taxonomy | Optional |

Possible categories include Animation, Horror, Romantic comedy, Drama, Comedy, Action, Thriller, Suspense, Documentary, Science fiction. The list can grow naturally.

---

# 5. Theatre-related thoughts

Theatre writing should normally be free-form posts rather than a rigid curriculum. Tags organize the material after writing.

Possible tags:

- 演技
- セリフ
- 身体
- 感情
- 演出
- 台本
- 稽古

The content should remain personal, practical, and concise rather than pretending to be universal academic theory.

---

## 5.1 演劇公演とは誰のものか

Core principle:

- A theatre performance is the director's work.
- The director gives the performance its overall artistic direction.
- Actors are components used to express and realize the work.
- An actor's job is to prepare sufficiently for the expression the work requires, not to make themselves stand out for its own sake.
- Making an actor shine is fundamentally part of the directing side's work.
- However, a director cannot know 100% of an actor's possibilities in advance.
- Therefore actors should actively show their possibilities during rehearsal.
- Rehearsal is where actors offer possibilities; the director decides what belongs in the final work.
- In performance, the actor serves the work.

The article is HATAKITI's personal theatre viewpoint, not an objective universal theory.

---

## 5.2 演技をしろ＞するな＞しろ

The established wording "しろ＞するな＞しろ" is intentionally retained.

1. **演技をしろ** — beginners often respond with conspicuous theatrical speech and exaggerated acting.
2. **演技をするな** — when this is far from natural dramatic expression, the actor is told to stop "acting" and simply be the character.
3. **演技をしろ** — once the actor can genuinely exist as the character, they then act the character's behavior and intentions.

The point is to move from "performing acting" to being the character, and then to purposeful action as that character.

---

## 5.3 台本の読み方①「セリフから感情を作らない」

Dialogue in a script is the final output produced by the character's emotion and situation. The actor should not simply add a matching emotion to the written line.

Natural direction:

```text
状況・出来事
   ↓
人物の感情
   ↓
人物の欲求・行動
   ↓
セリフ
```

A common initial approach is the reverse: angry line → angry voice, sad line → sad voice, happy line → happy voice. The intended approach is to create the emotional and situational conditions from which the line naturally emerges.

The key question is:

> 「なぜこの人物は、このセリフを言わなければならなくなったのか？」

The actor examines what happened, what the character feels, what the character wants, and why this line is necessary now.

Core statement:

> **セリフは感情から出てくる。感情をセリフに乗せるのではない。**

---

## 5.4 身体表現①「動くより、止める」

Body expression in acting and dance should consciously focus on stopping as well as movement.

Continuous movement tends to flow past the audience's perception. A clear stop creates a moment that can be recognized and remembered.

Dance can gain sharpness through:

```text
動く → 止める → 動く → 止める
```

In acting, move sufficiently when movement is required, but deliberately stop when an important moment needs to reach the audience.

A full-body stop also creates a foundation for small expression:

```text
全身を止める
   ↓
首だけ動かす
   ↓
視線を変える
   ↓
表情を変える
```

Therefore body-expression skill is not only large movement. It is the ability to switch cleanly between large movement, complete stillness, and small movement.

Core statements:

> **動くことを考える前に、止まることを考える。**

> **全身を止められるからこそ、身体の一部分を動かして表現できる。**

---

## 5.5 感情のベクトル

When two or more people are on stage, feelings toward other people can be organized as physical vectors.

Two useful factors are:

- strength of interest / attention toward the other person
- positive or negative direction of the emotion

These affect distance, body direction, movement, and gaze.

Examples:

```text
強い好意
   ↓
相手に近づきたい
   ↓
相手へ向かう身体のベクトル

強い嫌悪
   ↓
相手から離れたい
   ↓
相手から離れる身体のベクトル
```

Complex feelings can create different vectors in different parts of the body.

For example, wanting to talk while feeling guilty:

```text
身体 → 相手へ
視線 → 相手から
```

Liking someone but being too embarrassed to approach:

```text
身体 → 相手から離れる
視線 → 相手へ
```

The more detailed the emotional conflict, the more detailed the physical expression can become.

The practical question is:

> 「この感情なら、身体はどちらへ向かい、視線はどちらへ向かい、距離はどう変化するのか？」

Emotional vectors are a practical acting tool, not a scientific formula. They work especially well with stillness because a stable body allows a vector to be expressed through eyes, head, shoulders, hands, or other small parts.

---

## 5.6 表現論①「どう見られたいか」より「どう見えるか」

An actor's intention about how they want a character to appear is not the same as what the audience actually sees.

Creating an internal character history, daily life, values, and relationships can be useful for deepening a role. However, the audience cannot see the actor's private settings. They see voice, body, movement, gaze, facial expression, timing, distance, and other observable elements.

Therefore the actor must ask not only:

> 「自分はこの役をどう見せたいのか？」

but:

> **「実際に客席からはどう見えているのか？」**

There is an important distinction between deepening a role through imagination and inventing a private role that contradicts the script.

Imagining an unstated past can be useful when it helps explain the character while remaining consistent with the given role. If an actor creates a private setting that substantially conflicts with the character and uses it to justify the performance, they may be creating a different character rather than deepening the given one.

Core statement:

> **役を深めるために設定を作るのであって、設定を作るために役を変えてはいけない。**

Useful sequence:

```text
役を理解する
   ↓
内面を作り込む
   ↓
身体・声・行動に変換する
   ↓
客席からどう見えるか確認する
   ↓
必要に応じて修正する
```

The final test is the audience's perception, not the actor's private intention.

---

## 5.7 身体表現②「ないものを身体で見せる」

An actor's body can make the audience perceive the weight, size, movement, distance, speed, and presence of things that are not literally represented on stage.

### Weight

A physically light box can be presented as heavy by tracing the bodily actions of genuinely lifting a heavy object:

- lower the hips
- ground through the legs
- prepare the back and arms
- apply visible effort when lifting
- let the body's movement reflect the imagined load

The actor is using knowledge of the body's response to weight to give the object believable physical presence.

### Size

A giant unseen monster can be made perceptible by raising the gaze high above normal eye level. If it moves, the actor can follow its movement with the eyes, allowing the audience to construct its path.

The principle is:

> **舞台上に存在しないものを、自分の身体によって観客の中に存在させる。**

The actor should ask:

> 「本当に重いものなら、自分の身体はどう動くのか？」

> 「本当に巨大なものを見たら、視線や身体はどう反応するのか？」

> 「速いものが移動したら、自分の目はどう追うのか？」

The actor's body is their most immediate expressive tool. One part of the actor's work is therefore to explore how much can be communicated using that body alone.

Core statement:

> **自分自身が持っている身体という武器で、何を表現できるのかを突き詰める。**

This principle connects naturally with movement / stillness and emotional vectors.

---

# 6. Authorship / responsibility

The theatre essays are based on HATAKITI's own ideas and rough spoken / written concepts. HATAKITI provides the original thought and viewpoint; ChatGPT organizes and turns those ideas into readable articles.

Every published theatre essay must include:

> 文責：チャッピー

This is a writing / editorial responsibility marker. It does not mean ChatGPT is the source of HATAKITI's theatre philosophy.

ChatGPT must not invent new ideology and present it as HATAKITI's own. If an interpretation materially changes the original idea, HATAKITI should confirm it before publication.

---

# 7. Site-wide footer / co-creation statement

All pages should contain this unobtrusive footer statement:

> このページは、友達の少ないHATAKITIが、チャッピー（ChatGPT）とともに作成しています。

This is intentionally humorous and should not be presented as a major promotional element.

---

# 8. ChatGPT → WordPress draft integration

HATAKITI.com should support a future workflow in which ChatGPT can create WordPress content as a draft when explicitly instructed by HATAKITI.

```text
HATAKITI
   ↓
ChatGPT
   ↓
ChatGPT Action / authenticated API connection
   ↓
HATAKITI-specific WordPress API endpoint
   ↓
WordPress REST API
   ↓
WordPress draft
   ↓
HATAKITI reviews
   ↓
HATAKITI publishes manually
```

### Authentication / authorization

- Create a dedicated WordPress integration user such as `chatgpt-editor`.
- Grant only minimum capabilities for creating / editing drafts.
- Use WordPress Application Passwords.
- HTTPS only.
- No user, plugin, theme, or site-setting administration.
- No automatic publishing in the initial implementation.

### HATAKITI-specific API layer

Prefer a small custom WordPress plugin rather than exposing the entire WordPress REST API to ChatGPT.

Example endpoint:

```text
/wp-json/hatakiti/v1/draft
```

The endpoint should accept only required fields such as:

- content type
- title
- body
- tags
- categories
- optional featured image information

The plugin validates the request, applies content-specific defaults, and creates the WordPress draft.

For theatre essays it should ensure the required `文責：チャッピー` credit.

The initial integration is draft-only. HATAKITI remains responsible for final publication.

---

# 9. Relationship principle

```text
HATAKITI
  ├─ 日々の所感（HATAKITIが更新）
  ├─ 演劇についての文章（HATAKITI原案＋チャッピー文章化／文責：チャッピー）
  ├─ 観劇記録（HATAKITIが更新）
  ├─ 映画記録（HATAKITIが更新）
  └─ 活動・制作
```

Relationships are optional. The site should prioritize easy creation, easy reading, and easy future retrieval.

---

# 10. WordPress implementation direction

Expected implementation:

- Normal WordPress Posts for DailyThought / BlogPost
- Normal WordPress Posts with tags for theatre-related thoughts
- Custom Post Types for TheatreViewingRecord and FilmViewingRecord where this improves input / archive UX
- Custom Fields for structured viewing data
- Categories / tags for lightweight organization
- Custom archive / search templates where useful
- Site-wide footer with the co-creation statement
- Theatre essay template with `文責：チャッピー`
- Dedicated least-privilege integration user
- Application Password
- HATAKITI-specific REST endpoint
- ChatGPT Action / authenticated API connection
- Draft-only initial permission

The exact plugins, API implementation, OpenAPI schema, and WordPress configuration should be finalized during implementation after reviewing the actual WordPress environment.

The guiding principle is the simplest WordPress structure that provides the required user experience while keeping the ChatGPT integration narrow, auditable, and safe.
