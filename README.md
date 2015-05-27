# Vend Provider for OAuth 2.0 Client

[![Build Status](https://img.shields.io/travis/wheniwork/oauth2-vend.svg)](https://travis-ci.org/wheniwork/oauth2-vend)
[![Code Coverage](https://img.shields.io/coveralls/wheniwork/oauth2-vend.svg)](https://coveralls.io/r/wheniwork/oauth2-vend)
[![Code Quality](https://img.shields.io/scrutinizer/g/wheniwork/oauth2-vend.svg)](https://scrutinizer-ci.com/g/wheniwork/oauth2-vend/)
[![License](https://img.shields.io/packagist/l/wheniwork/oauth2-vend.svg)](https://github.com/wheniwork/oauth2-vend/blob/master/LICENSE)
[![Latest Stable Version](https://img.shields.io/packagist/v/wheniwork/oauth2-vend.svg)](https://packagist.org/packages/wheniwork/oauth2-vend)

This package provides Vend OAuth 2.0 support for the PHP League's [OAuth 2.0 Client](https://github.com/thephpleague/oauth2-client).

## Installation

To install, use composer:

```
composer require wheniwork/oauth2-vend
```

## Usage

Usage is the same as The League's OAuth client, using `Wheniwork\OAuth2\Client\Provider\Vend` as the provider.

### Authorization Code Flow

```php
$provider = new Wheniwork\OAuth2\Client\Provider\Vend([
    'clientId'     => '{vend-client-id}',
    'clientSecret' => '{vend-client-secret}',
    'domainPrefix' => '{vend-domain-prefix}',
    'redirectUri'  => 'https://example.com/callback-url'
]);

if (!isset($_GET['code'])) {

    // If we don't have an authorization code then get one
    $authUrl = $provider->getAuthorizationUrl();
    $_SESSION['oauth2state'] = $provider->state;
    header('Location: '.$authUrl);
    exit;

// Check given state against previously stored one to mitigate CSRF attack
} elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {

    unset($_SESSION['oauth2state']);
    exit('Invalid state');

} else {

    // Try to get an access token (using the authorization code grant)
    $token = $provider->getAccessToken('authorization_code', [
        'code' => $_GET['code']
    ]);

    // Vend does not provide a way to get information about the currently
    // authenticated user. (If you know of a way, please let me know!)

    // Use this to interact with an API on the users behalf
    echo $token->accessToken;
}
```

## Refreshing a Token

```php
$provider = new Wheniwork\OAuth2\Client\Provider\Vend([
    'clientId'     => '{vend-client-id}',
    'clientSecret' => '{vend-client-secret}',
    'domainPrefix' => '{vend-domain-prefix}',
    'redirectUri'  => 'https://example.com/callback-url'
]);

$grant = new \League\OAuth2\Client\Grant\RefreshToken();
$token = $provider->getAccessToken($grant, ['refresh_token' => $refreshToken]);
```
