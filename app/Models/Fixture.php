<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class Fixture extends Model
{
    /** @use HasFactory<\Database\Factories\FixtureFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'matchday',
        'home_team_id',
        'away_team_id',
        'home_goals',
        'away_goals',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'home_goals' => 'integer',
            'away_goals' => 'integer',
            'matchday' => 'integer',
        ];
    }

    protected $appends = [
        'is_completed',
    ];

    public function homeTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'home_team_id');
    }

    public function awayTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'away_team_id');
    }

    public function getIsCompletedAttribute(): bool
    {
        return $this->status === 'completed'
            && $this->home_goals !== null
            && $this->away_goals !== null;
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }
}
