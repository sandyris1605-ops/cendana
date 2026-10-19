<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $casts = ['tanggal' => 'date'];
    public function category() { return $this->belongsTo(ExpenseCategory::class, 'expense_category_id'); }
    public function items() { return $this->hasMany(ExpenseItem::class); }
    public function purchase() { return $this->hasOne(Purchase::class); }
    public function maintenance() { return $this->hasOne(Maintenance::class); }
}
