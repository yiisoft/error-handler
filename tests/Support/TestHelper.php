<?php

declare(strict_types=1);

namespace Yiisoft\ErrorHandler\Tests\Support;

use HttpSoft\Message\ServerRequest;
use HttpSoft\Message\ServerRequestFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

use function dirname;

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

    /**
     * Generates a trace array in the format identical to `debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)`.
     *
     * @param bool[] $isVendor List of boolean values where `true` means a vendor file and `false` means an application file.
     *
     * @return array
     */
    public static function generateTrace(array $isVendor): array
    {
        $rootPath = dirname(__DIR__, 2);
        $vendorFile = $rootPath . '/vendor/autoload.php';
        $appFile = $rootPath . '/src/ErrorHandler.php';

        $trace = [];

        foreach ($isVendor as $index => $vendor) {
            $trace[] = [
                'file' => $vendor ? $vendorFile : $appFile,
                'line' => $index + 1,
                'function' => 'testFunction' . $index,
                'class' => 'TestClass',
                'type' => '->',
            ];
        }

        return $trace;
    }
}
