<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

use App\Services\DynamoDbService;


class UserProfileController extends Controller
{
    public function store(Request $request, DynamodbService $db)
    {
        $db->putUserItem([
            'id' => (string) Str::uuid(),
            'name' => $request->name,
            'email' => $request->email
        ]);

        return response()->json(['message' => 'Profile Saved']);
    }

    public function show($id, DynamoDbService $db)
    {
        $item = $db->getUserItem($id);
        return response()->json($item['Item'] ?? []);
    }
}
