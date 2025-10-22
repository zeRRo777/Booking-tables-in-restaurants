<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;

class RestaurantChain extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = ['name', 'status_id'];

    public function superAdmins(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'chain_super_admins');
    }

    public function restaurants(): HasMany
    {
        return $this->hasMany(Restaurant::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(ChainStatuse::class);
    }

    #[Scope]
    public function active(Builder $query): void
    {
        $query->whereHas('status', fn($q) => $q->where('name', 'active'));
    }

    #[Scope]
    public function forUser(Builder $query, ?User $user): void
    {
        if (!$user || !$user->hasAnyRole(['superadmin', 'admin_chain'])) {
            $query->active();
            return;
        }

        if ($user->hasRole('superadmin')) {
            return;
        }

        if ($user->hasRole('admin_chain')) {
            $query->where(function (Builder $q) use ($user) {
                $q->active()
                    ->orWhereHas('superAdmins', fn($adminQuery) => $adminQuery->where('user_id', $user->id));
            });
        }
    }
}
