<?php

namespace Metrogistics\AzureSocialite;

class UserFactory
{
    protected $config;
    protected static $user_callback;

    public function __construct()
    {
        $this->config = config('azure-oauth');
    }

    public function convertAzureUser($azure_user, $user)
    {
        $user_class = config('azure-oauth.user_class');
        $user_map = config('azure-oauth.user_map');
        $id_field = config('azure-oauth.user_id_field');

        if(!$user) {
            $user = new $user_class;
        }

        $user->$id_field = $azure_user->id;

        foreach($user_map as $azure_field => $user_field){
            $user->$user_field = $azure_user->$azure_field;
        }

        $callback = static::$user_callback;

        if($callback && is_callable($callback)){
            $callback($user, $azure_user);
        }

        $user->save();

        return $user;
    }

    public static function userCallback($callback)
    {
        if(! is_callable($callback)){
            throw new \Exception("Must provide a callable.");
        }

        static::$user_callback = $callback;
    }
}
