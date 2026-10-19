<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TopUp extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $casts = ['tanggal' => 'date'];

    public static function saldo(): float
    {
        return (float) static::sum('jumlah') - (float) \App\Models\Expense::sum('jumlah');
    }
}
