<?php

namespace App\Auth;

use App\Models\PersonalAccessToken;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;

class TokenGuard implements Guard
{
    protected ?Authenticatable $user = null;

    public function __construct(protected Request $request) {}

    public function check(): bool
    {
        return !is_null($this->user());
    }

    public function guest(): bool
    {
        return ! $this->check();
    }

    public function user(): ?Authenticatable
    {
        $plainTextToken = $this->request->bearerToken();

        if (! $plainTextToken){
            return $this->user = null;
        }

        $token = PersonalAccessToken::findValidToken($plainTextToken);

        if (! $token) {
            return $this->user = null;
        }

        $token->forceFill([
            'last_used_at' => now(),
        ])->save();

        return $this->user = $token->user;
    }

    public function id(): int|string|null
    {
        return $this->user()?->getAuthIdentifier();
    }

    public function validate(array $credentials = []): bool
    {
        return false;
    }

    public function hasUser(): bool
    {
        return !is_null($this->user);
    }

    public function setUser(Authenticatable $user): void
    {
        $this->user = $user;
    }
}
