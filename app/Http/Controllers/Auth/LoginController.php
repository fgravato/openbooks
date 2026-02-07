<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Domains\Identity\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\Identity\UserResource;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Response;
use Inertia\ResponseFactory;
use Laravel\Fortify\TwoFactorAuthenticationProvider;

class LoginController extends Controller
{
    public function __construct(
        private readonly ResponseFactory $inertia,
        private readonly StatefulGuard $guard,
        private readonly Hasher $hasher,
        private readonly TwoFactorAuthenticationProvider $twoFactorAuthenticationProvider,
    ) {}

    public function showLoginForm(Request $request): Response
    {
        return $this->inertia->render('Auth/Login', [
            'status' => $request->session()->get('status'),
            'two_factor_required' => false,
        ]);
    }

    public function login(LoginRequest $request): JsonResponse|RedirectResponse
    {
        /** @var array{email: string, password: string, remember?: bool, totp_code?: string|null} $credentials */
        $credentials = $request->validated();

        $user = User::query()
            ->withoutGlobalScopes()
            ->where('email', Str::lower($credentials['email']))
            ->first();

        if (! $user instanceof User || ! $this->hasher->check($credentials['password'], (string) $user->password)) {
            return $this->failedAuthenticationResponse($request);
        }

        if ($this->requiresTwoFactorVerification($user, $credentials['totp_code'] ?? null)) {
            return $this->twoFactorRequiredResponse($request);
        }

        $this->guard->login($user, (bool) ($credentials['remember'] ?? false));

        $request->session()->regenerate();
        $user->forceFill(['last_login_at' => now()])->save();

        if ($request->wantsJson()) {
            return response()->json([
                'data' => UserResource::make($user->loadMissing('organization')),
            ]);
        }

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request): JsonResponse|RedirectResponse
    {
        $this->guard->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->wantsJson()) {
            return response()->json([
                'message' => __('Logged out.'),
            ]);
        }

        return redirect()->route('login');
    }

    private function failedAuthenticationResponse(Request $request): JsonResponse|RedirectResponse
    {
        if ($request->wantsJson()) {
            return response()->json([
                'message' => __('The provided credentials are incorrect.'),
            ], 422);
        }

        return redirect()->route('login')
            ->withErrors([
                'email' => __('The provided credentials are incorrect.'),
            ])
            ->withInput($request->safe()->only('email', 'remember'));
    }

    private function twoFactorRequiredResponse(Request $request): JsonResponse|RedirectResponse
    {
        if ($request->wantsJson()) {
            return response()->json([
                'message' => __('Two-factor authentication code is required.'),
                'two_factor_required' => true,
            ], 422);
        }

        return redirect()->route('login')
            ->withErrors([
                'totp_code' => __('A valid two-factor authentication code is required.'),
            ])
            ->withInput($request->safe()->only('email', 'remember'));
    }

    private function requiresTwoFactorVerification(User $user, ?string $totpCode): bool
    {
        if (! is_string($user->mfa_secret) || $user->mfa_secret === '') {
            return false;
        }

        if (! is_string($totpCode) || $totpCode === '') {
            return true;
        }

        return ! $this->twoFactorAuthenticationProvider->verify(decrypt($user->mfa_secret), $totpCode);
    }
}
