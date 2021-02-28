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
- Debug mode displays details, stacktrace, has dark and light themes and handy buttons to search for error without typing.  
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

Creating an error handler:

```php
use Yiisoft\ErrorHandler\ErrorHandler;
use Yiisoft\ErrorHandler\Renderer\HtmlRenderer;

/**
 * @var \Psr\Log\LoggerInterface $logger
 */

$errorHandler = new ErrorHandler($logger, new HtmlRenderer());
```

The error handler logs information about the error using any [PSR-3](https://www.php-fig.org/psr/psr-3/)
compatible logger. If for some reason you do not want to log error information,
specify an instance of the `\Psr\Log\NullLogger`.

By default, the error handler is set to production mode and displays no detailed information.
You can enable and disable debug mode as follows:

```php
// Enable debug mode:
$errorHandler->debug();

// Disable debug mode:
$errorHandler->debug(false);

// Or define the environment dynamically:
$errorHandler->debug($_ENV['debug'] ?? false);
```

The error handler handles out-of-memory errors. To achieve it, memory is pre-allocated so that if a problem occurs with
a lack of memory, the error handler can handle the error using this reserved memory. You can specify your own reserve
size using the `memoryReserveSize()` method. If you set this value to 0, no memory will be reserved.

```php
// Allocate 512KB. Defaults to 256KB.
$errorHandler->memoryReserveSize(524_288);
```

The `register()` method registers the PHP error and exception handlers.
To unregister these and restore the PHP error and exception handlers, use the `unregister()` method.

```php
$errorHandler->register();
// Errors are being handled.
$errorHandler->unregister();
// Errors are not handled.
```

### Rendering error data

The following renderers are available out of the box:

- `Yiisoft\ErrorHandler\Renderer\HeaderRenderer` - Renders error into HTTP headers. It is used for HEAD requests.
- `Yiisoft\ErrorHandler\Renderer\HtmlRenderer` - Renders error into HTML.
- `Yiisoft\ErrorHandler\Renderer\JsonRenderer` - Renders error into JSON.
- `Yiisoft\ErrorHandler\Renderer\PlainTextRenderer` - Renders error into plain text.
- `Yiisoft\ErrorHandler\Renderer\XmlRenderer` - Renders error into XML.

If the existing renderers are not enough, you can create your own. To do this, you must implement the
`Yiisoft\ErrorHandler\ThrowableRendererInterface` and specify it when creating an instance of the error handler.

```php
use Yiisoft\ErrorHandler\ErrorHandler;

/**
 * @var \Psr\Log\LoggerInterface $logger
 * @var \Yiisoft\ErrorHandler\ThrowableRendererInterface $renderer
 */

$errorHandler = new ErrorHandler($logger, $renderer);
```

For more information about creating your own renders and examples of rendering error data,
[see here](https://github.com/yiisoft/docs/blob/master/guide/en/runtime/handling-errors.md#rendering-error-data).

### Using middleware for catching unhandled errors

`Yiisoft\ErrorHandler\Middleware\ErrorCatcher` is a [PSR-15](https://www.php-fig.org/psr/psr-15/) middleware that
catches exceptions that appear during middleware stack execution and passes them to the handler.

```php
use Yiisoft\ErrorHandler\Middleware\ErrorCatcher;

/**
 * @var \Psr\Container\ContainerInterface $container
 * @var \Psr\Http\Message\ResponseFactoryInterface $responseFactory
 * @var \Psr\Http\Message\ServerRequestInterface $request
 * @var \Psr\Http\Server\RequestHandlerInterface $handler
 * @var \Yiisoft\ErrorHandler\ErrorHandler $errorHandler
 * @var \Yiisoft\ErrorHandler\ThrowableRendererInterface $renderer
 */

$errorCatcher = new ErrorCatcher($responseFactory, $errorHandler, $container);

// In any case, it will return an instance of the `Psr\Http\Message\ResponseInterface`.
// Either the expected response, or a response with error information.
$response = $errorCatcher->process($request, $handler);
```

The error catcher chooses how to render an exception based on accept HTTP header. If it is `text/html`
or any unknown content type, it will use the error or exception HTML template to display errors. For other
mime types, the error handler will choose different renderer that is registered within the error catcher.
By default, JSON, XML and plain text are supported. You can change this behavior as follows:

```php
// Returns a new instance without renderers by the specified content types.
$errorCatcher = $errorCatcher->withoutRenderers('application/xml', 'text/xml');

// Returns a new instance with the specified content type and renderer class.
$errorCatcher = $errorCatcher->withRenderer('my/format', new MyRenderer());

// Returns a new instance with the specified force content type to respond with regardless of request.
$errorCatcher = $errorCatcher->forceContentType('application/json');
```

### Using middleware for mapping certain exceptions to custom responses

`Yiisoft\ErrorHandler\Middleware\ExceptionResponder` is a [PSR-15](https://www.php-fig.org/psr/psr-15/)
middleware that maps certain exceptions to custom responses.

```php
use Yiisoft\ErrorHandler\Middleware\ExceptionResponder;

/**
 * @var \Psr\Http\Message\ResponseFactoryInterface $responseFactory
 * @var \Psr\Http\Message\ServerRequestInterface $request
 * @var \Psr\Http\Server\RequestHandlerInterface $handler
 * @var \Yiisoft\Injector\Injector $injector
 */
 
$exceptionMap = [
    // Status code with which the response will be created by the factory.
    MyNotFoundException::class => 404,
    // PHP callable that must return a `Psr\Http\Message\ResponseInterface`.
    MyHttpException::class => static fn () => new MyResponse(),
    // ...
];

$exceptionResponder = new ExceptionResponder($exceptionMap, $responseFactory, $injector);

// Returns the expected response, or the response associated with the thrown exception,
// or throws an exception if it does not present in the exception map.
$response = $exceptionResponder->process($request, $handler);
```

In the application middleware stack `Yiisoft\ErrorHandler\Middleware\ExceptionResponder` must be placed before
`Yiisoft\ErrorHandler\Middleware\ErrorCatcher`.

For use in the [Yii framework](http://www.yiiframework.com/),
see [Yii guide to handling errors](https://github.com/yiisoft/docs/blob/master/guide/en/runtime/handling-errors.md).

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
