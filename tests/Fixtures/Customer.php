<?php

namespace RomanSulzhyk\FilamentImport\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = ['name', 'email', 'phone', 'city', 'joined_at'];

    protected $casts = ['joined_at' => 'date'];
}
