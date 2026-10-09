<?php

declare(strict_types=1);

namespace Kreait\Firebase\JWT\Tests;

use Kreait\Firebase\JWT\Action\CreateCustomToken;
use Kreait\Firebase\JWT\Action\CreateCustomToken\Handler;
use Kreait\Firebase\JWT\Contract\Token;
use Kreait\Firebase\JWT\CustomTokenGenerator;
use Kreait\Firebase\JWT\SecureToken;
use Kreait\Firebase\JWT\Value\Duration;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class CustomTokenGeneratorTest extends TestCase
{
    private ?CreateCustomToken $action = null;

    private CustomTokenGenerator $generator;

    protected function setUp(): void
    {
        $handler = $this->createStub(Handler::class);
        $handler->method('handle')->willReturnCallback(function (CreateCustomToken $action): Token {
            $this->action = $action;

            return SecureToken::withValues('', [], []);
        });

        $this->generator = new CustomTokenGenerator($handler);
    }

    #[DoesNotPerformAssertions]
    public function testItCanBeCreatedWithCredentials(): void
    {
        CustomTokenGenerator::withClientEmailAndPrivateKey('email@domain.tld', 'some-private-key');
    }

    public function testItDelegatesASimpleAction(): void
    {
        $this->generator->createCustomToken('uid');
        $this->assertInstanceOf(CreateCustomToken::class, $this->action);
        $this->assertSame('uid', $this->action->uid());
        $this->assertEmpty($this->action->customClaims());
        $this->assertTrue(Duration::fromDateIntervalSpec(CreateCustomToken::DEFAULT_TTL)->equals($this->action->timeToLive()));
    }

    public function testItDelegatesAnActionWithCustomClaims(): void
    {
        $customClaims = ['first' => 'first', 'true' => true, 'false' => false, 'null' => null];
        $this->generator->createCustomToken('uid', $customClaims);
        $this->assertInstanceOf(CreateCustomToken::class, $this->action);

        $this->assertEquals($customClaims, $this->action->customClaims());
    }

    public function testItDelegatesAnActionWithACustomTokenExpiration(): void
    {
        $this->generator->createCustomToken('uid', [], 1337);
        $this->assertInstanceOf(CreateCustomToken::class, $this->action);

        $this->assertTrue(Duration::inSeconds(1337)->equals($this->action->timeToLive()));
    }

    public function testItUsesATenantIdWhenGiven(): void
    {
        $this->generator->withTenantId('my-tenant')->createCustomToken('uid');
        $this->assertInstanceOf(CreateCustomToken::class, $this->action);

        $this->assertSame('my-tenant', $this->action->tenantId());
    }
}
