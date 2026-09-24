<?php

namespace RomanSulzhyk\FilamentImport\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** team_id is fillable, as it often is in real apps, which is exactly the risk. */
class TeamCustomer extends Model
{
    protected $fillable = ['team_id', 'name', 'email'];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
