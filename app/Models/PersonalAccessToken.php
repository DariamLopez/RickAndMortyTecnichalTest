<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class PersonalAccessToken extends Model
{
    protected $fillable = ['user_id', 'name', 'token', 'last_used_at', 'expires_at'];

    protected function casts(): array
    {
        return [
            'last_used_at' => 'datetime',
            'expires_at' => 'datetime'
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public static function generateFor(User $user, string $name, ?DateTimeInterface $expires_at = null): array
    {
        $plainTextToken = Str::random(64);

        $model = self::create([
            'user_id' => $user->id,
            'name' => $name,
            'token' => hash('sha256', $plainTextToken),
            'expires_at' => $expires_at
        ]);

        return [$model, $plainTextToken];
    }

    public static function findValidToken(string $plainTextToken): ?self
    {
        $token = self::where('token', hash('sha256', $plainTextToken))->first();

        if (!$token || $token->isExpired()) {
            return null;
        }
        return $token;
    }
}
