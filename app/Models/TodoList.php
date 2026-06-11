<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TodoList extends Model
{
    protected $table = "todo_lists";
    protected $fillable = [
        'user_id',
        'customer_id',
        'created_by',
        'todo_type',
        'todo_date',
        'deposit_date',
        'remark',
    ];
    public function customer()
    {
      return $this->belongsTo(User::class, 'customer_id', 'id');
    }
     public function staff()
    {
      return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
