<?php

declare(strict_types=1);

namespace Yiisoft\ErrorHandler\Tests\Support;

use HttpSoft\Message\ServerRequest;
use HttpSoft\Message\ServerRequestFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

final class TestHelper
{
    public static function createRequest(
        string $method = 'GET',
        string|UriInterface $uri = 'https://example.com',
        array $serverParams = [],
        array $headers = [],
    ): ServerRequest {
        $request = (new ServerRequestFactory())->createServerRequest($method, $uri, $serverParams);
        foreach ($headers as $name => $value) {
            $request = $request->withAddedHeader($name, $value);
        }
        return $request;
    }

    public static function getResponseContent(ResponseInterface $response): string
    {
        $body = $response->getBody();
        $body->rewind();
        return $body->getContents();
    }
}
