# Yii Error Handler Change Log

## 4.3.2 under development

- Bug #160: Fix skipping the first trace item when handling a PHP error (@vjik)

## 4.3.1 December 18, 2025

- Enh #159: Add PHP 8.5 support (@vjik)

## 4.3.0 September 06, 2025

- New #156: Pass request to callable value in `ExceptionResponder` middleware (@vjik)

## 4.2.0 August 19, 2025

- New #151: Add `$traceLink` parameter to `HtmlRenderer` to allow linking to trace files (@vjik)
- New #153: Introduce `UserException` attribute to mark user exceptions (@vjik)
- Enh #150: Cleanup templates, remove legacy code (@vjik)

## 4.1.0 April 18, 2025

- New #145: Add `Yiisoft\ErrorHandler\ThrowableResponseFactory` that provides a response for `Throwable` object with
  renderer provider usage (@vjik)
- Chg #145: Mark `Yiisoft\ErrorHandler\Factory\ThrowableResponseFactory` as deprecated (@vjik)
- Enh #145: Set content type header in renderers (@vjik)
- Bug #142: Fix dark mode argument display issues (@pamparam83)

## 4.0.0 February 05, 2025

- Chg #137: Add separate parameters for each of `HtmlRenderer` settings in constructor. Mark `$settings` parameter as
  deprecated (@vjik)
- Chg #139: Change PHP constraint in `composer.json` to `~8.1.0 || ~8.2.0 || ~8.3.0 || ~8.4.0` (@vjik)
- Enh #125: Add error code & show function arguments (@xepozz)
- Enh #130: Pass exception message instead of rendered exception to logger in `ErrorHandler` (@olegbaturin)
- Enh #133: Extract response generator from `ErrorCatcher` middleware into separate `ThrowableResponseFactory`
  class (@olegbaturin)
- Enh #138, #139: Raise the minimum PHP version to 8.1 and minor refactoring (@vjik)
- Bug #139: Explicitly mark nullable parameters (@vjik)

## 3.3.0 July 11, 2024

- Enh #112: Add copy cURL button, sort request headers, fix UI (@xepozz)
- Enh #113: Simplify error log (@xepozz)
- Enh #114: Show full argument by click (@xepozz)
- Enh #116: Remove @anonymous postfix (@xepozz)
- Enh #117, #120: Show arguments table by click (@xepozz, @vjik)
- Bug #114: Stop `click` event on text selection (@xepozz)
- Bug #122: Do `exit(1)` after all shutdown functions, even postponed ones (@samdark)

## 3.2.1 March 07, 2024

- Enh #102: Add support for `psr/http-message` of `^2.0` version (@vjik)

## 3.2.0 January 30, 2024

- New #98: Add ability to execute `getBody()` on response when `ExceptionResponder` middleware is processing (@vjik)
- Enh #96: Trace PHP errors (@xepozz, @vjik)

## 3.1.0 January 07, 2024

- New #87: Add `CompositeException` to be able to render multiple exceptions (@xepozz)
- Chg #75: Dispatch `ApplicationError` in `ErrorCatcher` (@xepozz)
- Enh #82: Add `HeadersProvider` (@xepozz)
- Enh #86: Add color scheme definition based on system settings (@dood-)
- Bug #87: Fix a bug with try/finally from #75 (@xepozz)

## 3.0.0 February 14, 2023

- Chg #64: Raise PHP version to `^8.0` (@vjik, @xepozz)
- Chg #72: Adapt configuration group names to Yii conventions (@vjik)
- Enh #65: Explicitly add transitive dependencies `ext-mbstring`, `psr/http-factory` and
  `psr/http-server-handler` (@vjik)

## 2.1.1 January 26, 2023

- Bug #70: Prevent duplication of throwable rendering (@vjik)

## 2.1.0 June 15, 2022

- Enh #54: Add shutdown event, fix cwd (@rustamwin)
- Enh #55: Defer exit on terminate (@rustamwin)
- Enh #57: Add markdown support for friendly exception solutions (@vjik)
- Enh #58: Add support for `2.0`, `3.0` versions of `psr/log` (@rustamwin)

## 2.0.2 February 04, 2022

- Bug #50: Fix JSON rendering on JSON recursion exception (@thenotsoft)

## 2.0.1 January 26, 2022

- Bug #49: Fix JSON rendering of non-UTF-8 encoded string (@devanych)

## 2.0.0 November 09, 2021

- Chg #48: Transfer `HeaderHelper` to `yiisoft/http` package (@devanych)
- Enh #45: Improve appearance of solution from friendly exceptions (@vjik)

## 1.0.0 May 13, 2021

Initial release.
