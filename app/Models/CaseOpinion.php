<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CaseOpinion extends Model
{
    use HasFactory;

    public const CHOICES = ['natural' => 'An ordinary explanation', 'unexplained' => 'Still unexplained', 'undecided' => 'I need more evidence'];

    protected $fillable = ['choice', 'revision', 'user_id'];

    public function story(): BelongsTo
    {
        return $this->belongsTo(Story::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
