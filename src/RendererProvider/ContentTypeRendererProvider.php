<?php

declare(strict_types=1);

namespace Yiisoft\ErrorHandler\RendererProvider;

use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\ErrorHandler\Renderer\HtmlRenderer;
use Yiisoft\ErrorHandler\Renderer\JsonRenderer;
use Yiisoft\ErrorHandler\Renderer\PlainTextRenderer;
use Yiisoft\ErrorHandler\Renderer\XmlRenderer;
use Yiisoft\ErrorHandler\ThrowableRendererInterface;
use Yiisoft\Http\Header;
use Yiisoft\Http\HeaderValueHelper;

use function array_key_exists;

final class ContentTypeRendererProvider implements RendererProviderInterface
{
    /**
     * @psalm-var array<string, class-string<ThrowableRendererInterface>>
     */
    private readonly array $renderers;

    /**
     * @psalm-param array<string, class-string<ThrowableRendererInterface>>|null $renderers
     */
    public function __construct(
        private readonly ContainerInterface $container,
        ?array $renderers = null,
    ) {
        $this->renderers = $renderers ?? [
            'application/json' => JsonRenderer::class,
            'application/xml' => XmlRenderer::class,
            'text/xml' => XmlRenderer::class,
            'text/plain' => PlainTextRenderer::class,
            'text/html' => HtmlRenderer::class,
            '*/*' => HtmlRenderer::class,
        ];
    }

    public function get(ServerRequestInterface $request): ?ThrowableRendererInterface
    {
        $rendererClass = $this->selectRendererClass($request);
        if ($rendererClass === null) {
            return null;
        }

        /** @var ThrowableRendererInterface */
        return $this->container->get($rendererClass);
    }

    /**
     * @psalm-return class-string<ThrowableRendererInterface>|null
     */
    private function selectRendererClass(ServerRequestInterface $request): ?string
    {
        $acceptHeader = $request->getHeader(Header::ACCEPT);

        try {
            $contentTypes = HeaderValueHelper::getSortedAcceptTypes($acceptHeader);
        } catch (InvalidArgumentException) {
            // The "Accept" header contains an invalid "q" factor.
            return null;
        }

        foreach ($contentTypes as $contentType) {
            if (array_key_exists($contentType, $this->renderers)) {
                return $this->renderers[$contentType];
            }
        }

        return null;
    }
}
