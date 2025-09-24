<?php

namespace App\Http\Controllers\Auth;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class GmailOAuthController extends Controller
{
    public function login(Request $request)
    {
        $payload = $request->getPayload();

        $accessToken = $payload->get('access_token');
        if (empty($accessToken)) {
            throw new BadRequestHttpException("Access token required");
        }

        $client = new \Google_Client(['client_id' => '77431662884-43n6oacikogkt26rslhokgkrbqs76q70.apps.googleusercontent.com']);

        $result = $client->verifyIdToken($payload['access_token']);
        $response = new Response();
        $response->setContent($result);
        return $response;
    }
}
