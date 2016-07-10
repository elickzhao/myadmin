<?php

namespace Illuminate\Auth;

use InvalidArgumentException; //无效的参数异常

trait CreatesUserProviders
{
    /**
     * The registered custom provider creators.
     *
     * @var array
     */
    protected $customProviderCreators = [];

    /**
     * Create the user provider implementation for the driver.
     *
     * @param  string  $provider
     * @return \Illuminate\Contracts\Auth\UserProvider
     *
     * @throws \InvalidArgumentException //无效的参数异常
     */
    public function createUserProvider($provider)
    {
        $config = $this->app['config']['auth.providers.'.$provider];
        //customProviderCreators[] 这个在AuthManager里的provider()方法 是保存这个属性 但是好像目前登录验证并没有使用
        //没有第一次取出后保存到数组里
        if (isset($this->customProviderCreators[$config['driver']])) {
            return call_user_func(
                $this->customProviderCreators[$config['driver']], $this->app, $config
            );
        }

        switch ($config['driver']) {
            case 'database':
                return $this->createDatabaseProvider($config);
            case 'eloquent':
                return $this->createEloquentProvider($config);
            default:
                throw new InvalidArgumentException("Authentication user provider [{$config['driver']}] is not defined.");
        }
    }

    /**
     * Create an instance of the database user provider.
     *
     * @param  array  $config
     * @return \Illuminate\Auth\DatabaseUserProvider
     */
    protected function createDatabaseProvider($config)
    {
        $connection = $this->app['db']->connection();

        return new DatabaseUserProvider($connection, $this->app['hash'], $config['table']);
    }

    /**
     * Create an instance of the Eloquent user provider.
     * 创建一个 Eloquent 用户提供程序实例
     * @param  array  $config
     * @return \Illuminate\Auth\EloquentUserProvider
     */
    protected function createEloquentProvider($config)
    {
        return new EloquentUserProvider($this->app['hash'], $config['model']);
    }
}
