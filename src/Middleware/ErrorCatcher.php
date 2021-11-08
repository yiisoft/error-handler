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
use Yiisoft\ErrorHandler\Renderer\HeaderRenderer;
use Yiisoft\ErrorHandler\Renderer\HtmlRenderer;
use Yiisoft\ErrorHandler\Renderer\JsonRenderer;
use Yiisoft\ErrorHandler\Renderer\PlainTextRenderer;
use Yiisoft\ErrorHandler\Renderer\XmlRenderer;
use Yiisoft\ErrorHandler\ThrowableRendererInterface;
use Yiisoft\Http\Header;
use Yiisoft\Http\HeaderValueHelper;
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

    /**
     * Returns a new instance with the specified content type and renderer class.
     *
     * @param string $contentType The content type to add associated renderers for.
     * @param string $rendererClass The classname implementing the {@see ThrowableRendererInterface}.
     *
     * @return self
     */
    public function withRenderer(string $contentType, string $rendererClass): self
    {
        if (!is_subclass_of($rendererClass, ThrowableRendererInterface::class)) {
            throw new InvalidArgumentException(sprintf(
                'Class "%s" does not implement "%s".',
                $rendererClass,
                ThrowableRendererInterface::class,
            ));
        }

        $new = clone $this;
        $new->renderers[$this->normalizeContentType($contentType)] = $rendererClass;
        return $new;
    }

    /**
     * Returns a new instance without renderers by the specified content types.
     *
     * @param string[] $contentTypes The content types to remove associated renderers for.
     * If not specified, all renderers will be removed.
     *
     * @return self
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
     * @param string $contentType The content type to respond with regardless of request.
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
            return $this->generateErrorResponse($t, $request);
        }
    }

    /**
     * Generates a response with error information.
     *
     * @param Throwable $t
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    private function generateErrorResponse(Throwable $t, ServerRequestInterface $request): ResponseInterface
    {
        $contentType = $this->contentType ?? $this->getContentType($request);
        $renderer = $request->getMethod() === Method::HEAD ? new HeaderRenderer() : $this->getRenderer($contentType);

        $data = $this->errorHandler->handle($t, $renderer, $request);
        $response = $this->responseFactory->createResponse(Status::INTERNAL_SERVER_ERROR);

        return $data->addToResponse($response->withHeader(Header::CONTENT_TYPE, $contentType));
    }

    /**
     * Returns the renderer by the specified content type, or null if the renderer was not set.
     *
     * @param string $contentType The content type associated with the renderer.
     *
     * @return ThrowableRendererInterface|null
     */
    private function getRenderer(string $contentType): ?ThrowableRendererInterface
    {
        if (isset($this->renderers[$contentType])) {
            return $this->container->get($this->renderers[$contentType]);
        }

        return null;
    }

    /**
     * Returns the priority content type from the accept request header.
     *
     * @param ServerRequestInterface $request
     *
     * @return string The priority content type.
     */
    private function getContentType(ServerRequestInterface $request): string
    {
        try {
            foreach (HeaderValueHelper::getSortedAcceptTypes($request->getHeader(Header::ACCEPT)) as $header) {
                if (array_key_exists($header, $this->renderers)) {
                    return $header;
                }
            }
        } catch (InvalidArgumentException $e) {
            // The Accept header contains an invalid q factor.
        }

        return '*/*';
    }

    /**
     * Normalizes the content type.
     *
     * @param string $contentType The raw content type.
     *
     * @return string Normalized content type.
     */
    private function normalizeContentType(string $contentType): string
    {
        if (strpos($contentType, '/') === false) {
            throw new InvalidArgumentException('Invalid content type.');
        }

        return strtolower(trim($contentType));
    }
}
