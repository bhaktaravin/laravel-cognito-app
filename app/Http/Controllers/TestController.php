<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CognitoClient;

use Illuminate\Http\JsonResponse;
use App\Services\DynamoDbService;

class TestController extends Controller
{
    //
    protected $dynamo;

    public function __construct(DynamoDbService $dynamo)
    {
        $this->dynamo = $dynamo;
    }

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

    public function testDynamoDB()
    {
        $users = $this->dynamo->listUsers();
        return response()->json($users);

    }

}
