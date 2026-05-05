<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = [
        'owner_id',
        'title',
        'content_html',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function shares()
    {
        return $this->hasMany(DocumentShare::class);
    }

    public function sharedUsers()
    {
        return $this->belongsToMany(User::class, 'document_shares')
            ->withPivot('permission')
            ->withTimestamps();
    }
}