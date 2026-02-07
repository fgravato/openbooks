<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Domains\Identity\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\TwoFactorRequest;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Auth\SessionGuard;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use Inertia\ResponseFactory;
use Laravel\Fortify\RecoveryCode;
use Laravel\Fortify\TwoFactorAuthenticationProvider;

class TwoFactorAuthController extends Controller
{
    public function __construct(
        private readonly ResponseFactory $inertia,
        private readonly SessionGuard $guard,
        private readonly Hasher $hasher,
        private readonly TwoFactorAuthenticationProvider $twoFactorAuthenticationProvider,
    ) {}

    public function show2faForm(Request $request): Response
    {
        $user = $request->user();
        abort_unless($user instanceof User, 401);

        $secret = $request->session()->get('2fa.pending_secret');

        if (! is_string($secret) || $secret === '') {
            $secret = $this->twoFactorAuthenticationProvider->generateSecretKey();
            $request->session()->put('2fa.pending_secret', $secret);
        }

        return $this->inertia->render('Auth/TwoFactorSetup', [
            'enabled' => is_string($user->mfa_secret) && $user->mfa_secret !== '',
            'qr_code_svg' => $this->generateQrCodeSvg($this->twoFactorAuthenticationProvider->qrCodeUrl(
                config('app.name', 'OpenBooks'),
                $user->email,
                $secret,
            )),
            'backup_codes' => $request->session()->get('2fa.backup_codes', []),
        ]);
    }

    public function enable2fa(TwoFactorRequest $request): JsonResponse|RedirectResponse
    {
        $user = $request->user();
        abort_unless($user instanceof User, 401);

        $secret = (string) $request->session()->get('2fa.pending_secret', '');

        if ($secret === '' || ! $this->twoFactorAuthenticationProvider->verify($secret, $request->validated('totp_code'))) {
            return $this->twoFactorValidationFailed($request);
        }

        $backupCodes = array_map(
            static fn (): string => RecoveryCode::generate(),
            range(1, 8),
        );
        $request->session()->put('2fa.backup_codes', $backupCodes);

        $user->forceFill([
            'mfa_secret' => encrypt($secret),
        ])->save();

        $request->session()->forget('2fa.pending_secret');

        if ($request->wantsJson()) {
            return response()->json([
                'message' => __('Two-factor authentication enabled.'),
                'backup_codes' => $backupCodes,
            ]);
        }

        return redirect()->route('2fa.setup')->with('success', __('Two-factor authentication enabled.'));
    }

    public function disable2fa(TwoFactorRequest $request): JsonResponse|RedirectResponse
    {
        $user = $request->user();
        abort_unless($user instanceof User, 401);

        if (! $this->hasher->check((string) $request->validated('password'), (string) $user->password)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => __('Password confirmation is invalid.'),
                ], 422);
            }

            return redirect()->route('2fa.setup')->withErrors([
                'password' => __('Password confirmation is invalid.'),
            ]);
        }

        $user->forceFill([
            'mfa_secret' => null,
        ])->save();

        $request->session()->forget('2fa.backup_codes');

        if ($request->wantsJson()) {
            return response()->json([
                'message' => __('Two-factor authentication disabled.'),
            ]);
        }

        return redirect()->route('2fa.setup')->with('success', __('Two-factor authentication disabled.'));
    }

    public function verify2fa(TwoFactorRequest $request): JsonResponse|RedirectResponse
    {
        $user = $this->guard->user();

        if (! $user instanceof User || ! is_string($user->mfa_secret) || $user->mfa_secret === '') {
            abort(422, __('Two-factor authentication is not enabled.'));
        }

        $valid = $this->twoFactorAuthenticationProvider->verify(
            decrypt($user->mfa_secret),
            $request->validated('totp_code'),
        );

        if (! $valid) {
            return $this->twoFactorValidationFailed($request);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'message' => __('Two-factor authentication verified.'),
            ]);
        }

        return redirect()->route('dashboard');
    }

    private function twoFactorValidationFailed(Request $request): JsonResponse|RedirectResponse
    {
        if ($request->wantsJson()) {
            return response()->json([
                'message' => __('Invalid two-factor authentication code.'),
            ], 422);
        }

        return redirect()->route('2fa.setup')->withErrors([
            'totp_code' => __('Invalid two-factor authentication code.'),
        ]);
    }

    private function generateQrCodeSvg(string $contents): string
    {
        $writer = new Writer(
            new ImageRenderer(
                new RendererStyle(220),
                new SvgImageBackEnd,
            ),
        );

        return $writer->writeString($contents);
    }
}
