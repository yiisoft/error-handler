<?php

declare(strict_types=1);

namespace Yiisoft\ErrorHandler\RendererProvider;

use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\ErrorHandler\Renderer\HeaderRenderer;
use Yiisoft\ErrorHandler\ThrowableRendererInterface;
use Yiisoft\Http\Header;
use Yiisoft\Http\HeaderValueHelper;
use Yiisoft\Http\Method;

final class HeadRendererProvider implements RendererProviderInterface
{
    public function get(ServerRequestInterface $request): ?ThrowableRendererInterface
    {
        if ($request->getMethod() === Method::HEAD) {
            return new HeaderRenderer(
                $this->getAcceptContentType($request),
            );
        }

        return null;
    }

    private function getAcceptContentType(ServerRequestInterface $request): ?string
    {
        $acceptHeader = $request->getHeader(Header::ACCEPT);

        try {
            $contentTypes = HeaderValueHelper::getSortedAcceptTypes($acceptHeader);
        } catch (InvalidArgumentException) {
            // The "Accept" header contains an invalid "q" factor.
            return null;
        }

        return empty($contentTypes) ? null : reset($contentTypes);
    }
}
