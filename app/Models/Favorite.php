<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Favorite extends Model
{
    use HasFactory;

    /**
     * Атрибуты, которые можно массово назначать.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'track_id',
    ];

    /**
     * Получить пользователя, которому принадлежит избранный трек.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Получить трек, который добавлен в избранное.
     */
    public function track(): BelongsTo
    {
        return $this->belongsTo(Track::class);
    }
} 