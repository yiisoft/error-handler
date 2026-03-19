<?php

declare(strict_types=1);

namespace Yiisoft\ErrorHandler\Tests\Support;

use RuntimeException;

/**
 * Safe markdown link with parentheses [Wiki](https://en.wikipedia.org/wiki/Function_(mathematics)).
 *
 * Inline tag with parentheses {@link https://en.wikipedia.org/wiki/Function_(mathematics) Inline wiki}.
 */
final class TestParenthesizedMarkdownDocBlockException extends RuntimeException {}
