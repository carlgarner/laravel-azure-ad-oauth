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
            $azure_user = Socialite::driver('azure-oauth')->user();
        } catch(InvalidStateException $e) {
            $azure_user = Socialite::driver('azure-oauth')->stateless()->user();
        }

        $user = $this->findOrCreateUser($azure_user);

        auth()->login($user, true);

        // session([
        //     'azure_user' => $user
        // ]);

        return redirect(
            config('azure-oauth.redirect_on_login')
        );
    }

    protected function findOrCreateUser($azure_user)
    {
	    $user_class = config('azure-oauth.user_class');
	    $user_field = config('azure-oauth.user_azure_field');

        $user = $user_class::where(config('azure-oauth.user_id_field'), $azure_user->$user_field)->first();

        return (new UserFactory())->convertAzureUser($azure_user, $user);
    }
}
