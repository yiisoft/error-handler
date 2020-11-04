<?php

declare(strict_types=1);

namespace Yiisoft\ErrorHandler;

use Composer\InstalledVersions;

final class Info
{
    public static function frameworkVersion(): string
    {
        try {
            return InstalledVersions::getVersion('yiisoft/yii-web') ?? 'unknown version';
        } catch (\OutOfBoundsException $e) {
            return 'unknown version';
        }
    }

    public static function frameworkPath(): string
    {
        return dirname(__DIR__, 3) . '/yii-web';
    }
}
