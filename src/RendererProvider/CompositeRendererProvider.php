<?php

declare(strict_types=1);

namespace Yiisoft\ErrorHandler\RendererProvider;

use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\ErrorHandler\ThrowableRendererInterface;

final class CompositeRendererProvider implements RendererProviderInterface
{
    /**
     * @psalm-var list<RendererProviderInterface>
     */
    private readonly array $providers;

    /**
     * @no-named-arguments
     */
    public function __construct(RendererProviderInterface ...$providers)
    {
        $this->providers = $providers;
    }

    public function get(ServerRequestInterface $request): ?ThrowableRendererInterface
    {
        foreach ($this->providers as $provider) {
            $renderer = $provider->get($request);
            if ($renderer !== null) {
                return $renderer;
            }
        }

        return null;
    }
}
