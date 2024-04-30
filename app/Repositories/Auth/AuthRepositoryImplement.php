<?php

namespace App\Repositories\Auth;

use App\Models\Auth;
use App\Models\OauthClient;
use App\Models\Profile;
use App\Models\User;
use App\Traits\ServiceResponser;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Laravel\Passport\Token;
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
    protected Profile $profile;

    public function __construct(
        User    $model,
        Profile $profile
    )
    {
        $this->model = $model;
        $this->profile = $profile;
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

    public function logout($user): array
    {
        $revoke = Token::where('user_id', $user->id)->update(['revoked' => true]);

        if (!$revoke) return $this->finalResultFail(message: 'Logout failed');
        return $this->finalResultSuccess();
    }

    public function verifyProfile($data): array
    {
        $user = $this->model->with('profile')->find($data['user_id']);
        if (!$user) return $this->finalResultFail(message: 'User not found');

        if ($user->is_verified) return $this->finalResultFail(message: 'Profile already Verified');

        if ($user->profile != null) return $this->finalResultFail(message: 'Profile already Updated');

        $phoneNumberExist = $this->profile->where('phone_number', $data['phone_number'])->exists();
        if ($phoneNumberExist) return $this->finalResultFail(message: 'Profile already exist');

        $profile = $this->profile->create([
            'user_id' => $data['user_id'],
            'full_name' => $data['full_name'],
            'phone_number' => $data['phone_number'],
            'country' => $data['country'],
            'address' => $data['address'],
        ]);
        if (!$profile) return $this->finalResultFail(message: 'Profile verification failed');

        $user->is_verified = true;
        $status = $user->save();

        if (!$status) return $this->finalResultFail(message: 'Verify profile failed');
        return $this->finalResultSuccess();
    }
}
