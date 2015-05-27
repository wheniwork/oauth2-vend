<?php

namespace Wheniwork\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessToken;

class Vend extends AbstractProvider
{
    /**
     * @var string
     */
    public $domainPrefix;

    public $authorizationHeader = 'Bearer';

    public function __construct($options = [])
    {
        if (empty($options['domainPrefix'])) {
            throw new \RuntimeException('Vend provider requires a "domainPrefix" option');
        }
        parent::__construct($options);
    }

    /**
     * Get a Vend API URL, depending on path.
     *
     * @param  string $path
     * @return string
     */
    protected function getApiUrl($path)
    {
        return sprintf(
            'https://%s.vendhq.com/api/%s',
            $this->domainPrefix,
            $path
        );
    }

    public function urlAuthorize()
    {
        return 'https://secure.vendhq.com/connect';
    }

    public function urlAccessToken()
    {
        return $this->getApiUrl('1.0/token');
    }

    public function urlUserDetails(AccessToken $token)
    {
        throw new \RuntimeException('Vend does not provide details for single users');
    }

    public function userDetails($response, AccessToken $token)
    {
        return [];
    }

    /**
     * Helper method that can be used to fetch API responses.
     *
     * @param  string      $path
     * @param  AccessToken $token
     * @param  boolean     $as_array
     * @return array|object
     */
    public function getApiResponse($path, AccessToken $token, $as_array = true)
    {
        $url = $this->getApiUrl($path);

        $headers = $this->getHeaders($token);

        return json_decode($this->fetchProviderData($url, $headers), $as_array);
    }
}
