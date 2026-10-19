<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'is_admin', 'plan', 'usage_count', 'usage_period'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'usage_count' => 'integer',
        ];
    }

    public function currentPeriod(): string
    {
        return now()->format('Y-m');
    }

    public function ensureUsagePeriod(): void
    {
        $period = $this->currentPeriod();

        if ($this->usage_period === $period) {
            return;
        }

        $this->forceFill([
            'usage_count' => 0,
            'usage_period' => $period,
        ])->save();
    }

    public function planConfig(): ?array
    {
        if (! $this->plan) {
            return null;
        }

        return config('plans.'.$this->plan);
    }

    public function isUnlimited(): bool
    {
        if ($this->is_admin && ! $this->plan) {
            return true;
        }

        // Special chat-only account: no package, no monthly cap.
        return strcasecmp((string) $this->email, 'admin@admin.com') === 0;
    }

    public function planName(): string
    {
        if ($this->planConfig()) {
            return (string) $this->planConfig()['name'];
        }

        if ($this->isUnlimited()) {
            return $this->is_admin ? 'Admin' : 'غير محدود';
        }

        return 'بدون باقة';
    }

    public function monthlyLimit(): ?int
    {
        if ($this->isUnlimited()) {
            return null;
        }

        $limit = $this->planConfig()['monthly_limit'] ?? null;

        return $limit !== null ? (int) $limit : null;
    }

    public function canUseSearch(): bool
    {
        return $this->isUnlimited() || (bool) $this->plan;
    }

    public function usedRecords(): int
    {
        $this->ensureUsagePeriod();

        return (int) $this->usage_count;
    }

    public function remainingRecords(): ?int
    {
        $limit = $this->monthlyLimit();

        if ($limit === null) {
            return null;
        }

        return max(0, $limit - $this->usedRecords());
    }

    public function usagePercent(): int
    {
        $limit = $this->monthlyLimit();

        if ($limit === null || $limit <= 0) {
            return 0;
        }

        return (int) min(100, round(($this->usedRecords() / $limit) * 100));
    }

    public function hasReachedLimit(): bool
    {
        $remaining = $this->remainingRecords();

        return $remaining !== null && $remaining <= 0;
    }

    public function recordUsage(int $count = 1): void
    {
        if ($count <= 0) {
            return;
        }

        $this->ensureUsagePeriod();
        $this->increment('usage_count', $count);
        $this->refresh();
    }

    /**
     * @return array{plan: string|null, plan_name: string, used: int, limit: int|null, remaining: int|null, percent: int}
     */
    public function usageSummary(): array
    {
        return [
            'plan' => $this->plan,
            'plan_name' => $this->planName(),
            'used' => $this->usedRecords(),
            'limit' => $this->monthlyLimit(),
            'remaining' => $this->remainingRecords(),
            'percent' => $this->usagePercent(),
        ];
    }
}
