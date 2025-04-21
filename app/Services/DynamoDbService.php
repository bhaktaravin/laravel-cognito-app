<?php

namespace App\Services;

use Aws\DynamoDb\DynamoDbClient;
use Aws\Sdk;
use Aws\DynamoDb\Marshaler;
use Illuminate\Support\Facades\Auth;
use Exception;

class DynamoDbService
{
    protected $dynamoDb;
    protected $table;
    protected $marshaler;

    public function __construct()
    {
        $sdk = new Sdk([
            'region' => env('AWS_DEFAULT_REGION'),
            'version' => 'latest',
            'credentials' => [
                'key' => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
            ],
        ]);

        $this->dynamoDb = $sdk->createDynamoDb();
        $this->table = env('DYNAMODB_TABLE');
        $this->marshaler = new Marshaler();
    }

    public function putUserItem(array $data)
    {
        $user = Auth::user();
        if (!$user) {
            throw new Exception("User not authenticated.");
        }

        $data['user_id'] = $user->sub ?? $user->id; // Adjust based on your auth structure
        return $this->putItem($data);
    }

    public function getUserItem($id)
    {
        $user = Auth::user();
        if (!$user) {
            throw new Exception("User not authenticated.");
        }

        return $this->dynamoDb->getItem([
            'TableName' => $this->table,
            'Key' => $this->marshal([
                'user_id' => $user->sub ?? $user->id,
                'id' => $id,
            ]),
        ]);
    }

    public function putItem(array $item)
    {
        return $this->dynamoDb->putItem([
            'TableName' => $this->table,
            'Item' => $this->marshal($item),
        ]);
    }

    public function getItem($key)
    {
        return $this->dynamoDb->getItem([
            'TableName' => $this->table,
            'Key' => $this->marshal(['id' => $key]),
        ]);
    }

    protected function marshal(array $data)
    {
        return $this->marshaler->marshalItem($data);
    }
}
