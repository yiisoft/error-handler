<?php

declare(strict_types=1);

namespace Yiisoft\ErrorHandler\Tests\Support;

use RuntimeException;

/**
 * This description contains multiple untrusted payloads that must not execute.
 * {@link javascript:alert(1) Click me} and {@link https://www.yiiframework.com Safe link}.
 * [Click me](javascript:alert(document.domain))
 *
 * [Encoded payload](JaVaScRiPt:alert(1))
 *
 * [Data URL](data:text/html,<script>alert(1)</script>)
 *
 * ![Image payload](javascript:alert('img'))
 *
 * <img src="x" onerror="alert('html-img')">
 *
 * <a href="javascript:alert('html-link')">Raw HTML link</a>
 *
 * <svg onload="alert('svg')"></svg>
 */
final class TestUnsafeMarkdownDocBlockException extends RuntimeException {}
