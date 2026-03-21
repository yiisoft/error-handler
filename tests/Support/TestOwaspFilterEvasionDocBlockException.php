<?php

declare(strict_types=1);

namespace Yiisoft\ErrorHandler\Tests\Support;

use RuntimeException;

/**
 * OWASP-inspired payloads that must remain inert in throwable descriptions.
 *
 * <a href="&#0000106&#0000097&#0000118&#0000097&#0000115&#0000099&#0000114&#0000105&#0000112&#0000116&#0000058&#0000097&#0000108&#0000101&#0000114&#0000116&#0000040&#0000039&#0000088&#0000083&#0000083&#0000039&#0000041">Decimal entity payload</a>
 *
 * <a href="jav&#x09;ascript:alert('XSS');">Encoded tab payload</a>
 *
 * <img src= onmouseover="alert('xss')">
 *
 * <img onmouseover="alert('xss')">
 *
 * <img dynsrc="javascript:alert('XSS')">
 *
 * <img lowsrc="javascript:alert('XSS')">
 */
final class TestOwaspFilterEvasionDocBlockException extends RuntimeException {}
