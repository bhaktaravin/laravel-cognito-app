<?php

namespace App\Services;


use Aws\CognitoIdentityProvider\CognitoIdentityProviderClient;

class CognitoClient {
    protected $client;

    public function _construct(){
        $this->client = new CognitoIdentityProviderClient([
            'version' => 'latest',
            'region' => env('AWS_DEFAULT_REGION'),
            'credentials' => [
                'key' => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
            ],
        ]);
    }
}
