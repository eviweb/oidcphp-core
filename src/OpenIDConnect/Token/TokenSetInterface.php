<?php

namespace OpenIDConnect\Token;

use JsonSerializable;
use OpenIDConnect\Claims;

/**
 * The token set interface for OpenID Connect flow
 */
interface TokenSetInterface extends JsonSerializable
{
    /**
     * @see https://openid.net/specs/openid-connect-core-1_0.html#IDToken
     */
    public const REQUIRED_CLAIMS = [
        'aud',
        'exp',
        'iat',
        'iss',
        'sub',
    ];

    /**
     * @see https://tools.ietf.org/html/rfc6749#section-1.4
     * @return string
     */
    public function accessToken(): string;

    /**
     * @see https://tools.ietf.org/html/rfc6749#section-5.1
     * @return int|null
     */
    public function expiresIn(): ?int;

    /**
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool;

    /**
     * @return bool
     */
    public function hasExpiresIn(): bool;

    /**
     * @return bool
     */
    public function hasIdToken(): bool;

    /**
     * @return bool
     */
    public function hasRefreshToken(): bool;

    /**
     * @return bool
     */
    public function hasScope(): bool;

    /**
     * Verified claim from ID token string
     *
     * @see https://openid.net/specs/openid-connect-core-1_0.html#IDToken
     * @return Claims
     */
    public function idTokenClaims(): Claims;

    /**
     * The raw ID token string
     *
     * @return string
     */
    public function idToken(): ?string;

    /**
     * @see https://tools.ietf.org/html/rfc6749#section-1.5
     * @return string|null
     */
    public function refreshToken(): ?string;

    /**
     * @see https://tools.ietf.org/html/rfc6749#section-5.1
     * @return array|null
     */
    public function scope(): ?array;

    /**
     * Returns additional vendor values stored in the token.
     *
     * @param string|null $key Given null will get all value
     * @param mixed $default Return default when the key is not found
     * @return mixed
     */
    public function values(string $key = null, $default = null);
}