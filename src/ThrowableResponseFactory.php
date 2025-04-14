<?php

declare(strict_types=1);

namespace Yiisoft\ErrorHandler;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use Yiisoft\ErrorHandler\RendererProvider\RendererProviderInterface;
use Yiisoft\Http\Status;

/**
 * `ThrowableResponseFactory` produces a response with rendered `Throwable` object.
 */
final class ThrowableResponseFactory implements ThrowableResponseFactoryInterface
{
    private readonly HeadersProvider $headersProvider;

    public function __construct(
        private readonly ErrorHandler $errorHandler,
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly RendererProviderInterface $rendererProvider,
        ?HeadersProvider $headersProvider = null,
    ) {
        $this->headersProvider = $headersProvider ?? new HeadersProvider();
    }

    public function create(Throwable $throwable, ServerRequestInterface $request): ResponseInterface
    {
        $renderer = $this->rendererProvider->get($request);

        $response = $this->responseFactory->createResponse(Status::INTERNAL_SERVER_ERROR);
        foreach ($this->headersProvider->getAll() as $name => $value) {
            $response = $response->withHeader($name, $value);
        }

        return $this->errorHandler
            ->handle($throwable, $renderer, $request)
            ->addToResponse($response);
    }
}
