<?php

namespace Wheniwork\OAuth2\Client\Test\Provider;

use Wheniwork\OAuth2\Client\Provider\Vend;
use League\OAuth2\Client\Token\AccessToken;

use Mockery as m;

class VendTest extends \PHPUnit_Framework_TestCase
{
    protected $provider;

    protected function setUp()
    {
        $this->provider = new Vend([
            'clientId' => 'mock_client_id',
            'clientSecret' => 'mock_secret',
            'domainPrefix' => 'mock_domain',
            'redirectUri' => 'none',
        ]);
    }

    public function tearDown()
    {
        m::close();
        parent::tearDown();
    }

    public function testAuthorizationUrl()
    {
        $url = $this->provider->getAuthorizationUrl();
        $uri = parse_url($url);
        parse_str($uri['query'], $query);

        $this->assertArrayHasKey('client_id', $query);
        $this->assertArrayHasKey('redirect_uri', $query);
        $this->assertArrayHasKey('state', $query);
        $this->assertArrayHasKey('response_type', $query);
        $this->assertNotNull($this->provider->state);
    }

    public function testUrlAccessToken()
    {
        $url = $this->provider->urlAccessToken();
        $uri = parse_url($url);

        $this->assertEquals('/api/1.0/token', $uri['path']);
        $this->assertContains('mock_domain', $uri['host']);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testUrlUserDetails()
    {
        $token = new AccessToken(['access_token' => 'fake']);

        $url = $this->provider->urlUserDetails($token);
    }

    public function testGetAccessToken()
    {
        $expiration = time() + 60 * 60 * 24; // Vend tokens expire after 24 hours

        $response = m::mock('Guzzle\Http\Message\Response');
        $response->shouldReceive('getBody')->times(1)->andReturn(sprintf(
            '{"access_token": "mock_access_token", "expires": %d, "domain_prefix": "%s"}',
            $expiration,
            $this->provider->domainPrefix
        ));

        $client = m::mock('Guzzle\Service\Client');
        $client->shouldReceive('setBaseUrl')->times(1);
        $client->shouldReceive('post->send')->times(1)->andReturn($response);
        $this->provider->setHttpClient($client);

        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);

        $this->assertEquals('mock_access_token', $token->accessToken);
        $this->assertLessThanOrEqual($expiration, $token->expires);
        $this->assertGreaterThanOrEqual(time(), $token->expires);
    }
}
