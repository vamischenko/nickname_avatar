<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class UserService
{
    private const USERS_SET_KEY = 'users:set';

    private const USER_KEY_PREFIX = 'user:';

    public function exists(string $nickname): bool
    {
        return (bool) Redis::exists(self::USER_KEY_PREFIX.$nickname);
    }

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

        Redis::setex($key, $ttlSeconds, json_encode($user));

        $score = now()->timestamp;
        Redis::zadd(self::USERS_SET_KEY, $score, $nickname);

        return $user;
    }

    public function all(): array
    {
        $nicknames = Redis::zrange(self::USERS_SET_KEY, 0, -1);

        if (empty($nicknames)) {
            return [];
        }

        $users = [];

        foreach ($nicknames as $nickname) {
            $data = Redis::get(self::USER_KEY_PREFIX.$nickname);

            if ($data !== null) {
                $users[] = json_decode($data, true);
            } else {
                Redis::zrem(self::USERS_SET_KEY, $nickname);
            }
        }

        return array_reverse($users);
    }

    public function deleteExpired(): int
    {
        $ttlMinutes = (int) config('app.user_ttl_minutes', 60);
        $cutoff = now()->subMinutes($ttlMinutes)->timestamp;

        $expiredNicknames = Redis::zrangebyscore(self::USERS_SET_KEY, '-inf', $cutoff);

        if (empty($expiredNicknames)) {
            return 0;
        }

        $deleted = 0;

        foreach ($expiredNicknames as $nickname) {
            Redis::del(self::USER_KEY_PREFIX.$nickname);
            Redis::zrem(self::USERS_SET_KEY, $nickname);
            $deleted++;
        }

        return $deleted;
    }
}
