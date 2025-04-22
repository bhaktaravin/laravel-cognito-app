<?php

namespace App\Http\Controllers;

use App\Services\DynamoDbService;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    //

    protected $dynamo;

    public function __construct(DynamoDbService $dynamo)
    {
        $this->dynamo = $dynamo;
    }

    public function listUsers()
    {
        $users = $this->dynamo->listUsers();
        return response()->json($users);
    }
}
