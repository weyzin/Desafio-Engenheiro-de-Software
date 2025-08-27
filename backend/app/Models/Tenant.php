<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Tenant extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['name','slug'];

    protected static function booted()
    {
        static::creating(function (Tenant $t) {
            if (empty($t->id)) {
                $t->id = (string) Str::uuid();
            }
            // normaliza slug
            $t->slug = strtolower($t->slug);
        });

        static::updating(function (Tenant $t) {
            if (!empty($t->slug)) {
                $t->slug = strtolower($t->slug);
            }
        });
    }
}
