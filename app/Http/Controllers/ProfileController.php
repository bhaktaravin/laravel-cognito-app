<?php

namespace App\Http\Controllers;

use App\Service\DynamoDbService;



class ProfileController extends Controller
{
    public function store(Request $request, DynamoDbService $db)
    {
        $db->putItem([
            'id' => (string) Str::uuid(),
            'name' => $request->name,
            'email' => $request->email,
        ]);

        return response()->json(['message' => 'Saved to DynamoDB']);
    }

    public function show($id, DynamoDbService $db)
    {
        $item = $db->getItem($id);
        return response()->json($item['Item'] ?? []);
    }
}
