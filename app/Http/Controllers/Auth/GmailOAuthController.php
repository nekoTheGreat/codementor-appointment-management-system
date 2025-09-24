<?php

namespace App\Http\Controllers\Auth;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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

        $result = $client->verifyIdToken($payload['access_token']);
        $response = new Response();
        $response->setContent($result);
        return $response;
    }
}
