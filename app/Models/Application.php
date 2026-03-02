<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'dob',
        'phone',
        'position',
        'employment',
        'address',
        'city',
        'state',
        'zip',
        'license_path',
        'resume_path',
        'signature',
        'terms_accepted',

    ];
}
