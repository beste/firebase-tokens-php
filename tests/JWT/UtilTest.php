<?php

declare(strict_types=1);

namespace Kreait\Firebase\JWT\Tests;

use Kreait\Firebase\JWT\Util;
use PHPUnit\Framework\Attributes\BackupGlobals;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[BackupGlobals(true)]
final class UtilTest extends TestCase
{
    private const string VARIABLE = 'FIREBASE_TOKENS_TEST_ENV';

    protected function setUp(): void
    {
        unset($_SERVER[self::VARIABLE], $_ENV[self::VARIABLE]);
        putenv(self::VARIABLE);
    }

    protected function tearDown(): void
    {
        putenv(self::VARIABLE);
    }

    #[DataProvider('environmentValues')]
    public function testItNormalizesEnvironmentValues(mixed $value, ?string $expected): void
    {
        if ($value !== null) {
            $_SERVER[self::VARIABLE] = $value;
        }

        $this->assertSame($expected, Util::getenv(self::VARIABLE));
    }

    /**
     * @return \Iterator<string, array{mixed, ?string}>
     */
    public static function environmentValues(): \Iterator
    {
        yield 'missing' => [null, null];
        yield 'empty' => ['', null];
        yield 'spaces' => ['   ', null];
        yield 'other whitespace' => ["\t\n\r", null];
        yield 'string' => ['value', 'value'];
        yield 'string with whitespace' => [' value ', 'value'];
        yield 'zero' => ['0', '0'];
        yield 'boolean' => [false, null];
        yield 'integer' => [0, null];
        yield 'array' => [[], null];
    }

    public function testItPreservesEnvironmentSourcePrecedence(): void
    {
        putenv(self::VARIABLE.'= native ');
        $this->assertSame('native', Util::getenv(self::VARIABLE));

        $_ENV[self::VARIABLE] = ' env ';
        $this->assertSame('env', Util::getenv(self::VARIABLE));

        $_SERVER[self::VARIABLE] = ' server ';
        $this->assertSame('server', Util::getenv(self::VARIABLE));

        $_SERVER[self::VARIABLE] = '   ';
        $this->assertNull(Util::getenv(self::VARIABLE));

        $_SERVER[self::VARIABLE] = false;
        $this->assertNull(Util::getenv(self::VARIABLE));

        $_SERVER[self::VARIABLE] = null;
        $this->assertSame('env', Util::getenv(self::VARIABLE));
    }
}
