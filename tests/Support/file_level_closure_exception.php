<?php

declare(strict_types=1);

$closure = function (): void {
    throw new RuntimeException('test');
};

try {
    $closure();
} catch (RuntimeException $e) {
    return $e;
}

throw new RuntimeException('Unreachable.');
