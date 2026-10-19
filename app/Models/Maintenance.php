<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Maintenance extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $casts = ['tanggal' => 'date'];
    public function room() { return $this->belongsTo(Room::class); }
    public function expense() { return $this->belongsTo(Expense::class); }
}
