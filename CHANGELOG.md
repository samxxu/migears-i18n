# Changelog

All notable changes to `migears/i18n` are documented here. This project follows [Semantic Versioning](https://semver.org/).

## [2.0.0] — Unreleased

### Added

- `LocalizedDate` — a date bound to a viewer's timezone and translator. It reports neutral facts (`toDateString()`, `dayOfWeek()`, `isToday()`, `relativeParts()`, `humanizeParts()`) and renders text through the translator (`relative()`, `humanize()`), so the class carries no language of its own. `jsonSerialize()` returns raw `timestamp` / `iso` / `timezone` and `__toString()` returns the locale-neutral `Y-m-d H:i`, so a payload never carries this server's language.
- Moved here from `migears/utils`, where it was `MiGears\Utils\Date`. Presenting a date to a person in their own timezone and language is a localization concern, and the class no longer produces prose on its own.

### Notes

- The package still ships no locale data. `LocalizedDate` looks up a documented `date.*` key set through whatever `TranslatorInterface` you inject, so the message table stays with the application.
- `relative()` and `humanize()` throw a `LogicException` when no translator was supplied. `relativeParts()` and `humanizeParts()` work without one.
- Numeric date parts (`%year%`, `%month%`, `%day%`) are passed unpadded so each message table decides its own padding — `3月15日` rather than `03月15日`.
- The month count from `relativeParts()` is floored at 1: a gap of roughly 30 days reaches the month branch while PHP's calendar diff can still report 0 months, which used to render as `0 months ago`.
