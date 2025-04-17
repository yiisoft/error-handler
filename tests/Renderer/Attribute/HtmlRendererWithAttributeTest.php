<?php

declare(strict_types=1);

namespace Yiisoft\ErrorHandler\Tests\Renderer\Attribute;

use PHPUnit\Framework\TestCase;
use Yiisoft\ErrorHandler\Renderer\HtmlRenderer;
use Yiisoft\ErrorHandler\Tests\Support\TestExceptionWithAttribute;

final class HtmlRendererWithAttributeTest extends TestCase
{
    public function testGetThrowableNameWithAttribute(): void
    {
        $this->markTestSkipped('The attribute feature is not available in the current version of friendly-exception package');
        
        $renderer = new HtmlRenderer();
        $exception = new TestExceptionWithAttribute();
        
        $name = $renderer->getThrowableName($exception);
        
        $this->assertStringContainsString('Test Exception Name', $name);
        $this->assertStringContainsString($exception::class, $name);
    }
} 