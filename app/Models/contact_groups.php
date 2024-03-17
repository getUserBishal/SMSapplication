<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class contact_groups extends Model
{
    use HasFactory;

    // protected $fillable = [
    //     'name',
    //     'description',
    // ];

    public function contacts()
    {
        return $this->hasMany(contacts::class,'group_id');
    }
}