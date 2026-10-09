<?php

declare(strict_types=1);

namespace Kreait\Firebase\JWT\Tests;

use Kreait\Firebase\JWT\Action\VerifySessionCookie;
use Kreait\Firebase\JWT\Action\VerifySessionCookie\Handler;
use Kreait\Firebase\JWT\Contract\Token;
use Kreait\Firebase\JWT\SecureToken;
use Kreait\Firebase\JWT\SessionCookieVerifier;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;

/**
 * @internal
 */
final class SessionCookieVerifierTest extends TestCase
{
    private ?VerifySessionCookie $action = null;

    private SessionCookieVerifier $verifier;

    protected function setUp(): void
    {
        $handler = $this->createStub(Handler::class);
        $handler->method('handle')->willReturnCallback(function (VerifySessionCookie $action): Token {
            $this->action = $action;

            return SecureToken::withValues('', [], []);
        });

        $this->verifier = new SessionCookieVerifier($handler);
    }

    #[DoesNotPerformAssertions]
    public function testItCanBeCreatedWithAProjectId(): void
    {
        SessionCookieVerifier::createWithProjectId('project-id');
    }

    #[DoesNotPerformAssertions]
    public function testItCanBeCreatedWithAProjectIdAndCustomCache(): void
    {
        SessionCookieVerifier::createWithProjectIdAndCache('project-id', $this->createStub(CacheItemPoolInterface::class));
    }

    public function testItVerifiesAToken(): void
    {
        $this->verifier->verifySessionCookie('cookie');
        $this->assertInstanceOf(VerifySessionCookie::class, $this->action);
        $this->assertSame('cookie', $this->action->sessionCookie());
        $this->assertSame(0, $this->action->leewayInSeconds());
    }

    public function testItVerifiesATokenWithLeeway(): void
    {
        $this->verifier->verifySessionCookieWithLeeway('cookie', 1337);
        $this->assertInstanceOf(VerifySessionCookie::class, $this->action);
        $this->assertSame('cookie', $this->action->sessionCookie());
        $this->assertSame(1337, $this->action->leewayInSeconds());
    }

    public function testItVerifiesATokenWithAnExpectedTenantId(): void
    {
        $this->verifier->withExpectedTenantId('my-tenant')->verifySessionCookie('cookie');
        $this->assertInstanceOf(VerifySessionCookie::class, $this->action);
        $this->assertSame('my-tenant', $this->action->expectedTenantId());
    }
}
