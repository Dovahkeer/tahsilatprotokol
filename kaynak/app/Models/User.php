<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

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
        ];
    }

    public function yetkiKaydi(): HasOne
    {
        return $this->hasOne(TahsilatYetkiliKullanici::class);
    }

    public function protokoller(): HasMany
    {
        return $this->hasMany(Protokol::class, 'created_by');
    }

    public function tahsilatlar(): HasMany
    {
        return $this->hasMany(Tahsilat::class, 'created_by');
    }

    public function isAdmin(): bool
    {
        return (bool) $this->is_admin;
    }

    public function isYonetici(): bool
    {
        return $this->isAdmin();
    }

    public function tabPermissions(?array $tabKeys = null): array
    {
        $tabKeys ??= collect(config('tahsilat.tab_tanimlari', []))
            ->pluck('key')
            ->all();

        if ($this->isAdmin()) {
            return collect($tabKeys)
                ->mapWithKeys(fn (string $key) => [$key => true])
                ->all();
        }

        $storedPermissions = is_array($this->yetkiKaydi?->tab_permissions)
            ? $this->yetkiKaydi->tab_permissions
            : [];

        return collect($tabKeys)
            ->mapWithKeys(fn (string $key) => [$key => (bool) ($storedPermissions[$key] ?? false)])
            ->all();
    }

    public function canAccessTab(string $tabKey): bool
    {
        return (bool) ($this->tabPermissions([$tabKey])[$tabKey] ?? false);
    }
}
