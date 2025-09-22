<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Employee extends Model
{
    protected $fillable = [
        'name',
        'cover_photo_path',
        'description',
        'project_id',
        'group',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
