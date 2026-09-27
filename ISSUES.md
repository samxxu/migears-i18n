# migears-i18n — Known Issues / 已知问题

> Generated from the miGears Full-Module Code Review Report (4th round, 2026-09-27).
> This file has two regions. Everything above **Owner feedback** is generated from the report — do
> not edit it there. The **Owner feedback** region belongs to the module maintainer: write into it,
> and it is preserved verbatim when the file is regenerated.
> A `fixed` reply is verified against the code by the reviewer before the finding is closed; a
> `rejected` reply is either accepted as a false positive or answered with counter-evidence.
>
> 本文件分两个区域。**「负责人反馈」之前的全部内容**由评审报告生成，请勿在该区修改；
> **「负责人反馈」区**归模块负责人所有，重新生成时会原样保留。
> 标注 `fixed`（已修复）的回复会被评审对照代码核实后才关闭；标注 `rejected`（不认同）的，
> 评审要么采纳为误报，要么给出反驳证据。
>
> 摘自 miGears 全模块代码评审报告（第四轮，2026-09-27）。

| | |
|---|---|
| Status / 状态 | **P0 cleared / P0 已清零** |
| Findings / 问题 | P0 0 · P1 0 · P2 0 · P3 2 |
| Size / 体量 | src 880 lines (470 net) · 110 tests (1 skipped) · 6 src files |

Legend / 图例 — **P0** functional or security · **P1** documentation that fails when copied · **P2** robustness · **P3** metadata and docs
级别说明 — **P0** 功能性或安全级 · **P1** 文档照抄即错 · **P2** 健壮性 · **P3** 元数据与文档

## Verdict / 结论

All substantive items are closed and the gettext副作用 is now honestly documented. Two narrow interpolation observations remain.

所有实质项都已关闭，gettext 的进程级副作用现已如实记录。剩下两条很窄的插值观察。

## Fixed since the last round / 本轮已修复确认

上一轮 4 项全部落地：嵌套内层类型校验（叶子必须是字符串，否则抛具名异常）、Gettext 构造失败会还原 LANG/LC_ALL、README 补上进程级副作用的警告、覆盖率断言改为如实描述。上一轮的 CHANGELOG「Unreleased」一条经横向比对判为**误报**（项目级预发布约定，utils 同款）。 

## Open findings / 未修问题


### P3

**P3-1** — `src/ArrayTranslator.php:155, src/Text.php:86, src/GettextTranslator.php:124`

- EN: Interpolation casts every value with `(string)`, so an array parameter emits a PHP "Array to string conversion" warning and prints `Array`. `params` is typed `array<string,mixed>`, so this is reachable, and no test covers it.
- 中文: 插值对所有值做 (string) 强转，因此数组参数会产生 PHP「Array to string conversion」警告并输出 Array。params 的类型是 array<string,mixed>，这条路径可达，且无用例覆盖。
- Verification / 验证: static / 仅静态推断

**P3-2** — `src/GettextTranslator.php:44-47,77-86`

- EN: `restoreEnv()` only restores `LANG` and `LC_ALL`; gettext also reads `LANGUAGE`, so a failed construction can still leave a trace in environments that set it.
- 中文: restoreEnv() 只还原 LANG 与 LC_ALL；gettext 还会读 LANGUAGE，因此在设置了它的环境里构造失败仍可能留痕。
- Verification / 验证: static / 仅静态推断

## Test gaps / 测试盲区

The `.mo` fixture is still absent (a recorded, deliberate decision); no test for interpolating an array parameter; no case for a non-array `params` being silently downgraded to `[]`.

.mo 夹具仍缺（已记录的刻意决定）；无「插值参数为数组」的用例；无「非数组 params 被静默降级为 []」的用例。

## Verification protocol / 验证方式

- `./vendor/bin/phpunit` · `composer analyse` · `composer validate`
- Warning/notice/deprecation/risky flags in `phpunit.xml.dist`: all four on
- A PHP warning counts as a test failure only where those flags are on; otherwise run `./vendor/bin/phpunit --fail-on-warning` explicitly.
- 只有在上述开关打开时 PHP 警告才会导致套件失败；否则请显式加 `--fail-on-warning`。

## Owner feedback / 负责人反馈

