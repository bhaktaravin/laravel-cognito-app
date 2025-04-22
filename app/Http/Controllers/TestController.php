<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CognitoClient;

use Illuminate\Http\JsonResponse;


class TestController extends Controller
{
    //

    public function testCognito(CognitoClient $cognitoClient): JsonResponse
    {
        try {
            $client = $cognitoClient->getClient();

            // Call a safe operation to test connection
            $result = $client->listUserPools([
                'MaxResults' => 5,
            ]);

            return response()->json($result->toArray());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function testDynamoDB() {
        $dynamoClient = new \Aws\DynamoDb\DynamoDbClient([
            'region' => config('services.dynamodb.region'),
            'version' => 'latest',
            'credentials' => [
                'key' => config('services.dynamodb.key'),
                'secret' => config('services.dynamodb.secret'),
            ]
        ]);

        try {

            $users = $this->$dynamoClient->listUsers();
            return response()->json($users);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
