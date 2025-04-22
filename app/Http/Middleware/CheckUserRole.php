<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\DynamoDbService;
class CheckUserRole
{
        public function handle(Request $request, Closure $next, $role)
        {
            $userSub = $request->user()->sub ?? null;
            if (!$userSub) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            $dynamoUser = app(DynamoDbService::class)->getUserItem($userSub);

            if (!isset($dynamoUser['role']) || $dynamoUser['role'] !== $role) {
                return response()->json(['message' => 'Forbidden'], 403);
            }

            return $next($request);
        }


}
