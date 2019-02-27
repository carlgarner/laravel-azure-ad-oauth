<?php

namespace Metrogistics\AzureSocialite;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;

class AuthController extends Controller
{
    public function redirectToOauthProvider()
    {
        return Socialite::driver('azure-oauth')->redirect();
    }

    public function handleOauthResponse(Request $request)
    {
        if (!$request->input('code')) {
            $redirect = redirect(config('azure-oauth.redirect_on_error'));
            $error = 'Login failed: ' .
                $request->input('error') .
                ' - ' .
                $request->input('error_description');
            return $redirect->withErrors($error);
        }

        try {
            $user = Socialite::driver('azure-oauth')->user();
        } catch(InvalidStateException $e) {
            $user = Socialite::driver('azure-oauth')->stateless()->user();
        }

        $authUser = $this->findOrCreateUser($user);

        auth()->login($authUser, true);

        // session([
        //     'azure_user' => $user
        // ]);

        return redirect(
            config('azure-oauth.redirect_on_login')
        );
    }

    protected function findOrCreateUser($user)
    {
	$user_class = config('azure-oauth.user_class');
	$user_field = config('azure-oauth.user_azure_field');
        $authUser = $user_class::where(config('azure-oauth.user_id_field'), $user->$user_field)->first();

        if ($authUser) {
            return $authUser;
        }

        $UserFactory = new UserFactory();

        return $UserFactory->convertAzureUser($user);
    }
}
