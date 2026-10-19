<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;
    protected $guarded = [];
    public static function get($key, $default = null) {
        return static::where('key', $key)->value('value') ?? $default;
    }
    public static function set($key, $value) {
        return static::updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
