<?php

namespace App\Providers;

use Illuminate\Support\Facades\Redis;
use Illuminate\Support\ServiceProvider;
use Predis\ClientInterface;

/**
 * Основной сервис-провайдер приложения.
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Регистрирует биндинги в контейнере.
     *
     * Привязывает {@see ClientInterface} к Redis-подключению по умолчанию,
     * чтобы {@see \App\Services\UserService} мог получать клиент через DI.
     */
    public function register(): void
    {
        $this->app->bind(ClientInterface::class, fn () => Redis::connection()->client());
    }

    /**
     * Выполняется после регистрации всех сервис-провайдеров.
     */
    public function boot(): void
    {
    }
}
