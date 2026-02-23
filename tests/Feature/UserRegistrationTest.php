<?php

namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UserRegistrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Redis::flushdb();
    }

    protected function tearDown(): void
    {
        Redis::flushdb();
        parent::tearDown();
    }

    public function test_register_user_successfully(): void
    {
        $response = $this->postJson('/api/register', [
            'nickname' => 'testuser',
            'avatar' => UploadedFile::fake()->image('avatar.jpg', 100, 100),
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => ['id', 'nickname', 'avatar', 'created_at'],
            ])
            ->assertJsonPath('data.nickname', 'testuser');
    }

    public function test_register_fails_with_duplicate_nickname(): void
    {
        $avatar = UploadedFile::fake()->image('avatar.jpg', 100, 100);

        $this->postJson('/api/register', [
            'nickname' => 'duplicateuser',
            'avatar' => $avatar,
        ])->assertStatus(201);

        $response = $this->postJson('/api/register', [
            'nickname' => 'duplicateuser',
            'avatar' => UploadedFile::fake()->image('avatar2.jpg', 100, 100),
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('errors.nickname.0', 'This nickname is already registered.');
    }

    public function test_register_fails_without_nickname(): void
    {
        $response = $this->postJson('/api/register', [
            'avatar' => UploadedFile::fake()->image('avatar.jpg', 100, 100),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['nickname']);
    }

    public function test_register_fails_without_avatar(): void
    {
        $response = $this->postJson('/api/register', [
            'nickname' => 'testuser',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['avatar']);
    }

    public function test_register_fails_with_invalid_avatar_mime(): void
    {
        $response = $this->postJson('/api/register', [
            'nickname' => 'testuser',
            'avatar' => UploadedFile::fake()->create('document.pdf', 500, 'application/pdf'),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['avatar']);
    }

    public function test_register_fails_with_oversized_avatar(): void
    {
        $response = $this->postJson('/api/register', [
            'nickname' => 'testuser',
            'avatar' => UploadedFile::fake()->image('big.jpg')->size(3000),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['avatar']);
    }

    public function test_register_fails_with_invalid_nickname_characters(): void
    {
        $response = $this->postJson('/api/register', [
            'nickname' => 'invalid nickname!',
            'avatar' => UploadedFile::fake()->image('avatar.jpg', 100, 100),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['nickname']);
    }

    public function test_users_list_page_returns_ok(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_registered_user_appears_in_list(): void
    {
        $this->postJson('/api/register', [
            'nickname' => 'listuser',
            'avatar' => UploadedFile::fake()->image('avatar.jpg', 100, 100),
        ])->assertStatus(201);

        $this->get('/')->assertStatus(200)->assertSee('listuser');
    }
}
