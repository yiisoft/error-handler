<?php

declare(strict_types=1);

namespace Yiisoft\ErrorHandler\Exception;

use Exception;
use ReflectionProperty;
use Yiisoft\FriendlyException\FriendlyExceptionInterface;

use function array_slice;
use function in_array;
use function function_exists;

/**
 * `ErrorException` represents a PHP error.
 */
class ErrorException extends \ErrorException implements FriendlyExceptionInterface
{
    private const ERROR_NAMES = [
        E_ERROR => 'PHP Fatal Error',
        E_WARNING => 'PHP Warning',
        E_PARSE => 'PHP Parse Error',
        E_NOTICE => 'PHP Notice',
        E_CORE_ERROR => 'PHP Core Error',
        E_CORE_WARNING => 'PHP Core Warning',
        E_COMPILE_ERROR => 'PHP Compile Error',
        E_COMPILE_WARNING => 'PHP Compile Warning',
        E_USER_ERROR => 'PHP User Error',
        E_USER_WARNING => 'PHP User Warning',
        E_USER_NOTICE => 'PHP User Notice',
        E_STRICT => 'PHP Strict Warning',
        E_RECOVERABLE_ERROR => 'PHP Recoverable Error',
        E_DEPRECATED => 'PHP Deprecated Warning',
        E_USER_DEPRECATED => 'PHP User Deprecated Warning',
    ];

    public function __construct(
        string $message = '',
        int $code = 0,
        int $severity = 1,
        string $filename = __FILE__,
        int $line = __LINE__,
        Exception $previous = null
    ) {
        parent::__construct($message, $code, $severity, $filename, $line, $previous);
        $this->addXDebugTraceToFatalIfAvailable();
    }

    /**
     * Returns if error is one of fatal type.
     *
     * @param array $error error got from error_get_last()
     *
     * @return bool If error is one of fatal type.
     */
    public static function isFatalError(array $error): bool
    {
        return isset($error['type']) && in_array(
            $error['type'],
            [E_ERROR, E_PARSE, E_CORE_ERROR, E_CORE_WARNING, E_COMPILE_ERROR, E_COMPILE_WARNING],
            true,
        );
    }

    /**
     * @return string The user-friendly name of this exception.
     */
    public function getName(): string
    {
        return self::ERROR_NAMES[$this->getCode()] ?? 'Error';
    }

    public function getSolution(): ?string
    {
        return null;
    }

    /**
     * Fatal errors normally do not provide any trace making it harder to debug. In case XDebug is installed, we
     * can get a trace using `xdebug_get_function_stack()`.
     */
    private function addXDebugTraceToFatalIfAvailable(): void
    {
        if ($this->isXdebugStackAvailable()) {
            /**
             * XDebug trace can't be modified and used directly with PHP 7
             * @see https://github.com/yiisoft/yii2/pull/11723
             * @psalm-var array<int,array>
             */
            $xDebugTrace = array_slice(array_reverse(xdebug_get_function_stack()), 1, -1);
            $trace = [];

            foreach ($xDebugTrace as $frame) {
                if (!isset($frame['function'])) {
                    $frame['function'] = 'unknown';
                }

                // XDebug < 2.1.1: http://bugs.xdebug.org/view.php?id=695
                if (!isset($frame['type']) || $frame['type'] === 'static') {
                    $frame['type'] = '::';
                } elseif ($frame['type'] === 'dynamic') {
                    $frame['type'] = '->';
                }

                // XDebug has a different key name
                if (isset($frame['params']) && !isset($frame['args'])) {
                    /** @var mixed */
                    $frame['args'] = $frame['params'];
                }
                $trace[] = $frame;
            }

            $ref = new ReflectionProperty('Exception', 'trace');
            $ref->setAccessible(true);
            $ref->setValue($this, $trace);
        }
    }

    /**
     * Ensures that Xdebug stack trace is available based on Xdebug version.
     * Idea taken from developer bishopb at https://github.com/rollbar/rollbar-php
     */
    private function isXdebugStackAvailable(): bool
    {
        if (!function_exists('\xdebug_get_function_stack')) {
            return false;
        }

        // check for Xdebug being installed to ensure origin of xdebug_get_function_stack()
        $version = phpversion('xdebug');

        if ($version === false) {
            return false;
        }

        // Xdebug 2 and prior
        if (version_compare($version, '3.0.0', '<')) {
            return true;
        }

        // Xdebug 3 and later, proper mode is required
        return str_contains(ini_get('xdebug.mode'), 'develop');
    }
}
