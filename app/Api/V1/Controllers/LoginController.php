<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Requests\LoginRequest;
use App\Http\Controllers\Controller;
use Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\JWTAuth;

class LoginController extends Controller
{
    /**
     * Log the user in.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(LoginRequest $request, JWTAuth $JWTAuth)
    {
        $credentials = $request->only(['email', 'password']);

        try {
            $token = Auth::guard()->attempt($credentials);

            if (! $token) {
                throw new AccessDeniedHttpException();
            }
        } catch (JWTException $e) {
            Log::error($e->message());

            throw new HttpException(500);
        }

        $role_id = Auth::guard()->user()->hasRoles()->pluck('id');

        return response()
            ->json([
                'status' => 'ok',
                'token' => $token,
                'user' => Auth::guard()->user(),
                'role' => Auth::guard()->user()->hasRoles()->get(),
                'expires_in' => Auth::guard()->factory()->getTTL() * 120,
            ]);
    }
}
