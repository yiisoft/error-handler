<?php

declare(strict_types=1);

namespace Yiisoft\ErrorHandler\Middleware;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;
use Yiisoft\Injector\Injector;

use function is_int;
use function is_callable;

/**
 * ExceptionResponder maps certain exceptions to custom responses.
 */
final class ExceptionResponder implements MiddlewareInterface
{
    /**
     * @var callable[]|int[] A callable that must return a ResponseInterface or response status code.
     */
    private array $exceptionMap;
    private ResponseFactoryInterface $responseFactory;
    private Injector $injector;

    /**
     * @param callable[]|int[] $exceptionMap A must that should return a ResponseInterface or response status code.
     * @param ResponseFactoryInterface $responseFactory
     * @param Injector $injector
     */
    public function __construct(array $exceptionMap, ResponseFactoryInterface $responseFactory, Injector $injector)
    {
        $this->exceptionMap = $exceptionMap;
        $this->responseFactory = $responseFactory;
        $this->injector = $injector;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (Throwable $t) {
            foreach ($this->exceptionMap as $exceptionType => $responseHandler) {
                if ($t instanceof $exceptionType) {
                    if (is_int($responseHandler)) {
                        return $this->responseFactory->createResponse($responseHandler);
                    }

                    if (is_callable($responseHandler)) {
                        return $this->injector->invoke($responseHandler);
                    }
                }
            }
            throw $t;
        }
    }
}
