<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    protected $table = 'tenants';
    public $incrementing = false; // UUID
    protected $keyType = 'string';

    protected $fillable = ['id', 'name', 'slug'];

    public function domains(): HasMany
    {
        return $this->hasMany(TenantDomain::class);
    }
}
