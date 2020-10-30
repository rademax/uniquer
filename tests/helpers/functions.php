<?php

use App\Models\Article;

function createArticle($attributes = [])
{

    return Article::factory()->create($attributes);
}
