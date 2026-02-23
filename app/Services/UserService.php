<?php

namespace App\Services;

use Illuminate\Support\Str;
use Predis\ClientInterface;

/**
 * Сервис для управления пользователями через Redis.
 *
 * Пользователи хранятся как строки JSON с TTL.
 * Sorted set используется для упорядоченного листинга и очистки по времени.
 */
class UserService
{
    /** @var string Ключ sorted set со всеми никнеймами (score = unix timestamp создания) */
    private const USERS_SET_KEY = 'users:set';

    /** @var string Префикс ключа отдельного пользователя: user:{nickname} */
    private const USER_KEY_PREFIX = 'user:';

    /**
     * @param  ClientInterface  $redis  Redis-клиент
     */
    public function __construct(private readonly ClientInterface $redis) {}

    /**
     * Проверяет, существует ли пользователь с заданным никнеймом.
     *
     * @param  string  $nickname  Никнейм для проверки
     * @return bool true, если пользователь найден в Redis
     */
    public function exists(string $nickname): bool
    {
        return (bool) $this->redis->exists(self::USER_KEY_PREFIX.$nickname);
    }

    /**
     * Создаёт нового пользователя и сохраняет его в Redis.
     *
     * Запись хранится с TTL, определённым в конфиге app.user_ttl_minutes.
     * Никнейм также добавляется в sorted set для листинга.
     *
     * @param  string  $nickname  Уникальный никнейм пользователя
     * @param  string  $avatarPath  Путь к файлу аватара относительно диска public
     * @return array{id: string, nickname: string, avatar: string, created_at: string} Данные созданного пользователя
     */
    public function create(string $nickname, string $avatarPath): array
    {
        $ttlSeconds = (int) config('app.user_ttl_minutes', 60) * 60;

        $user = [
            'id' => Str::uuid()->toString(),
            'nickname' => $nickname,
            'avatar' => $avatarPath,
            'created_at' => now()->toIso8601String(),
        ];

        $key = self::USER_KEY_PREFIX.$nickname;

        $this->redis->setex($key, $ttlSeconds, json_encode($user));

        $score = now()->timestamp;
        $this->redis->zadd(self::USERS_SET_KEY, [$nickname => $score]);

        return $user;
    }

    /**
     * Возвращает список всех актуальных пользователей, отсортированных от новых к старым.
     *
     * Если ключ пользователя в Redis истёк (TTL вышел), запись удаляется из sorted set.
     *
     * @return array<int, array{id: string, nickname: string, avatar: string, created_at: string}>
     */
    public function all(): array
    {
        $nicknames = $this->redis->zrange(self::USERS_SET_KEY, 0, -1);

        if (empty($nicknames)) {
            return [];
        }

        $users = [];

        foreach ($nicknames as $nickname) {
            $data = $this->redis->get(self::USER_KEY_PREFIX.$nickname);

            if ($data !== null) {
                $users[] = json_decode($data, true);
            } else {
                $this->redis->zrem(self::USERS_SET_KEY, $nickname);
            }
        }

        return array_reverse($users);
    }

    /**
     * Удаляет пользователей, созданных раньше порогового времени.
     *
     * Порог вычисляется как: now() - app.user_ttl_minutes.
     * Удаляет как ключ пользователя, так и запись в sorted set.
     *
     * @return int Количество удалённых записей
     */
    public function deleteExpired(): int
    {
        $ttlMinutes = (int) config('app.user_ttl_minutes', 60);
        $cutoff = now()->subMinutes($ttlMinutes)->timestamp;

        $expiredNicknames = $this->redis->zrangebyscore(self::USERS_SET_KEY, '-inf', $cutoff);

        if (empty($expiredNicknames)) {
            return 0;
        }

        $deleted = 0;

        foreach ($expiredNicknames as $nickname) {
            $this->redis->del(self::USER_KEY_PREFIX.$nickname);
            $this->redis->zrem(self::USERS_SET_KEY, $nickname);
            $deleted++;
        }

        return $deleted;
    }
}
