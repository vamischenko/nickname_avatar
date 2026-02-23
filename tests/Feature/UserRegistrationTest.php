<?php

namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Predis\ClientInterface;
use Tests\TestCase;

/**
 * Функциональные тесты API регистрации пользователей.
 *
 * Перед каждым тестом сбрасывает Redis и подменяет диск public на фейковый.
 */
class UserRegistrationTest extends TestCase
{
    /** @var ClientInterface Redis-клиент, используемый для сброса данных между тестами */
    private ClientInterface $redis;

    /**
     * Инициализирует фейковое хранилище и очищает Redis перед каждым тестом.
     */
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        $this->redis = $this->app->make(ClientInterface::class);
        $this->redis->flushdb();
    }

    /**
     * Очищает Redis после каждого теста.
     */
    protected function tearDown(): void
    {
        $this->redis->flushdb();
        parent::tearDown();
    }

    /**
     * Успешная регистрация возвращает 201 и данные пользователя.
     */
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

    /**
     * Повторная регистрация с тем же никнеймом возвращает 422.
     */
    public function test_register_fails_with_duplicate_nickname(): void
    {
        $this->postJson('/api/register', [
            'nickname' => 'duplicateuser',
            'avatar' => UploadedFile::fake()->image('avatar.jpg', 100, 100),
        ])->assertStatus(201);

        $response = $this->postJson('/api/register', [
            'nickname' => 'duplicateuser',
            'avatar' => UploadedFile::fake()->image('avatar2.jpg', 100, 100),
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('errors.nickname.0', 'This nickname is already registered.');
    }

    /**
     * Запрос без nickname возвращает ошибку валидации.
     */
    public function test_register_fails_without_nickname(): void
    {
        $response = $this->postJson('/api/register', [
            'avatar' => UploadedFile::fake()->image('avatar.jpg', 100, 100),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['nickname']);
    }

    /**
     * Запрос без avatar возвращает ошибку валидации.
     */
    public function test_register_fails_without_avatar(): void
    {
        $response = $this->postJson('/api/register', [
            'nickname' => 'testuser',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['avatar']);
    }

    /**
     * Загрузка файла недопустимого MIME-типа возвращает ошибку валидации.
     */
    public function test_register_fails_with_invalid_avatar_mime(): void
    {
        $response = $this->postJson('/api/register', [
            'nickname' => 'testuser',
            'avatar' => UploadedFile::fake()->create('document.pdf', 500, 'application/pdf'),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['avatar']);
    }

    /**
     * Загрузка аватара размером более 2 МБ возвращает ошибку валидации.
     */
    public function test_register_fails_with_oversized_avatar(): void
    {
        $response = $this->postJson('/api/register', [
            'nickname' => 'testuser',
            'avatar' => UploadedFile::fake()->image('big.jpg')->size(3000),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['avatar']);
    }

    /**
     * Никнейм с пробелами и спецсимволами возвращает ошибку валидации.
     */
    public function test_register_fails_with_invalid_nickname_characters(): void
    {
        $response = $this->postJson('/api/register', [
            'nickname' => 'invalid nickname!',
            'avatar' => UploadedFile::fake()->image('avatar.jpg', 100, 100),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['nickname']);
    }

    /**
     * Страница списка пользователей отдаёт 200.
     */
    public function test_users_list_page_returns_ok(): void
    {
        $this->get('/')->assertStatus(200);
    }

    /**
     * Зарегистрированный пользователь отображается на странице списка.
     */
    public function test_registered_user_appears_in_list(): void
    {
        $this->postJson('/api/register', [
            'nickname' => 'listuser',
            'avatar' => UploadedFile::fake()->image('avatar.jpg', 100, 100),
        ])->assertStatus(201);

        $this->get('/')->assertStatus(200)->assertSee('listuser');
    }
}
