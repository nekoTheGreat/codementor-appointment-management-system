<?php

namespace App\Http\Controllers\Auth;


use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class GmailOAuthController extends Controller
{
    public function login(Request $request)
    {
        $payload = $request->getPayload();

        $accessToken = $payload->get('access_token');
        if (empty($accessToken)) {
            throw new BadRequestHttpException("Access token required");
        }

        // verify token and retrieve user info
        $client = new \Google_Client(['client_id' => config('auth.google.client_id')]);
        $result = $client->verifyIdToken($accessToken);
        if (empty($result)) {
            throw new UnauthorizedHttpException("Failed to retrieve client data");
        }

        // create user if not found
        [
            'email' => $email,
            'given_name' => $firstName,
            'family_name' => $lastName,
            'sub' => $userId,
        ] = $result;
        $user = User::where(['email' => $email])->first();
        if (empty($user)) {
            $user = new User();
            $user->email = $email;
            $user->first_name = $firstName;
            $user->last_name = $lastName;
            $user->password = Hash::make(Str::password());
            $user->save();
        }

        // authenticate
        Auth::login($user);

        return redirect('/');
    }
}
