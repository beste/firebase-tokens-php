<?php

declare(strict_types=1);

namespace Kreait\Firebase\JWT\Tests;

use Kreait\Firebase\JWT\Action\VerifyIdToken;
use Kreait\Firebase\JWT\Action\VerifyIdToken\Handler;
use Kreait\Firebase\JWT\Contract\Token;
use Kreait\Firebase\JWT\IdTokenVerifier;
use Kreait\Firebase\JWT\SecureToken;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;

/**
 * @internal
 */
final class IdTokenVerifierTest extends TestCase
{
    private ?VerifyIdToken $action = null;

    private IdTokenVerifier $verifier;

    protected function setUp(): void
    {
        $handler = $this->createStub(Handler::class);
        $handler->method('handle')->willReturnCallback(function (VerifyIdToken $action): Token {
            $this->action = $action;

            return SecureToken::withValues('', [], []);
        });

        $this->verifier = new IdTokenVerifier($handler);
    }

    #[DoesNotPerformAssertions]
    public function testItCanBeCreatedWithAProjectId(): void
    {
        IdTokenVerifier::createWithProjectId('project-id');
    }

    #[DoesNotPerformAssertions]
    public function testItCanBeCreatedWithAProjectIdAndCustomCache(): void
    {
        IdTokenVerifier::createWithProjectIdAndCache('project-id', $this->createStub(CacheItemPoolInterface::class));
    }

    public function testItVerifiesAToken(): void
    {
        $this->verifier->verifyIdToken('token');
        $this->assertInstanceOf(VerifyIdToken::class, $this->action);
        $this->assertSame('token', $this->action->token());
        $this->assertSame(0, $this->action->leewayInSeconds());
    }

    public function testItVerifiesATokenWithLeeway(): void
    {
        $this->verifier->verifyIdTokenWithLeeway('token', 1337);
        $this->assertInstanceOf(VerifyIdToken::class, $this->action);
        $this->assertSame('token', $this->action->token());
        $this->assertSame(1337, $this->action->leewayInSeconds());
    }

    public function testItVerifiesATokenWithAnExpectedTenantId(): void
    {
        $this->verifier->withExpectedTenantId('my-tenant')->verifyIdToken('token');
        $this->assertInstanceOf(VerifyIdToken::class, $this->action);
        $this->assertSame('my-tenant', $this->action->expectedTenantId());
    }
}
