<?php

declare(strict_types=1);

namespace Yiisoft\ErrorHandler\Tests;

use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class MockRequestHandler implements RequestHandlerInterface
{
    public ServerRequestInterface $processedRequest;
    private int $responseStatus;
    private ?\Throwable $handleException;

    public function __construct(int $responseStatus = 200)
    {
        $this->responseStatus = $responseStatus;
    }

    public function setHandleException(?\Throwable $throwable): self
    {
        $this->handleException = $throwable;
        return $this;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->handleException !== null) {
            throw $this->handleException;
        }
        $this->processedRequest = $request;
        return new Response($this->responseStatus);
    }
}
