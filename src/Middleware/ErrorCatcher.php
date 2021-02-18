<?php

declare(strict_types=1);

namespace Yiisoft\ErrorHandler\Middleware;

use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;
use Yiisoft\ErrorHandler\ErrorHandler;
use Yiisoft\ErrorHandler\HeaderHelper;
use Yiisoft\ErrorHandler\Renderer\HeaderRenderer;
use Yiisoft\ErrorHandler\Renderer\HtmlRenderer;
use Yiisoft\ErrorHandler\Renderer\JsonRenderer;
use Yiisoft\ErrorHandler\Renderer\PlainTextRenderer;
use Yiisoft\ErrorHandler\Renderer\XmlRenderer;
use Yiisoft\ErrorHandler\ThrowableRendererInterface;
use Yiisoft\Http\Header;
use Yiisoft\Http\Method;
use Yiisoft\Http\Status;

use function array_key_exists;
use function count;
use function is_subclass_of;
use function sprintf;
use function strpos;
use function strtolower;
use function trim;

/**
 * ErrorCatcher catches all throwables from the next middlewares and renders it
 * according to the content type passed by the client.
 */
final class ErrorCatcher implements MiddlewareInterface
{
    private array $renderers = [
        'application/json' => JsonRenderer::class,
        'application/xml' => XmlRenderer::class,
        'text/xml' => XmlRenderer::class,
        'text/plain' => PlainTextRenderer::class,
        'text/html' => HtmlRenderer::class,
        '*/*' => HtmlRenderer::class,
    ];

    private ResponseFactoryInterface $responseFactory;
    private ErrorHandler $errorHandler;
    private ContainerInterface $container;
    private ?string $contentType = null;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        ErrorHandler $errorHandler,
        ContainerInterface $container
    ) {
        $this->responseFactory = $responseFactory;
        $this->errorHandler = $errorHandler;
        $this->container = $container;
    }

    public function withRenderer(string $contentType, string $rendererClass): self
    {
        $this->validateRenderer($rendererClass);

        $new = clone $this;
        $new->renderers[$this->normalizeContentType($contentType)] = $rendererClass;
        return $new;
    }

    /**
     * @param string[] $contentTypes MIME types to remove associated renderers for.
     * If not specified, all renderers will be removed.
     */
    public function withoutRenderers(string ...$contentTypes): self
    {
        $new = clone $this;

        if (count($contentTypes) === 0) {
            $new->renderers = [];
            return $new;
        }

        foreach ($contentTypes as $contentType) {
            unset($new->renderers[$this->normalizeContentType($contentType)]);
        }

        return $new;
    }

    /**
     * Force content type to respond with regardless of request.
     *
     * @param string $contentType
     *
     * @return self
     */
    public function forceContentType(string $contentType): self
    {
        $contentType = $this->normalizeContentType($contentType);

        if (!isset($this->renderers[$contentType])) {
            throw new InvalidArgumentException(sprintf('The renderer for %s is not set.', $contentType));
        }

        $new = clone $this;
        $new->contentType = $contentType;
        return $new;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (Throwable $t) {
            return $this->handleException($t, $request);
        }
    }

    private function handleException(Throwable $t, ServerRequestInterface $request): ResponseInterface
    {
        $contentType = $this->contentType ?? $this->getContentType($request);
        $renderer = $request->getMethod() === Method::HEAD ? new HeaderRenderer() : $this->getRenderer($contentType);

        $data = $this->errorHandler->handleCaughtThrowable($t, $renderer, $request);
        $response = $this->responseFactory->createResponse(Status::INTERNAL_SERVER_ERROR);

        return $data->addToResponse($response->withHeader(Header::CONTENT_TYPE, $contentType));
    }

    private function getRenderer(string $contentType): ?ThrowableRendererInterface
    {
        if (isset($this->renderers[$contentType])) {
            return $this->container->get($this->renderers[$contentType]);
        }

        return null;
    }

    private function getContentType(ServerRequestInterface $request): string
    {
        try {
            foreach (HeaderHelper::getSortedAcceptTypes($request->getHeader(Header::ACCEPT)) as $header) {
                if (array_key_exists($header, $this->renderers)) {
                    return $header;
                }
            }
        } catch (InvalidArgumentException $e) {
            // The Accept header contains an invalid q factor
        }

        return '*/*';
    }

    private function normalizeContentType(string $contentType): string
    {
        if (strpos($contentType, '/') === false) {
            throw new InvalidArgumentException('Invalid content type.');
        }

        return strtolower(trim($contentType));
    }

    private function validateRenderer(string $rendererClass): void
    {
        if (trim($rendererClass) === '') {
            throw new InvalidArgumentException('The renderer class cannot be an empty string.');
        }

        if (!is_subclass_of($rendererClass, ThrowableRendererInterface::class)) {
            throw new InvalidArgumentException(sprintf(
                'Class "%s" does not implement "%s".',
                $rendererClass,
                ThrowableRendererInterface::class,
            ));
        }
    }
}
