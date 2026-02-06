<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\Fortify\CreateNewUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\Identity\UserResource;
use Illuminate\Auth\SessionGuard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use Inertia\ResponseFactory;

class RegisterController extends Controller
{
    public function __construct(
        private readonly ResponseFactory $inertia,
        private readonly CreateNewUser $createNewUser,
        private readonly SessionGuard $guard,
    ) {
    }

    public function showRegistrationForm(Request $request): Response
    {
        return $this->inertia->render('Auth/Register', [
            'status' => $request->session()->get('status'),
        ]);
    }

    public function register(RegisterRequest $request): JsonResponse|RedirectResponse
    {
        $user = $this->createNewUser->create($request->validated());

        $this->guard->login($user);
        $request->session()->regenerate();

        if ($request->wantsJson()) {
            return response()->json([
                'data' => UserResource::make($user->loadMissing('organization')),
            ], 201);
        }

        return redirect()->route('dashboard');
    }
}
