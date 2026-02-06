<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\UpdatePasswordRequest;
use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Response;
use Inertia\ResponseFactory;

class PasswordResetController extends Controller
{
    public function __construct(
        private readonly ResponseFactory $inertia,
        private readonly PasswordBroker $passwordBroker,
        private readonly Hasher $hasher,
    ) {
    }

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse|RedirectResponse
    {
        $status = $this->passwordBroker->sendResetLink($request->validated());

        if ($request->wantsJson()) {
            return response()->json([
                'message' => __($status),
            ]);
        }

        return redirect()->route('password.request')->with('status', __($status));
    }

    public function showForgotPasswordForm(Request $request): Response
    {
        return $this->inertia->render('Auth/ForgotPassword', [
            'status' => $request->session()->get('status'),
        ]);
    }

    public function resetPassword(Request $request, string $token): Response
    {
        return $this->inertia->render('Auth/ResetPassword', [
            'email' => (string) $request->query('email', ''),
            'token' => $token,
        ]);
    }

    public function updatePassword(UpdatePasswordRequest $request): JsonResponse|RedirectResponse
    {
        $status = $this->passwordBroker->reset(
            $request->validated(),
            function ($user, string $password): void {
                $user->forceFill([
                    'password' => $this->hasher->make($password),
                    'remember_token' => Str::random(60),
                ])->save();
            },
        );

        if ($request->wantsJson()) {
            return response()->json([
                'message' => __($status),
            ], $status === PasswordBroker::PASSWORD_RESET ? 200 : 422);
        }

        if ($status === PasswordBroker::PASSWORD_RESET) {
            return redirect()->route('login')->with('status', __($status));
        }

        return redirect()->back()->withErrors([
            'email' => __($status),
        ]);
    }
}
