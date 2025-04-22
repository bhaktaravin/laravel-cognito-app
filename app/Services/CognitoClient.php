<?php

namespace App\Services;


use Aws\CognitoIdentityProvider\CognitoIdentityProviderClient;

class CognitoClient {
    protected $client;

    public function __construct(){
        $this->client = new CognitoIdentityProviderClient([
            'version' => 'latest',
            'region' => config('services.cognito.region'),
            'credentials' => [
                'key' => config('services.cognito.key'),
                'secret' => config('services.cognito.secret'),
            ],
        ]);


    }

    public function getClient(){
        return $this->client;
    }





    public function testCognitoConnection(){
        try {
            $this->client->getClient();

            $response = $client0>describeUserPool([
                'UserPoolId' => config('services.cognito.user_pool_id'),
            ]);

            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }

    }
}


