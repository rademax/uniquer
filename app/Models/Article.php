<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{

    use HasFactory;

    protected $fillable = [
        'content',
        'is_original',
    ];

    public function scopeOriginal($query)
    {

        return $query->where('is_original', true);
    }

    public function getDuplicateIds()
    {

        return $this->duplicates()->pluck('id');
    }

    public function duplicates()
    {

        return $this->belongsToMany(Article::class, 'duplicates', 'article_id', 'duplicate_id');
    }
}
