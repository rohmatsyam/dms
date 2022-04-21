<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;
    protected $fillable = [
        'desc',
    ];

    public function role()
    {
        // (Model relasi, tujuan, local)
        return $this->hasMany(User::class, 'role_id', 'id');
    }
}
