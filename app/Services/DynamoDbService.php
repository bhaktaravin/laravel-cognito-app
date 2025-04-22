<?php

namespace App\Services;

use Aws\DynamoDb\DynamoDbClient;
use Aws\Sdk;
use Aws\DynamoDb\Marshaler;
use Illuminate\Support\Facades\Auth;
use Exception;
use Illuminate\Support\Facades\Log;

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

    /**
     * Put a user item into DynamoDB.
     *
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function putUserItem(array $data)
{
    $user = Auth::user();
    if (!$user) {
        throw new Exception("User not authenticated.");
    }

    //Print Data
    Log::info("This is the data: ", $data);
    // Set 'UserId' using 'sub' field from the User model
    $data['UserId'] = $user->sub ?? $user->id; // Use 'sub' or 'id' depending on what you need

    // Log data to verify
    Log::info('Putting item into DynamoDB: ', $data);

    return $this->putItem($data);
}


    /**
     * Get a user item from DynamoDB.
     *
     * @param string $id
     * @return array
     * @throws Exception
     */
    public function getUserItem($id)
    {
        $user = Auth::user();
        if (!$user) {
            throw new Exception("User not authenticated.");
        }

        try {
            $result = $this->dynamoDb->getItem([
                'TableName' => $this->table,
                'Key' => $this->marshal([
                    'user_id' => $user->sub ?? $user->id,
                    'id' => $id,
                ]),
            ]);

            return $result;
        } catch (Exception $e) {
            Log::error('Error fetching user item from DynamoDB: ' . $e->getMessage());
            throw new Exception('Error fetching user item: ' . $e->getMessage());
        }
    }

    /**
     * Insert a generic item into DynamoDB.
     *
     * @param array $item
     * @return array
     */
    public function putItem(array $item)
    {
        try {
            $result = $this->dynamoDb->putItem([
                'TableName' => $this->table,
                'Item' => $this->marshal($item),
            ]);

            return $result;
        } catch (Exception $e) {
            Log::error('Error putting item into DynamoDB: ' . $e->getMessage());
            throw new Exception('Error putting item: ' . $e->getMessage());
        }
    }

    /**
     * Get an item from DynamoDB by key.
     *
     * @param mixed $key
     * @return array
     */
    public function getItem($key)
    {
        try {
            $result = $this->dynamoDb->getItem([
                'TableName' => $this->table,
                'Key' => $this->marshal(['id' => $key]),
            ]);

            return $result;
        } catch (Exception $e) {
            Log::error('Error fetching item from DynamoDB: ' . $e->getMessage());
            throw new Exception('Error fetching item: ' . $e->getMessage());
        }
    }

    /**
     * List all users from the DynamoDB table.
     *
     * @return array
     */
    public function listUsers()
    {
        try {
            $result = $this->dynamoDb->scan([
                'TableName' => $this->table,
            ]);
            Log::info("Listing users from DynamoDB: ", $result->toArray());
            return $result['Items'];
        } catch (Exception $e) {
            Log::error('Error listing users from DynamoDB: ' . $e->getMessage());
            return ['error' => 'Error listing users from DynamoDB: ' . $e->getMessage()];
        }
    }

    /**
     * Helper method to marshal data for DynamoDB.
     *
     * @param array $data
     * @return array
     */
    protected function marshal(array $data)
    {
        return $this->marshaler->marshalItem($data);
    }
}
