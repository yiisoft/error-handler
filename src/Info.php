<?php

declare(strict_types=1);

namespace Yiisoft\ErrorHandler;

use Composer\InstalledVersions;
use OutOfBoundsException;

final class Info
{
    public static function frameworkVersion(): string
    {
        // For composer 1
        if (!class_exists(InstalledVersions::class)) {
            return 'unknown version';
        }

        try {
            return InstalledVersions::getVersion('yiisoft/yii-web') ?? 'unknown version';
        } catch (OutOfBoundsException $e) {
            return 'unknown version';
        }
    }

    public static function frameworkPath(): string
    {
        return dirname(__DIR__, 3) . '/yii-web';
    }
}
