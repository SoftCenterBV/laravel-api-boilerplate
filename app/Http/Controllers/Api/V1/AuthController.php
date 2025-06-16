<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Requests\Api\V1\Auth\VerifyMfaRequest;
use App\Http\Requests\Api\V1\Auth\VerifySetupMfaRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use PragmaRX\Google2FA\Exceptions\IncompatibleWithGoogleAuthenticatorException;
use PragmaRX\Google2FA\Exceptions\InvalidCharactersException;
use PragmaRX\Google2FA\Exceptions\SecretKeyTooShortException;
use PragmaRX\Google2FA\Google2FA;

class AuthController extends Controller
{
    public function login(LoginRequest $request): \Illuminate\Http\JsonResponse
    {
        $user = User::query()
            ->where('email', $request->input('email'))
            ->first();

        if (! $user || ! password_verify($request->input('password'), $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }
        if (! $user->mfa_secret) {
            return response()->json([
                'message' => 'Login successful',
                'token' => $user->createToken('User Token')->plainTextToken,
                'user' => $user->toArray(),
            ]);
        }

        $mfaSessionToken = bin2hex(random_bytes(32));
        Cache::put("mfa_session_{$mfaSessionToken}", $user->id, 300); // Store for 5 minutes


        return response()->json([
            'message' => 'MFA required',
            'mfaSessionToken' => $mfaSessionToken,
        ]);
    }

    /**
     * @throws IncompatibleWithGoogleAuthenticatorException
     * @throws InvalidCharactersException
     * @throws SecretKeyTooShortException
     */
    public function verifyMfa(VerifyMfaRequest $request): \Illuminate\Http\JsonResponse
    {
        $userId = Cache::get("mfa_session_{$request->mfaSessionToken}");
        if (!$userId) {
            return response()->json(['message' => 'Invalid or expired MFA session token'], 400);
        }

        $user = User::query()->find($userId);

        $mfaValidation = new Google2FA();
        $isValid = $mfaValidation->verifyKey(Crypt::decrypt($user->mfa_secret), $request->mfaCode);
        if (!$isValid) {
            return response()->json(['message' => 'Invalid MFA code'], 401);
        }
        // Clear the MFA session token
        Cache::forget("mfa_login_{$request->mfa_token}");

        return response()->json([
            'message' => 'Login successful',
            'token' => $user->createToken('User Token')->plainTextToken,
            'user' => $user->toArray(),
        ]);
    }

    public function logout(): \Illuminate\Http\JsonResponse
    {
        Auth::user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout successful',
        ])->setStatusCode(200);
    }

    public function initMfaSetup() : \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        if ($user->mfa_secret) {
            return response()->json(['message' => 'MFA already enabled'], 400);
        }

        $mfaSetup = new Google2FA();
        $secret = $mfaSetup->generateSecretKey();

        $company = config('app.name');
        $email = $user->email;

        $qrUrl = $mfaSetup->getQRCodeUrl(
            $company,
            $email,
            $secret
        );

        cache()->put("mfa_setup_{$user->id}", $secret, now()->addMinutes(10));

        return response()->json([
            'message' => 'MFA setup initialized',
            'secret' => $secret,
            'qrCodeUrl' => $qrUrl,
        ])->setStatusCode(200);
    }

    public function verifyMfaSetup(VerifySetupMfaRequest $request): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        $secret = cache()->get("mfa_setup_{$user->id}");

        if (! $secret) {
            return response()->json(['message' => 'No MFA setup in progress'], 400);
        }

        $mfaSetup = new Google2FA();

        if (! $mfaSetup->verifyKey($secret, $request->code)) {
            return response()->json(['message' => 'Invalid MFA code'], 401);
        }

        // Save the secret (encrypted for security)
        $user->mfa_secret = Crypt::encrypt($secret);
        $user->save();

        // Clean up
        cache()->forget("mfa_setup_{$user->id}");

        return response()->json(['message' => 'MFA enabled successfully']);
    }
}
