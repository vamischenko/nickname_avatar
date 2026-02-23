<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterUserRequest;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

/**
 * Контроллер пользователей.
 *
 * Обрабатывает регистрацию пользователя через API
 * и отображение HTML-страницы со списком всех зарегистрированных.
 */
class UserController
{
    public function __construct(private readonly UserService $userService)
    {
    }

    /**
     * Регистрирует нового пользователя.
     *
     * Принимает nickname и avatar, проверяет уникальность nickname,
     * сохраняет аватар на диск и данные пользователя в Redis.
     *
     * @param  RegisterUserRequest  $request  Валидированный запрос с nickname и avatar
     * @return JsonResponse 201 при успехе, 422 если nickname уже занят
     */
    public function register(RegisterUserRequest $request): JsonResponse
    {
        $nickname = $request->input('nickname');

        if ($this->userService->exists($nickname)) {
            return response()->json([
                'message' => 'Nickname already taken.',
                'errors' => ['nickname' => ['This nickname is already registered.']],
            ], 422);
        }

        $avatarPath = $request->file('avatar')->store('avatars', 'public');

        $user = $this->userService->create($nickname, $avatarPath);

        return response()->json([
            'message' => 'User registered successfully.',
            'data' => $user,
        ], 201);
    }

    /**
     * Отображает HTML-страницу со списком всех зарегистрированных пользователей.
     *
     * Пользователи передаются в шаблон отсортированными от новых к старым.
     *
     * @return View Шаблон users.index
     */
    public function index(): View
    {
        $users = $this->userService->all();

        return view('users.index', compact('users'));
    }
}
