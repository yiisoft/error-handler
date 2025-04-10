<?php

declare(strict_types=1);

namespace Yiisoft\ErrorHandler;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use Yiisoft\ErrorHandler\RendererProvider\CompositeRendererProvider;
use Yiisoft\ErrorHandler\RendererProvider\ContentTypeRendererProvider;
use Yiisoft\ErrorHandler\RendererProvider\HeadRendererProvider;
use Yiisoft\ErrorHandler\RendererProvider\RendererProviderInterface;
use Yiisoft\Http\Status;

final class ThrowableResponseFactory implements ThrowableResponseFactoryInterface
{
    private readonly RendererProviderInterface $rendererProvider;
    private readonly HeadersProvider $headersProvider;

    public function __construct(
        private readonly ErrorHandler $errorHandler,
        private readonly ResponseFactoryInterface $responseFactory,
        ContainerInterface $container,
        ?RendererProviderInterface $rendererProvider = null,
        ?HeadersProvider $headersProvider = null,
    ) {
        $this->rendererProvider = $rendererProvider ?? new CompositeRendererProvider(
            new HeadRendererProvider(),
            new ContentTypeRendererProvider(container: $container),
        );
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
