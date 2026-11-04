<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'username', 'email', 'password', 'role'])]
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
        ];
    }

    public function dokter(): HasOne
    {
        return $this->hasOne(Dokter::class);
    }

    public function notificationLogs(): HasMany
    {
        return $this->hasMany(NotificationLog::class, 'recipient_user_id');
    }

    public function unreadNotificationLogs(): HasMany
    {
        return $this->hasMany(NotificationLog::class, 'recipient_user_id')
            ->where('status', 'sent');
    }

    public function isAdminOrSuperAdmin(): bool
    {
        return in_array($this->role, ['super_admin', 'admin_sdm'], true);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isDokter(): bool
    {
        return $this->role === 'dokter';
    }

    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            'super_admin' => 'Super Admin (IT / Manajemen)',
            'admin_sdm' => 'Admin SDM (Kepegawaian)',
            'dokter' => 'Dokter (Staf Medis)',
            default => ucfirst($this->role),
        };
    }
}