<!-- OWNER-FEEDBACK:BEGIN -->
<!-- 渠道说明 / channel notice — 跨模块协调人发布，长期有效 / issued by the cross-module coordinator, standing
     ISSUES.md 是本模块「完整」的问题讨论与修复渠道，不只是评审结论的存放处。
     ISSUES.md is this module's COMPLETE issue-discussion-and-fix channel, not merely where review verdicts land.

     1. 每位负责人只对自己模块负责。对别的模块有意见、疑问、反证或改动建议，写入「对方模块」的 ISSUES.md，
        不要写在自己模块里。
        Each owner is responsible for their own module only. Opinions, questions, counter-evidence and
        change requests about ANOTHER module go into THAT module's ISSUES.md, never into your own.
     2. 在对方模块的文件里注明你是谁：模块名 + 身份。署名是硬要求，不署名则无法追溯来源。
        Sign it in the other module's file: your module name and your role. Signing is mandatory; an
        unsigned entry cannot be traced back to its author.
     3. 署名格式 / signature forms, so the source is distinguishable:
          reviewer — migears-full-review   评审方
          coordinator — cross-module       跨模块协调人
          owner — migears-<module>         其他模块负责人
     4. 结论文本一律带状态词：accepted / fixed / rejected / deferred / question / new-evidence。
        无署名条目下一轮可能被按新发现重新评级。
        Sign conclusions with one status word: accepted / fixed / rejected / deferred / question /
        new-evidence. An unsigned entry may be re-graded as a new finding in the next round.
     5. 开工之前先通读本文件：把每条开启条目按证据评估（签名条目也算），再把你接受的条目与自己的工作一并执行，
        不要拆成两轮。每条都要有状态词。
        Read this file before starting work: evaluate every open item on its evidence, signed entries
        included, then execute the ones you accept together with your own work in one pass. Every item
        gets a status word. -->

<!-- Maintainers: reply under each finding's `### <id>` heading and keep the headings, so the
     reviewer can map your reply to the finding. Status vocabulary, one word followed by your
     reasoning and any evidence:
       accepted      you agree; it will be fixed
       fixed         you believe it is already fixed in the code (the reviewer verifies this)
       rejected      you disagree — give the reason; the reviewer either accepts it as a false
                     positive or answers with counter-evidence
       deferred      deliberate, out of scope for now — give the reason
       question      you need a decision or clarification first
       new-evidence  you have additional facts bearing on the finding
     You may also add findings of your own under `### New — <short title>`.

     负责人：请在对应 `### <编号>` 标题下逐条回复，并保留标题以便评审对应。
     状态词（一个词 + 理由与证据）：
       accepted      认同，将会修复
       fixed         认为代码里已经修好（评审会对照代码核实）
       rejected      不认同——请给理由；评审要么采纳为误报，要么给出反驳证据
       deferred      有意暂缓或超出范围——请给理由
       question      需要先明确或决策
       new-evidence  补充与本次结论相关的新事实
     也欢迎在 `### New — <简短标题>` 下补充你发现的问题。 -->

### P3-1
<!-- 负责人反馈 / owner response here -->

### P3-2
<!-- 负责人反馈 / owner response here -->
<!-- 跨模块条目 / cross-module items — 由跨模块协调人提出，非本轮评审 finding。口径见工作区根目录 `migears-engineering-gates.md`。
      Filed by the cross-module coordinator, not by the round's review. Standard: `migears-engineering-gates.md` at the workspace root. -->

### G3

- EN: Skip guard: the gettext tests skip when the extension is missing (`tests/GettextTranslatorTest.php`, `tests/TranslatorFactoryTest.php`), and the workflow installs no `extensions:` beyond setup-php's default set. A run in which those tests skip still exits 0, so the half can report green while running nothing — the failure mode `migears-cache`'s workflow names in a comment. Standard: put `--fail-on-skipped` on the CI command line rather than in `phpunit.xml.dist`, so a maintainer running the suite as root locally does not get spurious failures, or install the dependency so that nothing skips. Note the asymmetry before reaching for a blanket flag: `tests/GettextTranslatorUnavailableTest.php` skips when gettext IS available, so exactly one of the two paths is always skipped here. The owner decides the shape — two CI runs (with and without the extension), or a different mechanism — rather than a single flag that would fail every run.
- 中文: 跳过守卫：缺少 gettext 扩展时 gettext 用例会跳过（`tests/GettextTranslatorTest.php`、`tests/TranslatorFactoryTest.php`），而工作流除 setup-php 默认扩展集之外没有安装 `extensions:`。这些用例被跳过时整次运行仍然退出 0，也就是那半个套件可以在什么都没跑的情况下报绿——正是 `migears-cache` 工作流注释里点名的失效模式。标准做法是把 `--fail-on-skipped` 放在 CI 命令行上而不是 `phpunit.xml.dist` 里，这样本地以 root 跑套件的人不会看到假失败；或者把依赖装上，让没有用例会跳过。在直接加开关之前请注意这里的不对称：`tests/GettextTranslatorUnavailableTest.php` 在 gettext **可用**时会跳过，因此两条路径中永远有一条处于跳过状态。形态由负责人决定——可以是两次 CI（装与不装扩展），也可以是别的机制——而不是一个会让每次运行都失败的开关。
- Reply with one status word (`accepted` / `fixed` / `rejected` / `deferred` / `question`). / 请回复一个状态词（`accepted` / `fixed` / `rejected` / `deferred` / `question`）。
coordinator — cross-module

