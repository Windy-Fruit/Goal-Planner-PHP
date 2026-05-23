<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Goal extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'deadline',
        'status',
    ];

    protected $casts = [
        'deadline' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'goal_category');
    }

    public function getProgressAttribute(): int
    {
        $total = $this->tasks()->count();

        if ($total === 0) {
            return 0;
        }

        $completed = $this->tasks()->where('is_completed', true)->count();

        return (int) round($completed / $total * 100);
    }
}
