<?php

namespace Tests\OpenIDConnect\OAuth2\Grant;

use OpenIDConnect\Exceptions\RelyingPartyException;
use OpenIDConnect\OAuth2\Grant\Factory;
use OpenIDConnect\OAuth2\Grant\Password;
use PHPUnit\Framework\TestCase;

class PasswordTest extends TestCase
{
    /**
     * @test
     */
    public function shouldReturnPasswordWhenRegisterInFactory(): void
    {
        $factory = new Factory();
        $factory->setGrant('some', new Password());

        $this->assertInstanceOf(Password::class, $factory->getGrant('some'));
    }

    /**
     * @test
     */
    public function shouldReturnPasswordWhenNotRegisterInFactory(): void
    {
        $factory = new Factory();

        $this->assertInstanceOf(Password::class, $factory->getGrant('password'));
    }

    /**
     * @test
     */
    public function shouldReturnOkayWhenRequireParametersIsReady(): void
    {
        $target = (new Factory())->getGrant('password');

        $actual = $target->prepareRequestParameters([
            'username' => 'some-username',
            'password' => 'some-password',
        ]);

        $this->assertSame('password', $actual['grant_type']);
        $this->assertSame('some-username', $actual['username']);
        $this->assertSame('some-password', $actual['password']);
    }

    /**
     * @test
     */
    public function shouldThrowExceptionWhenRequireParametersIsNotReady(): void
    {
        $this->expectException(RelyingPartyException::class);

        $target = (new Factory())->getGrant('password');

        $target->prepareRequestParameters([]);
    }
}
