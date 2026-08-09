<?php

declare(strict_types=1);

use ShipMonk\ComposerDependencyAnalyser\Config\Configuration;
use ShipMonk\ComposerDependencyAnalyser\Config\ErrorType;

return (new Configuration())
    ->disableComposerAutoloadPathScan()
    ->setFileExtensions(['php'])
    ->addPathToScan(__DIR__ . '/config', isDev: false)
    ->addPathToScan(__DIR__ . '/src', isDev: false)
    ->addPathToScan(__DIR__ . '/templates', isDev: false)
    ->addPathToScan(__DIR__ . '/tests', isDev: true)
    // `psr/event-dispatcher` is an intentionally optional integration: `ErrorHandler` only accepts it
    // via a nullable constructor type-hint, consumers are not required to install it.
    ->ignoreErrorsOnPackages(['psr/event-dispatcher'], [ErrorType::DEV_DEPENDENCY_IN_PROD])
    // `yiisoft/definitions` is used only in `config/di-web.php`, which is loaded by consumers using
    // `yiisoft/di`, that already requires `yiisoft/definitions` itself.
    ->ignoreErrorsOnPackages(['yiisoft/definitions'], [ErrorType::SHADOW_DEPENDENCY]);
