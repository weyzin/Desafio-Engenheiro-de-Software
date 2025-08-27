<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;

class Vehicle extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id','brand','model','version','year','km','price','status','notes','images_json',
        'created_by','updated_by','deleted_by',
    ];

    protected $casts = [
        'price'       => 'float',
        'km'          => 'integer',
        'images_json' => 'array',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
        'deleted_at'  => 'datetime',
    ];

    public function getImagesAttribute(): array
    {
        return $this->images_json ?? [];
    }

    public function setImagesAttribute($value): void
    {
        $this->images_json = $value ?: [];
    }
}
