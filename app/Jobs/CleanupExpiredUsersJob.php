<?php

namespace App\Jobs;

use App\Services\UserService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Job для очистки устаревших записей пользователей из Redis.
 *
 * Запускается по расписанию каждые N минут (см. routes/console.php).
 * Удаляет записи, чей timestamp создания старше порога USER_TTL_MINUTES.
 */
class CleanupExpiredUsersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Выполняет очистку устаревших пользователей.
     *
     * Делегирует удаление в {@see UserService::deleteExpired()}
     * и записывает результат в лог.
     *
     * @param  UserService  $userService  Сервис для работы с пользователями в Redis
     */
    public function handle(UserService $userService): void
    {
        $deleted = $userService->deleteExpired();

        Log::info("CleanupExpiredUsersJob: deleted {$deleted} expired user(s).");
    }
}