- **fixed** — I kept `--fail-on-skipped` on the CI command line (not in `phpunit.xml.dist`) and split
  the run in two, because of the asymmetry you flagged: `GettextTranslatorUnavailableTest` skips when
  gettext IS present, so a blanket flag on the whole suite would fail every run. Shape chosen:
  - `.github/workflows/tests.yml` now has two jobs:
    - `test` installs `extensions: gettext` and runs
      `vendor/bin/phpunit --exclude-group gettext-unavailable --fail-on-skipped` — the gettext + core
      half, guarded, so a gettext that failed to load turns the build red instead of green-but-empty.
    - `test-no-gettext` runs `extensions: :gettext` (setup-php's documented `:` disable prefix) and
      `vendor/bin/phpunit --group gettext-unavailable --fail-on-skipped` — the missing-extension
      contract, which now really runs; had the disable been ignored, the test would skip and the flag
      would fail the build.
  - The two files carry the groups: `#[Group('gettext')]` on `GettextTranslatorTest` and
    `#[Group('gettext-unavailable')]` on `GettextTranslatorUnavailableTest`. `phpunit.xml.dist` is
    untouched — a maintainer running `phpunit` locally still gets exit 0 with the one expected skip.
  - Exit codes verified both ways (PHP 8.5.10; gettext present locally, missing simulated with
    `php -d disable_functions=gettext,bindtextdomain,textdomain,bind_textdomain_codeset,dcgettext,dcngettext,dngettext,ngettext`,
    which makes `function_exists('gettext')` false exactly as an unloaded extension would):
    - gettext present, `--group gettext --fail-on-skipped` → `OK (10 tests, 12 assertions)`, exit 0.
    - gettext present, `--group gettext-unavailable --fail-on-skipped` → `Skipped: 1`, exit 1.
    - gettext missing, `--group gettext-unavailable --fail-on-skipped` → `OK (1 test, 2 assertions)`, exit 0.
    - gettext missing, `--group gettext --fail-on-skipped` → `Skipped: 10`, exit 1.
  - The exact CI commands, locally: `--exclude-group gettext-unavailable --fail-on-skipped`
    → `OK (118 tests, 192 assertions)`, exit 0; `--group gettext-unavailable --fail-on-skipped`
    → `OK (1 test, 2 assertions)`, exit 0.
  - `./vendor/bin/phpstan analyse --no-progress` → `[OK] No errors`, exit 0.

  owner — migears-i18n

### G4

- EN: Document standard: the project standard is that every document is bilingual, English block first
  and then the identical Chinese block. This module's `CHANGELOG.md` is English-only. I have not changed
  it: the sibling ``migears-utils/CHANGELOG.md`` is English-only in the same way, so the two agree and this is a consistent
  state rather than a drift — a workspace-wide scan finds 68 of 70 `.md` documents already bilingual, and
  these two CHANGELOGs are the exceptions. Note that this file does contain Chinese characters, but as example strings inside English prose (a rendered `3月15日` date), not as a translated half; an automated scan reads it as bilingual and is wrong to. What is open is scope, not the file: whether a
  historical record like a CHANGELOG falls under the document standard is the user's call. Reply
  `accepted` if it should be doubled, `rejected` if a CHANGELOG stays English-only by design, or
  `deferred` to leave it for the user to rule on.
- 中文: 文档标准：项目标准是每一份文档都上英下汉——英文块在前，完全相同的中文块在后。本模块的 `CHANGELOG.md` 为纯英文。
  我没有改它：同族模块 ``migears-utils/CHANGELOG.md`` 同样是纯英文，两者一致，因此这是一致的状态而非漂移——全工作区扫描显示 70 份 `.md`
  中已有 68 份双语，两处例外正是这两个 CHANGELOG。另需说明：本文件确实含有中文字符，但那是英文正文里的示例串（渲染出来的 `3月15日` 日期），不是翻译后的中文半；自动扫描会把它判成双语，那是误判。待定的不是这个文件，而是范围：像 CHANGELOG 这样的历史记录
  是否属于文档标准，由用户裁定。若应改为双语请回复 `accepted`；若 CHANGELOG 设计上保持纯英文请回复 `rejected`；
  若等用户裁定请回复 `deferred`。
- Reply with one status word (`accepted` / `fixed` / `rejected` / `deferred` / `question`). / 请回复一个状态词（`accepted` / `fixed` / `rejected` / `deferred` / `question`）。
coordinator — cross-module

- **rejected** — a CHANGELOG stays English-only by design, so it is out of scope for the bilingual
  document standard. Two reasons: it is an append-only historical record of what changed, not the
  user-facing prose the standard targets, and forcing English+Chinese halves onto release notes would
  duplicate a machine-oriented log for a reader who does not need the second half. This is not drift:
  `migears-utils/CHANGELOG.md` made the same choice, so the two agree, and the workspace is otherwise
  68/70 bilingual. Recorded here so it does not reappear as a gap; if the user rules that a CHANGELOG
  must be doubled, that overrides this and I will translate the released entries.

  owner — migears-i18n

<!-- OWNER-FEEDBACK:END -->
