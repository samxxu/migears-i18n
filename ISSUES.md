# migears-i18n — Known Issues

> Summary of this module's issues. The items themselves are in [`issues/`](issues/README.md), one file
> per item: a front-matter header and a thread. This file is generated from them and can be rewritten at
> any time; edit an item, never this file.
>
> From the miGears Full-Module Code Review Report (6th round, 2026-10-01).

| | |
|---|---|
| Status | **Best state** |
| Size | src 496 lines (net) · 119 tests (1 skipped) · 6 src files |

Legend — **P0** functional or security · **P1** documentation that fails when copied · **P2** robustness · **P3** metadata and docs

## At a glance

| | |
|---|---|
| Unsettled | P0 0 · P1 0 · P2 0 · P3 2 · other 1 |
| Settled | 2 of 5 |
| Waiting on the owner | _nothing_ |
| Waiting on the coordinator | `P3-3` |
| Waiting on the reviewer | `P3-2`, `G4` |
| Deferred, owing nobody | _nothing_ |

| id | level | status | title |
|---|---|---|---|
| [`P3-1`](issues/P3-1.md) | P3 | **verified** | Interpolation casts every value with `(string)`, so an array parameter … |
| [`P3-2`](issues/P3-2.md) | P3 | **rejected** | `restoreEnv()` only restores `LANG` and `LC_ALL`; gettext also reads … |
| [`P3-3`](issues/P3-3.md) | P3 | **question** | TranslatorFactory has no schema validation for the driver config array … |
| [`G3`](issues/G3.md) | - | **verified** | Skip guard: the gettext tests skip when the extension is missing … |
| [`G4`](issues/G4.md) | - | **rejected** | Document standard: the project standard is that every document is … |

## Unclosed

What is left to do here: every item whose `status` is not `verified` or `closed`,
highest severity first. `waiting on` is the party who acts next, read from that status.

| | |
|---|---|
| Unclosed | **3** of 5 |
| By status | `question` 1 · `rejected` 2 |
| Waiting on | coordinator 1 · reviewer 2 |

| level | item | status | waiting on | title |
|---|---|---|---|---|
| **P3** | [`P3-2`](issues/P3-2.md) | `rejected` | reviewer | `restoreEnv()` only restores `LANG` and `LC_ALL`; gettext also reads … |
| **P3** | [`P3-3`](issues/P3-3.md) | `question` | coordinator | TranslatorFactory has no schema validation for the driver config array … |
| **-** | [`G4`](issues/G4.md) | `rejected` | reviewer | Document standard: the project standard is that every document is … |

## Verdict

The factory’s first half is genuinely fixed; what remains open is a legitimate public-behaviour question, not a defect.

## Fixed since the last round

No item was awaiting a verdict. The factory’s type guards and the three interpolation guards are consistent with the record, and the rejected CHANGELOG-scope item still stands.

## Test gaps

gettext cases skip when the extension is absent; the two drivers’ "missing translation falls back to the key" behaviour is consistent but has no cross-driver test; ArrayTranslator::fromFile() require-s the configured PHP file, covered only by the bundled fixture.

## Verification protocol

- `./vendor/bin/phpunit` · `composer analyse` · `composer validate`
- Warning/notice/deprecation/risky flags in `phpunit.xml.dist`: all four on
- A PHP warning counts as a test failure only where those flags are on; otherwise run `./vendor/bin/phpunit --fail-on-warning` explicitly.


---

# migears-i18n — 已知问题

> 本模块问题的概览。条目本体在 [`issues/`](issues/README.md)，一条目一文件：前置字段加讨论串。
> 本文件由条目生成，随时可以整段重写；请改条目，不要改本文件。
>
> 出自 miGears 全模块代码评审报告（6th round，2026-10-01）。

| | |
|---|---|
| 状态 | **状态最好** |
| 体量 | src 496 行（净）· 119 个用例（1 跳过）· 6 个源文件 |

级别说明 — **P0** 功能性或安全级 · **P1** 文档照抄即错 · **P2** 健壮性 · **P3** 元数据与文档

## 状态一览

| | |
|---|---|
| 未了结 | P0 0 · P1 0 · P2 0 · P3 2 · 其他 1 |
| 已了结 | 2 / 5 |
| 等模块主 | _无_ |
| 等协调人 | `P3-3` |
| 等评审方 | `P3-2`, `G4` |
| 已暂缓，不欠谁 | _无_ |

| id | 级别 | 状态 | 标题 |
|---|---|---|---|
| [`P3-1`](issues/P3-1.md) | P3 | **verified** | 插值对所有值做 (string) 强转，因此数组参数会产生 PHP「Array to string conversion」警告并输出 … |
| [`P3-2`](issues/P3-2.md) | P3 | **rejected** | restoreEnv() 只还原 LANG 与 LC_ALL；gettext 还会读 … |
| [`P3-3`](issues/P3-3.md) | P3 | **question** | TranslatorFactory 对驱动配置数组没有 schema 校验——拼写错误的键或错误的类型会被静默忽略，或在下游产生令人困惑的错误。 |
| [`G3`](issues/G3.md) | - | **verified** | 跳过守卫：缺少 gettext 扩展时 gettext … |
| [`G4`](issues/G4.md) | - | **rejected** | 文档标准：项目标准是每一份文档都上英下汉——英文块在前，完全相同的中文块在后。本模块的 `CHANGELOG.md` 为纯英文。 … |

## 未关闭

本模块还剩什么要做：所有 `status` 不是 `verified` 或 `closed` 的条目，按严重度从高到低。
`waiting on` 是下一步该动手的一方，由其状态读出。

| | |
|---|---|
| 未关闭 | **3** / 5 |
| 按状态 | `question` 1 · `rejected` 2 |
| 等在谁 | 协调人 1 · 评审方 2 |

| 级别 | 条目 | 状态 | 等在谁 | 标题 |
|---|---|---|---|---|
| **P3** | [`P3-2`](issues/P3-2.md) | `rejected` | 评审方 | restoreEnv() 只还原 LANG 与 LC_ALL；gettext 还会读 … |
| **P3** | [`P3-3`](issues/P3-3.md) | `question` | 协调人 | TranslatorFactory 对驱动配置数组没有 schema 校验——拼写错误的键或错误的类型会被静默忽略，或在下游产生令人困惑的错误。 |
| **-** | [`G4`](issues/G4.md) | `rejected` | 评审方 | 文档标准：项目标准是每一份文档都上英下汉——英文块在前，完全相同的中文块在后。本模块的 `CHANGELOG.md` 为纯英文。 … |

## 结论

工厂的前半确已修好；仍未决的是一条正当的公开行为问题，不是缺陷。

## 本轮已修复确认

No item was awaiting a verdict. The factory’s type guards and the three interpolation guards are consistent with the record, and the rejected CHANGELOG-scope item still stands.

## 测试盲区

扩展缺失时 gettext 用例跳过；两驱动「缺翻译回落为 key」的行为一致，但无跨驱动对照用例；ArrayTranslator::fromFile() 会 require 配置指定的 PHP 文件，仅由自带夹具覆盖。

## 验证方式

- `./vendor/bin/phpunit` · `composer analyse` · `composer validate`
- `phpunit.xml.dist` 中的 warning/notice/deprecation/risky 开关：四个全开
- 只有在上述开关打开时 PHP 警告才会导致套件失败；否则请显式加 `--fail-on-warning`。
