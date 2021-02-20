<p align="center">
    <a href="https://github.com/yiisoft" target="_blank">
        <img src="https://github.com/yiisoft.png" height="100px">
    </a>
    <h1 align="center">Yii error handler</h1>
    <br>
</p>

[![Latest Stable Version](https://poser.pugx.org/yiisoft/error-handler/v/stable.png)](https://packagist.org/packages/yiisoft/error-handler)
[![Total Downloads](https://poser.pugx.org/yiisoft/error-handler/downloads.png)](https://packagist.org/packages/yiisoft/error-handler)
[![Build status](https://github.com/yiisoft/error-handler/workflows/build/badge.svg)](https://github.com/yiisoft/error-handler/actions?query=workflow%3Abuild)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/yiisoft/error-handler/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/yiisoft/error-handler/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/yiisoft/error-handler/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/yiisoft/error-handler/?branch=master)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fyiisoft%2Ferror-handler%2Fmaster)](https://dashboard.stryker-mutator.io/reports/github.com/yiisoft/error-handler/master)
[![static analysis](https://github.com/yiisoft/error-handler/workflows/static%20analysis/badge.svg)](https://github.com/yiisoft/error-handler/actions?query=workflow%3A%22static+analysis%22)

The package provides advanced error handling. The features are:

- PSR-15 middleware for catching unhandled errors.
- PSR-15 middleware for mapping certain exceptions to custom responses.
- Production and debug modes.
- Takes PHP settings into account.
- Handles out of memory errors, fatals, warnings, notices and exceptions. 
- Can use any PSR-3 compatible logger for error logging.
- Detects response format based on mime type of the request.
- Supports responding with HTML, plain text, JSON, XML and headers out of the box.
- Has ability to implement your own error rendering for additional types.

## Requirements

- PHP 7.4 or higher.

## Installation

The package could be installed with composer:

```
composer require yiisoft/error-handler --prefer-dist
```

## General usage

## Testing

### Unit testing

The package is tested with [PHPUnit](https://phpunit.de/). To run tests:

```shell
./vendor/bin/phpunit
```

### Mutation testing

The package tests are checked with [Infection](https://infection.github.io/) mutation framework. To run it:

```shell
./vendor/bin/infection
```

### Static analysis

The code is statically analyzed with [Psalm](https://psalm.dev/). To run static analysis:

```shell
./vendor/bin/psalm
```

## License

The Yii error handler is free software. It is released under the terms of the BSD License.
Please see [`LICENSE`](./LICENSE.md) for more information.

Maintained by [Yii Software](https://www.yiiframework.com/).
