<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterUserRequest;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(private readonly UserService $userService) {}

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

    public function index(): View
    {
        $users = $this->userService->all();

        return view('users.index', compact('users'));
    }
}
