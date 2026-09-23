# migears/i18n

![Version](https://img.shields.io/badge/version-2.0.0-blue)

A minimalist internationalization (i18n) translation library. Zero mandatory dependencies, PHP 8.1+, based on PHP array translation files, with optional gettext support.

> **Background**: miGears is the open-source successor of **TinyGears**, a
> self-developed PHP framework. It was renamed and open-sourced recently because
> the name *TinyGears* is already taken in the open-source community.

## Features

- **Zero mandatory dependencies** - Works out of the box, no extensions required
- **Minimalist API** - `new ArrayTranslator($translations)` is all you need
- **PHP array translation files** - No .mo/.po files, easy to understand and maintain
- **Variable interpolation** - `translate('HELLO_USER', ['user' => 'Alice'])` → `"Hello, Alice"`
- **Multi-domain support** - Organize translations by module
- **Text object** - Deferred translation text object with JSON serialization support
- **Localized dates** - `LocalizedDate` presents a timestamp in the viewer's timezone, rendering through the translator
- **English by default** - Returns the key itself as fallback when translation is not found
- **100% unit test coverage**

## Installation

```bash
composer require migears/i18n
```

Optional: install `ext-gettext` to use `GettextTranslator`.

## Quick Start

### Basic Usage

```php
use MiGears\I18n\ArrayTranslator;

$translator = new ArrayTranslator([
    'HELLO' => 'Hello',
    'HELLO_USER' => 'Hello, %user%',
    'WELCOME_BACK' => 'Welcome back, %user%! You have %count% new messages.',
]);

echo $translator->translate('HELLO');                          // Hello
echo $translator->translate('HELLO_USER', ['user' => 'Alice']); // Hello, Alice
echo $translator->translate('WELCOME_BACK', [
    'user' => 'Bob',
    'count' => 5,
]); // Welcome back, Bob! You have 5 new messages.
```

### Loading Translations from PHP Files

```php
$translator = ArrayTranslator::fromFile('/path/to/translations.php');
```

Translation file `translations.php`:

```php
<?php
return [
    'HELLO' => 'Hello',
    'HELLO_USER' => 'Hello, %user%',
];
```

### Multiple Domains

```php
$translator = new ArrayTranslator([
    'messages' => [
        'HELLO' => 'Hello',
    ],
    'errors' => [
        'NOT_FOUND' => 'Page not found',
        'FORBIDDEN' => 'User %user% is not authorized',
    ],
]);

echo $translator->translate('HELLO', [], 'messages');        // Hello
echo $translator->translate('NOT_FOUND', [], 'errors');      // Page not found
echo $translator->translate('FORBIDDEN', ['user' => 'admin'], 'errors'); // User admin is not authorized
```

### Text Object (Deferred Translation)

```php
use MiGears\I18n\Text;

$text = new Text('HELLO_USER', ['user' => 'Alice']);

// Inject translator later
$text->setTranslator($translator);

echo $text; // Hello, Alice
```

Text objects support JSON serialization/deserialization, making it easy to pass translatable text in APIs:

```php
$json = json_encode($text); // {"key":"HELLO_USER","params":{"user":"Alice"},"domain":null}
$restored = Text::fromJson(json_decode($json, true));
```

### Gettext Support (Optional)

Requires the `ext-gettext` extension:

```php
use MiGears\I18n\GettextTranslator;

$translator = new GettextTranslator(
    defaultDomain: 'messages',
    locale: 'zh_CN.UTF-8',
    directory: '/path/to/locale',
);

$translator->addDomain('errors', '/path/to/locale');

echo $translator->translate('HELLO_USER', ['user' => 'Alice']);
```

### Driver Factory

Use `TranslatorFactory` to create a translator from a configuration array — ideal when the driver choice depends on runtime config or environment:

```php
use MiGears\I18n\TranslatorFactory;

// Array driver
$translator = TranslatorFactory::create([
    'driver' => 'array',
    'translations' => [
        'HELLO' => 'Hello',
        'HELLO_USER' => 'Hello, %user%',
    ],
]);

// Array driver — load from file
$translator = TranslatorFactory::create([
    'driver' => 'array',
    'file' => '/path/to/translations.php',
]);

// Gettext driver
$translator = TranslatorFactory::create([
    'driver'    => 'gettext',
    'locale'    => 'zh_CN.UTF-8',
    'domain'    => 'messages',
    'directory' => '/path/to/locale',
]);
```

### Localized Dates

`LocalizedDate` binds a timestamp to a viewer's timezone and translator. It reports neutral facts and renders text through the translator, so the class itself carries no language.

Rendering is for the server — use it in a template. Serialization stays raw, because a payload carrying rendered text would lock the client into this server's language:

```php
echo $createdAt->humanize();   // 今天 10:30
echo $createdAt->relative();   // 2 小时前

echo $createdAt;               // "2026-09-21 14:30" — locale-neutral

echo json_encode(['created_at' => $createdAt]);
// {"created_at":{"timestamp":1758450600,"iso":"2026-09-21T14:30:00+08:00","timezone":"Asia/Shanghai"}}
```

```php
use MiGears\I18n\ArrayTranslator;
use MiGears\I18n\LocalizedDate;

$translator = new ArrayTranslator([
    'date.weekday.0' => '周日',   // ... through date.weekday.6
    'date.humanize.today' => '今天 %time%',
    'date.humanize.yesterday' => '昨天 %time%',
    'date.humanize.tomorrow' => '明天 %time%',
    'date.humanize.weekday' => '%weekday% %time%',
    'date.humanize.date' => '%month%月%day%日 %time%',
    'date.humanize.dateOtherYear' => '%year%年%month%月%day%日 %time%',
    'date.relative.past.hour' => '%count% 小时前',
    // ... the rest of the date.relative.* keys
]);

$createdAt = new LocalizedDate($row['created_at'], $timezone, $translator);
```

The keys it looks up:

| Key | Params | Used for |
|-----|--------|----------|
| `date.relative.{direction}.{unit}` | `%count%` | `direction` is `past` or `future`; `unit` is `moment`, `minute`, `hour`, `day`, `week`, `month`, `year` |
| `date.weekday.{0-6}` | — | A weekday name, 0 = Sunday; feeds `date.humanize.weekday` |
| `date.humanize.today` / `.yesterday` / `.tomorrow` | `%time%` | The three days around now |
| `date.humanize.weekday` | `%weekday%`, `%time%` | The recent past, within six days |
| `date.humanize.date` | `%month%`, `%day%`, `%time%`, `%date%` | Older than that, still in the current year |
| `date.humanize.dateOtherYear` | `%year%`, `%month%`, `%day%`, `%time%`, `%date%` | Anything older |

`%month%` and `%day%` arrive as unpadded numbers, so `3月15日` does not turn into `03月15日`; `%date%` carries the ISO `Y-m-d` string for tables that prefer it. `relative()` and `humanize()` throw a `LogicException` when no translator was supplied; `relativeParts()` and `humanizeParts()` need none.

## API Reference

### TranslatorInterface

```php
interface TranslatorInterface
{
    public function translate(string $key, array $params = [], ?string $domain = null): string;
}
```

### ArrayTranslator

```php
// Constructor - supports flat array (single domain) or nested array (multiple domains)
new ArrayTranslator(array $translations, string $defaultDomain = 'messages');

// Load from PHP file
ArrayTranslator::fromFile(string $file, string $defaultDomain = 'messages'): self;

// Translate
$translator->translate(string $key, array $params = [], ?string $domain = null): string;
```

### Text

```php
new Text(string $key, array $params = [], ?string $domain = null);

$text->setTranslator(TranslatorInterface $translator): self;
$text->getKey(): string;
$text->getParams(): array;
$text->getDomain(): ?string;
(string) $text; // triggers translation

// JSON serialization
$text->jsonSerialize(): array;
Text::fromJson(array $data): self;
```

### TranslatorFactory

```php
// Create from config array — driver: "array" or "gettext"
TranslatorFactory::create(array $config): TranslatorInterface;
```

### LocalizedDate

| Method | Description |
|--------|-------------|
| `new LocalizedDate($input = null, $timezone = null, $translator = null)` | Bind a timestamp to a viewer's timezone and translator |
| `LocalizedDate::fromTimestamp($ts, $tz = null, $translator = null)` | Create from a Unix timestamp |
| `LocalizedDate::fromString($str, $tz = null, $translator = null)` | Create from a datetime string |
| `relative()` | Rendered distance from now, e.g. `2 小时前` |
| `humanize()` | Rendered friendly timestamp, e.g. `今天 10:30` |
| `relativeParts()` | Language-free distance from now |
| `humanizeParts()` | Language-free bucket for a friendly timestamp |
| `toDateTime()` / `timestamp()` | Underlying `DateTimeImmutable` / Unix timestamp |
| `toDateString()` / `toDateTimeString()` / `format($pattern)` | Locale-neutral formatting |
| `dayOfWeek()` | 0 (Sun) - 6 (Sat) |
| `isToday()` / `isYesterday()` / `isTomorrow()` | Comparison in the date's own timezone |
| `withTimezone($tz)` | Convert timezone (immutable; translator carried over) |
| `timezone()` | Get current timezone |
| `jsonSerialize()` | Raw `timestamp` / `iso` / `timezone`, never rendered text |
| `__toString()` | Locale-neutral `Y-m-d H:i` |

## Design Principles

- **No singletons** - Translators are plain objects, freely instantiable and injectable
- **No global state** - Does not depend on any Context or Registry
- **No logging dependency** - Does not log anything, letting the caller decide how to handle it
- **English by default** - Translation keys themselves are in English, returning the key directly when no translation is found
- **Optional translator injection** - Text objects work even without a translator (returns key + interpolation)

## Testing

```bash
composer install
./vendor/bin/phpunit
```

## License

MIT

---

# migears/i18n

![Version](https://img.shields.io/badge/version-2.0.0-blue)

极简国际化（i18n）翻译库。零强制依赖，PHP 8.1+，基于 PHP 数组的翻译文件，也可选支持 gettext。

## 特性

- **零强制依赖** - 开箱即用，不需要任何扩展
- **极简 API** - `new ArrayTranslator($translations)` 就能用
- **PHP 数组翻译文件** - 不用 .mo/.po，易于理解和维护
- **变量插值** - `translate('HELLO_USER', ['user' => 'Alice'])` → `"Hello, Alice"`
- **多 domain 支持** - 按模块组织翻译
- **Text 对象** - 可延迟翻译的文本对象，支持 JSON 序列化
- **本地化日期** - `LocalizedDate` 按用户时区呈现时间戳，并通过翻译器渲染文案
- **默认英文** - 找不到翻译时返回 key 本身作为降级
- **100% 单元测试覆盖率**

## 安装

```bash
composer require migears/i18n
```

可选：安装 `ext-gettext` 以使用 `GettextTranslator`。

## 快速开始

### 基本用法

```php
use MiGears\I18n\ArrayTranslator;

$translator = new ArrayTranslator([
    'HELLO' => '你好',
    'HELLO_USER' => '你好，%user%',
    'WELCOME_BACK' => '欢迎回来，%user%！你有 %count% 条新消息。',
]);

echo $translator->translate('HELLO');                          // 你好
echo $translator->translate('HELLO_USER', ['user' => 'Alice']); // 你好，Alice
echo $translator->translate('WELCOME_BACK', [
    'user' => 'Bob',
    'count' => 5,
]); // 欢迎回来，Bob！你有 5 条新消息。
```

### 从 PHP 文件加载翻译

```php
$translator = ArrayTranslator::fromFile('/path/to/translations.php');
```

翻译文件 `translations.php`：

```php
<?php
return [
    'HELLO' => '你好',
    'HELLO_USER' => '你好，%user%',
];
```

### 多 Domain

```php
$translator = new ArrayTranslator([
    'messages' => [
        'HELLO' => '你好',
    ],
    'errors' => [
        'NOT_FOUND' => '页面未找到',
        'FORBIDDEN' => '用户 %user% 无权访问',
    ],
]);

echo $translator->translate('HELLO', [], 'messages');        // 你好
echo $translator->translate('NOT_FOUND', [], 'errors');      // 页面未找到
echo $translator->translate('FORBIDDEN', ['user' => 'admin'], 'errors'); // 用户 admin 无权访问
```

### Text 对象（延迟翻译）

```php
use MiGears\I18n\Text;

$text = new Text('HELLO_USER', ['user' => 'Alice']);

// 稍后注入翻译器
$text->setTranslator($translator);

echo $text; // 你好，Alice
```

Text 对象支持 JSON 序列化/反序列化，便于在 API 中传递可翻译文本：

```php
$json = json_encode($text); // {"key":"HELLO_USER","params":{"user":"Alice"},"domain":null}
$restored = Text::fromJson(json_decode($json, true));
```

### Gettext 支持（可选）

需要 `ext-gettext` 扩展：

```php
use MiGears\I18n\GettextTranslator;

$translator = new GettextTranslator(
    defaultDomain: 'messages',
    locale: 'zh_CN.UTF-8',
    directory: '/path/to/locale',
);

$translator->addDomain('errors', '/path/to/locale');

echo $translator->translate('HELLO_USER', ['user' => 'Alice']);
```

### 驱动工厂

使用 `TranslatorFactory` 通过配置数组创建翻译器——适合驱动选择取决于运行时配置或环境的场景：

```php
use MiGears\I18n\TranslatorFactory;

// Array 驱动
$translator = TranslatorFactory::create([
    'driver' => 'array',
    'translations' => [
        'HELLO' => '你好',
        'HELLO_USER' => '你好，%user%',
    ],
]);

// Array 驱动 — 从文件加载
$translator = TranslatorFactory::create([
    'driver' => 'array',
    'file' => '/path/to/translations.php',
]);

// Gettext 驱动
$translator = TranslatorFactory::create([
    'driver'    => 'gettext',
    'locale'    => 'zh_CN.UTF-8',
    'domain'    => 'messages',
    'directory' => '/path/to/locale',
]);
```

### 本地化日期

`LocalizedDate` 把时间戳绑定到用户的时区与翻译器。它给出中立的事实，文案则通过翻译器渲染，因此类本身不含任何语言。

渲染用于服务端——在模板里调用即可。序列化保持原始数据，因为载荷里带着渲染后的文案，等于把客户端锁死在服务端的语言上：

```php
echo $createdAt->humanize();   // 今天 10:30
echo $createdAt->relative();   // 2 小时前

echo $createdAt;               // "2026-09-21 14:30" — 与语言无关

echo json_encode(['created_at' => $createdAt]);
// {"created_at":{"timestamp":1758450600,"iso":"2026-09-21T14:30:00+08:00","timezone":"Asia/Shanghai"}}
```

```php
use MiGears\I18n\ArrayTranslator;
use MiGears\I18n\LocalizedDate;

$translator = new ArrayTranslator([
    'date.weekday.0' => '周日',   // ... 到 date.weekday.6
    'date.humanize.today' => '今天 %time%',
    'date.humanize.yesterday' => '昨天 %time%',
    'date.humanize.tomorrow' => '明天 %time%',
    'date.humanize.weekday' => '%weekday% %time%',
    'date.humanize.date' => '%month%月%day%日 %time%',
    'date.humanize.dateOtherYear' => '%year%年%month%月%day%日 %time%',
    'date.relative.past.hour' => '%count% 小时前',
    // ... 其余 date.relative.* 键
]);

$createdAt = new LocalizedDate($row['created_at'], $timezone, $translator);
```

它会查找的键：

| 键 | 参数 | 用途 |
|-----|--------|----------|
| `date.relative.{direction}.{unit}` | `%count%` | `direction` 为 `past` 或 `future`；`unit` 为 `moment`、`minute`、`hour`、`day`、`week`、`month`、`year` |
| `date.weekday.{0-6}` | — | 星期名，0 为周日；供 `date.humanize.weekday` 使用 |
| `date.humanize.today` / `.yesterday` / `.tomorrow` | `%time%` | 今天前后三天 |
| `date.humanize.weekday` | `%weekday%`、`%time%` | 近期过去，六天内 |
| `date.humanize.date` | `%month%`、`%day%`、`%time%`、`%date%` | 更早，且仍在当年 |
| `date.humanize.dateOtherYear` | `%year%`、`%month%`、`%day%`、`%time%`、`%date%` | 更早的年份 |

`%month%` 与 `%day%` 传入的是不补零的数字，因此 `3月15日` 不会被写成 `03月15日`；`%date%` 提供 ISO 的 `Y-m-d` 字符串备用。未注入翻译器时 `relative()` 与 `humanize()` 抛 `LogicException`；`relativeParts()` 与 `humanizeParts()` 不需要翻译器。

## API 参考

### TranslatorInterface

```php
interface TranslatorInterface
{
    public function translate(string $key, array $params = [], ?string $domain = null): string;
}
```

### ArrayTranslator

```php
// 构造函数 - 支持扁平数组（单 domain）或嵌套数组（多 domain）
new ArrayTranslator(array $translations, string $defaultDomain = 'messages');

// 从 PHP 文件加载
ArrayTranslator::fromFile(string $file, string $defaultDomain = 'messages'): self;

// 翻译
$translator->translate(string $key, array $params = [], ?string $domain = null): string;
```

### Text

```php
new Text(string $key, array $params = [], ?string $domain = null);

$text->setTranslator(TranslatorInterface $translator): self;
$text->getKey(): string;
$text->getParams(): array;
$text->getDomain(): ?string;
(string) $text; // 触发翻译

// JSON 序列化
$text->jsonSerialize(): array;
Text::fromJson(array $data): self;
```

### TranslatorFactory

```php
// 从配置数组创建 — driver: "array" 或 "gettext"
TranslatorFactory::create(array $config): TranslatorInterface;
```

### LocalizedDate

| 方法 | 说明 |
|--------|-------------|
| `new LocalizedDate($input = null, $timezone = null, $translator = null)` | 把时间戳绑定到用户时区与翻译器 |
| `LocalizedDate::fromTimestamp($ts, $tz = null, $translator = null)` | 从 Unix 时间戳创建 |
| `LocalizedDate::fromString($str, $tz = null, $translator = null)` | 从日期时间字符串创建 |
| `relative()` | 渲染后的时间距离，如 `2 小时前` |
| `humanize()` | 渲染后的友好时间戳，如 `今天 10:30` |
| `relativeParts()` | 与当前时间的距离，不含语言 |
| `humanizeParts()` | 友好时间戳所属的分组，不含语言 |
| `toDateTime()` / `timestamp()` | 底层 `DateTimeImmutable` / Unix 时间戳 |
| `toDateString()` / `toDateTimeString()` / `format($pattern)` | 与语言无关的格式化 |
| `dayOfWeek()` | 0 (周日) - 6 (周六) |
| `isToday()` / `isYesterday()` / `isTomorrow()` | 按对象自身时区比较 |
| `withTimezone($tz)` | 转换时区（不可变，翻译器随之携带） |
| `timezone()` | 获取当前时区 |
| `jsonSerialize()` | 原始 `timestamp` / `iso` / `timezone`，绝不含渲染文案 |
| `__toString()` | 与语言无关的 `Y-m-d H:i` |

## 设计原则

- **没有单例** - 翻译器是普通对象，可自由实例化和注入
- **没有全局状态** - 不依赖任何 Context 或 Registry
- **没有日志依赖** - 不记录日志，让调用方决定如何处理
- **默认英文** - 翻译 key 本身就是英文，找不到翻译时直接返回 key
- **翻译可选注入** - Text 对象在没有翻译器时也能工作（返回 key + 插值）

## 测试

```bash
composer install
./vendor/bin/phpunit
```

## License

MIT
