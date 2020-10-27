<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{

    protected $fillable = [
        'content',
        'is_original',
        'duplicate_article_ids',
    ];

    public function scopeOriginal($query)
    {

        return $query->where('original', true);
    }

    public function duplicates()
    {

    }
}
