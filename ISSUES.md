# migears-i18n — Known Issues / 已知问题

> Summary of this module's issues. The items themselves are in [`issues/`](issues/README.md), one file
> per item: a front-matter header and a thread. This file is generated from them and can be rewritten at
> any time; edit an item, never this file.
>
> 本模块问题的概览。条目本体在 [`issues/`](issues/README.md)，一条目一文件：前置字段加讨论串。
> 本文件由条目生成，随时可以整段重写；请改条目，不要改本文件。
>
> From the miGears Full-Module Code Review Report (4th round, 2026-09-27).

| | |
|---|---|
| Status / 状态 | **P0 cleared / P0 已清零** |
| Size / 体量 | src 880 lines (470 net) · 110 tests (1 skipped) · 6 src files |

Legend / 图例 — **P0** functional or security · **P1** documentation that fails when copied · **P2** robustness · **P3** metadata and docs
级别说明 — **P0** 功能性或安全级 · **P1** 文档照抄即错 · **P2** 健壮性 · **P3** 元数据与文档

## At a glance / 状态一览

| | |
|---|---|
| Items / 条目 | P0 0 · P1 0 · P2 0 · P3 2 · other 2 |
| Answered / 已回复 | 2 of 4 |
| Waiting / 等待回复 | `P3-1`, `P3-2` |

| id | level | status | title |
|---|---|---|---|
| [`P3-1`](issues/P3-1.md) | P3 | **open** | Interpolation casts every value with `(string)`, so an array parameter … |
| [`P3-2`](issues/P3-2.md) | P3 | **open** | `restoreEnv()` only restores `LANG` and `LC_ALL`; gettext also reads … |
| [`G3`](issues/G3.md) | - | **fixed** | Skip guard: the gettext tests skip when the extension is missing … |
| [`G4`](issues/G4.md) | - | **rejected** | Document standard: the project standard is that every document is … |

## Verdict / 结论

All substantive items are closed and the gettext副作用 is now honestly documented. Two narrow interpolation observations remain.

所有实质项都已关闭，gettext 的进程级副作用现已如实记录。剩下两条很窄的插值观察。

## Fixed since the last round / 本轮已修复确认

上一轮 4 项全部落地：嵌套内层类型校验（叶子必须是字符串，否则抛具名异常）、Gettext 构造失败会还原 LANG/LC_ALL、README 补上进程级副作用的警告、覆盖率断言改为如实描述。上一轮的 CHANGELOG「Unreleased」一条经横向比对判为**误报**（项目级预发布约定，utils 同款）。 

## Test gaps / 测试盲区

The `.mo` fixture is still absent (a recorded, deliberate decision); no test for interpolating an array parameter; no case for a non-array `params` being silently downgraded to `[]`.

.mo 夹具仍缺（已记录的刻意决定）；无「插值参数为数组」的用例；无「非数组 params 被静默降级为 []」的用例。

## Verification protocol / 验证方式

- `./vendor/bin/phpunit` · `composer analyse` · `composer validate`
- Warning/notice/deprecation/risky flags in `phpunit.xml.dist`: all four on
- A PHP warning counts as a test failure only where those flags are on; otherwise run `./vendor/bin/phpunit --fail-on-warning` explicitly.
- 只有在上述开关打开时 PHP 警告才会导致套件失败；否则请显式加 `--fail-on-warning`。
