<?php

namespace Modules\Shop\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\User\Contracts\Authentication;
use Modules\User\Repositories\UserTokenRepository;

/**
 * @resource Shop::Auth
 */
class AuthController extends Controller
{
    /**
     * @var Authentication
     */
    private $auth;
    /**
     * @var UserTokenRepository
     */
    private $userToken;

    public function __construct(Authentication $auth, UserTokenRepository $userToken)
    {
        $this->auth = $auth;
        $this->userToken = $userToken;
    }

    public function postLogin(Request $request)
    {
        $credentials = [
            'email' => $request->email,
            'password' => $request->password,
        ];

        $remember = (bool) $request->get('remember_me', false);

        $error = $this->auth->login($credentials, $remember);

        if ($error) {
            return response()->json([
                'errors' => true,
                'message' => $error,
            ], 400);
        }

        $user = $this->auth->user();

        if($token = $user->api_keys()->first()) $user->token = $token->access_token;
        else $user->token = $this->userToken->generateFor($this->auth->id());

        return response()->json([
            'errors' => false,
            'message' => trans('user::messages.successfully logged in'),
            'data' => $user
        ]);
    }

    public function getUser(Request $request)
    {
        $user = $this->auth->user();

        if($token = $user->api_keys()->first()) $user->token = $token->access_token;
        else $user->token = $this->userToken->generateFor($this->auth->id());

        return response()->json([
            'errors' => false,
            'message' => trans('user::messages.welcome'),
            'data' => $user
        ]);
    }

}
