<?php

namespace Tests\Core\Metadata;

use InvalidArgumentException;
use Jose\Component\Checker\InvalidHeaderException;
use Jose\Component\Core\Util\JsonConverter;
use Jose\Component\KeyManagement\JWKFactory;
use Jose\Component\Signature\Algorithm\ES256;
use Jose\Component\Signature\Algorithm\PS256;
use Jose\Component\Signature\Algorithm\RS256;
use Jose\Component\Signature\Serializer\CompactSerializer;
use OpenIDConnect\Jwt\Factory;
use OutOfRangeException;
use Tests\TestCase;

class FactoryTest extends TestCase
{
    /**
     * @test
     */
    public function shouldThrowExceptionWhenAlgorithmIsNotDefine(): void
    {
        $this->expectException(OutOfRangeException::class);

        $target = new Factory($this->createProviderMetadata([
            'id_token_signing_alg_values_supported' => ['Whatever'],
        ]), $this->createClientMetadata());

        $actual = $target->createAlgorithmManager();

        $this->assertInstanceOf(RS256::class, $actual->get('RS256'));
        $this->assertInstanceOf(ES256::class, $actual->get('ES256'));
    }
    /**
     * @test
     */
    public function shouldReturnAlgorithmManagerContainAlgorithms(): void
    {
        $target = new Factory($this->createProviderMetadata([
            'id_token_signing_alg_values_supported' => ['RS256', 'ES256'],
        ]), $this->createClientMetadata());

        $actual = $target->createAlgorithmManager();

        $this->assertInstanceOf(RS256::class, $actual->get('RS256'));
        $this->assertInstanceOf(ES256::class, $actual->get('ES256'));
    }

    /**
     * @test
     */
    public function shouldReturnAlgorithmManagerContainEncryptionAlgorithms(): void
    {
        $target = new Factory($this->createProviderMetadata([
            'id_token_encryption_alg_values_supported' => ['RS256', 'ES256', 'PS256'],
            'id_token_signing_alg_values_supported' => ['RS256', 'ES256'],
        ]), $this->createClientMetadata());

        $actual = $target->createAlgorithmManager();

        $this->assertInstanceOf(RS256::class, $actual->get('RS256'));
        $this->assertInstanceOf(ES256::class, $actual->get('ES256'));
        $this->assertInstanceOf(PS256::class, $actual->get('PS256'));
    }

    /**
     * @test
     */
    public function shouldThrowExceptionWhenAlgorithmIsNotFound(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $target = new Factory($this->createProviderMetadata([
            'id_token_signing_alg_values_supported' => ['RS256'],
        ]), $this->createClientMetadata());

        $actual = $target->createAlgorithmManager();

        $actual->get('ES256');
    }

    /**
     * @test
     */
    public function shouldReturnHeaderCheckManagerContainAlgorithms(): void
    {
        $target = new Factory($this->createProviderMetadata([
            'id_token_encryption_alg_values_supported' => ['RS256', 'PS256'],
            'id_token_signing_alg_values_supported' => ['RS256', 'ES256'],
        ]), $this->createClientMetadata());

        $actual = $target->createHeaderCheckerManager()->getCheckers()['alg'];

        $this->assertNull($actual->checkHeader('RS256'));
        $this->assertNull($actual->checkHeader('PS256'));
        $this->assertNull($actual->checkHeader('ES256'));
    }

    /**
     * @test
     */
    public function shouldThrowExceptionWhenAlgorithmsNotFoundInHeaderCheckerManager(): void
    {
        $this->expectException(InvalidHeaderException::class);
        $this->expectExceptionMessage('Unsupported algorithm');

        $target = new Factory($this->createProviderMetadata(), $this->createClientMetadata());

        $actual = $target->createHeaderCheckerManager()->getCheckers()['alg'];

        $actual->checkHeader('HS256');
    }

    /**
     * A full flow for sign and verify
     *
     * @test
     */
    public function shouldBuildJwtAndVerifyAndStringAndSerializeStringAndLoadUsingRS256(): void
    {
        $target = new Factory($this->createProviderMetadata(), $this->createClientMetadata());

        $jwk = JWKFactory::createRSAKey(1024, ['alg' => 'RS256']);

        $builder = $target->createJwsBuilder();

        $jws = $builder->withPayload(JsonConverter::encode([]))
            ->addSignature($jwk, ['alg' => 'RS256'])
            ->build();

        $verifier = $target->createJwsVerifier();

        $this->assertTrue($verifier->verifyWithKey($jws, $jwk, 0));

        $token = (new CompactSerializer())->serialize($jws);

        // {"alg": "RS256"} + []
        $this->assertStringStartsWith('eyJhbGciOiJSUzI1NiJ9.W10', $token);

        $loader = $target->createJwsLoader();

        $jws = $loader->loadAndVerifyWithKey($token, $jwk, $sign);

        $this->assertSame('[]', $jws->getPayload());
    }
}
