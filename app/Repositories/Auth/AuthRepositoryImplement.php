<?php

namespace App\Repositories\Auth;

use App\Models\Auth;
use App\Models\OauthClient;
use App\Models\User;
use App\Traits\ServiceResponser;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use LaravelEasyRepository\Implementations\Eloquent;

class AuthRepositoryImplement extends Eloquent implements AuthRepository
{

    use ServiceResponser;

    /**
     * Model class to be used in this repository for the common methods inside Eloquent
     * Don't remove or change $this->model variable name
     * @property Model|mixed $model;
     */
    protected User $model;

    public function __construct(User $model)
    {
        $this->model = $model;
    }

    public function login($email, $password): array
    {
        $user = $this->model->where('email', $email)->first();
        if (!$user) return $this->finalResultFail(message: 'User not found');

        if (!Hash::check($password, $user->password)) return $this->finalResultFail(message: 'Wrong password');

        // set 2 for grant client password
        $clientId = 2;
        $clientSecret = OauthClient::find($clientId)->secret;
        $response = Http::asForm()->timeout(10)->post(env('APP_URL') . 'oauth/token', [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'grant_type' => 'password',
            'username' => $email,
            'password' => $password,
            'scope' => ''
        ]);

        if ($response->failed()) {
            return $this->finalResultFail(message: $response->json()['message']);
        }

        return $this->finalResultSuccess($response->json());
    }

    public function refreshToken($refreshToken): array
    {
        // set 2 for grant client password
        $clientId = 2;
        $clientSecret = OauthClient::find($clientId)->secret;
        $response = Http::asForm()->timeout(10)->post(env('APP_URL') . 'oauth/token', [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
            'scope' => ''
        ]);

        if ($response->failed()) {
            return $this->finalResultFail(message: $response->json()['message']);
        }

        return $this->finalResultSuccess($response->json());
    }
}
