<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class SocialiteAuthenticationController extends Controller
{
    public function redirect(): \Illuminate\Http\JsonResponse
    {
        return response()->json(Socialite::driver('google')->stateless()->redirect()->getTargetUrl());
    }

    public function callback(): \Illuminate\Http\RedirectResponse
    {
        $google_user = Socialite::driver('google')->stateless()->user();

        try
        {
            $user = User::updateOrCreate(
                ['email' => $google_user->getEmail()],
                [
                    'email' => $google_user->getEmail(), 'name' => $google_user->getName(),
                    'avatar' => $google_user->getAvatar(), 'socialite_id' => $google_user->getId(),
                    'nickname' => $google_user->getNickname(), 'socialite_token' => $google_user->token
                ]
            );

            Auth::login($user, $user->remember_token !== null);
        }
        catch (\Exception $exception)
        {
            Log::error("[Social Authentication Module] Error found {$exception->getMessage()} " .
                "with stack trace {$exception->getTraceAsString()}");

            return response()->redirectTo(config('app.frontend_url') . '/login')
                    ->withErrors($exception->getMessage())
                    ->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->redirectTo(config('app.frontend_url') . '/dashboard');
    }
}
