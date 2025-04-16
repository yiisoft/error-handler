<?php

declare(strict_types=1);

namespace Yiisoft\ErrorHandler\RendererProvider;

use Closure;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\ErrorHandler\ThrowableRendererInterface;

use function is_string;

/**
 * Provides a renderer based on a closure that returns a `ThrowableRendererInterface` or its class name.
 *
 * @psalm-type TClosure = Closure(ServerRequestInterface $request): (class-string<ThrowableRendererInterface>|ThrowableRendererInterface|null)
 */
final class ClosureRendererProvider implements RendererProviderInterface
{
    /**
     * @psalm-param TClosure $closure
     */
    public function __construct(
        private readonly Closure $closure,
        private readonly ContainerInterface $container,
    ) {
    }

    public function get(ServerRequestInterface $request): ?ThrowableRendererInterface
    {
        $result = ($this->closure)($request);

        if (is_string($result)) {
            /** @var ThrowableRendererInterface */
            return $this->container->get($result);
        }

        return $result;
    }
}
