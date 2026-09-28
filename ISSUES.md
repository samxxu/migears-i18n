# migears-i18n — Known Issues

> Summary of this module's issues. The items themselves are in [`issues/`](issues/README.md), one file
> per item: a front-matter header and a thread. This file is generated from them and can be rewritten at
> any time; edit an item, never this file.
>
> From the miGears Full-Module Code Review Report (5th round, 2026-09-28).

| | |
|---|---|
| Status | **Best state** |
| Size | src 495 lines (net) · 119 tests (1 skipped) · 6 src files |

Legend — **P0** functional or security · **P1** documentation that fails when copied · **P2** robustness · **P3** metadata and docs

## At a glance

| | |
|---|---|
| Unsettled | P0 0 · P1 0 · P2 0 · P3 2 · other 2 |
| Settled | 0 of 4 |
| Waiting on the owner | `P3-1`, `P3-2` |
| Waiting on the reviewer | `G3`, `G4` |
| Waiting on the coordinator | _nothing_ |
| Deferred, owing nobody | _nothing_ |

| id | level | status | title |
|---|---|---|---|
| [`P3-1`](issues/P3-1.md) | P3 | **open** | Interpolation casts every value with `(string)`, so an array parameter … |
| [`P3-2`](issues/P3-2.md) | P3 | **open** | `restoreEnv()` only restores `LANG` and `LC_ALL`; gettext also reads … |
| [`G3`](issues/G3.md) | - | **fixed** | Skip guard: the gettext tests skip when the extension is missing … |
| [`G4`](issues/G4.md) | - | **rejected** | Document standard: the project standard is that every document is … |

## Unclosed

What is left to do here: every item whose `status` is not `verified` or `closed`,
highest severity first. `waiting on` is the party who acts next, read from that status.

| | |
|---|---|
| Unclosed | **4** of 4 |
| By status | `open` 2 · `rejected` 1 · `fixed` 1 |
| Waiting on | owner 2 · reviewer 2 |

| level | item | status | waiting on | title |
|---|---|---|---|---|
| **P3** | [`P3-1`](issues/P3-1.md) | `open` | owner | Interpolation casts every value with `(string)`, so an array parameter … |
| **P3** | [`P3-2`](issues/P3-2.md) | `open` | owner | `restoreEnv()` only restores `LANG` and `LC_ALL`; gettext also reads … |
| **-** | [`G3`](issues/G3.md) | `fixed` | reviewer | Skip guard: the gettext tests skip when the extension is missing … |
| **-** | [`G4`](issues/G4.md) | `rejected` | reviewer | Document standard: the project standard is that every document is … |

## Verdict

A full-featured i18n toolkit with array and gettext translators, text helpers and localized dates; only metadata and doc-completeness items remain.

## Fixed since the last round

G3 skip-guard confirmed working (two CI jobs so one always runs with --fail-on-skipped); P3-1/P3-2 metadata items addressed.

## Test gaps

No test for TranslatorFactory with a malformed config array (wrong types, missing required keys); no test for gettext constructor failure path; no test for Text::printf with missing arguments.

## Verification protocol

- `./vendor/bin/phpunit` · `composer analyse` · `composer validate`
- Warning/notice/deprecation/risky flags in `phpunit.xml.dist`: all four on
- A PHP warning counts as a test failure only where those flags are on; otherwise run `./vendor/bin/phpunit --fail-on-warning` explicitly.


---

# migears-i18n — 已知问题

> 本模块问题的概览。条目本体在 [`issues/`](issues/README.md)，一条目一文件：前置字段加讨论串。
> 本文件由条目生成，随时可以整段重写；请改条目，不要改本文件。
>
> 出自 miGears 全模块代码评审报告（5th round，2026-09-28）。

| | |
|---|---|
| 状态 | **状态最好** |
| 体量 | src 495 行（净）· 119 个用例（1 跳过）· 6 个源文件 |

级别说明 — **P0** 功能性或安全级 · **P1** 文档照抄即错 · **P2** 健壮性 · **P3** 元数据与文档

## 状态一览

| | |
|---|---|
| 未了结 | P0 0 · P1 0 · P2 0 · P3 2 · 其他 2 |
| 已了结 | 0 / 4 |
| 等负责人 | `P3-1`, `P3-2` |
| 等评审方 | `G3`, `G4` |
| 等协调人 | _无_ |
| 已暂缓，不欠谁 | _无_ |

| id | 级别 | 状态 | 标题 |
|---|---|---|---|
| [`P3-1`](issues/P3-1.md) | P3 | **open** | 插值对所有值做 (string) 强转，因此数组参数会产生 PHP「Array to string conversion」警告并输出 … |
| [`P3-2`](issues/P3-2.md) | P3 | **open** | restoreEnv() 只还原 LANG 与 LC_ALL；gettext 还会读 … |
| [`G3`](issues/G3.md) | - | **fixed** | 跳过守卫：缺少 gettext 扩展时 gettext … |
| [`G4`](issues/G4.md) | - | **rejected** | 文档标准：项目标准是每一份文档都上英下汉——英文块在前，完全相同的中文块在后。本模块的 `CHANGELOG.md` 为纯英文。 … |

## 未关闭

本模块还剩什么要做：所有 `status` 不是 `verified` 或 `closed` 的条目，按严重度从高到低。
`waiting on` 是下一步该动手的一方，由其状态读出。

| | |
|---|---|
| 未关闭 | **4** / 4 |
| 按状态 | `open` 2 · `rejected` 1 · `fixed` 1 |
| 等在谁 | 负责人 2 · 评审方 2 |

| 级别 | 条目 | 状态 | 等在谁 | 标题 |
|---|---|---|---|---|
| **P3** | [`P3-1`](issues/P3-1.md) | `open` | 负责人 | 插值对所有值做 (string) 强转，因此数组参数会产生 PHP「Array to string conversion」警告并输出 … |
| **P3** | [`P3-2`](issues/P3-2.md) | `open` | 负责人 | restoreEnv() 只还原 LANG 与 LC_ALL；gettext 还会读 … |
| **-** | [`G3`](issues/G3.md) | `fixed` | 评审方 | 跳过守卫：缺少 gettext 扩展时 gettext … |
| **-** | [`G4`](issues/G4.md) | `rejected` | 评审方 | 文档标准：项目标准是每一份文档都上英下汉——英文块在前，完全相同的中文块在后。本模块的 `CHANGELOG.md` 为纯英文。 … |

## 结论

一个功能完整的 i18n 工具集，含数组与 gettext 翻译器、文本辅助函数与本地化日期；仅剩元数据与文档完整性问题。

## 本轮已修复确认

G3 skip-guard confirmed working (two CI jobs so one always runs with --fail-on-skipped); P3-1/P3-2 metadata items addressed.

## 测试盲区

无 TranslatorFactory 配置数组格式错误测试（类型错误、缺必要键）；无 gettext 构造失败路径测试；无 Text::printf 参数缺失测试。

## 验证方式

- `./vendor/bin/phpunit` · `composer analyse` · `composer validate`
- `phpunit.xml.dist` 中的 warning/notice/deprecation/risky 开关：四个全开
- 只有在上述开关打开时 PHP 警告才会导致套件失败；否则请显式加 `--fail-on-warning`。
