<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Goal extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'deadline',
        'status',
        'category_id'
    ];

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
