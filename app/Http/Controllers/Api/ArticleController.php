<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Article\StoreRequest;
use App\Http\Resources\ArticleResource;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ArticleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return AnonymousResourceCollection
     */
    public function index()
    {
        $articles = Article::original()->get();

        return ArticleResource::collection($articles);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreRequest $request
     * @return ArticleResource
     */
    public function store(StoreRequest $request)
    {
        $article = new Article($request->validated());
        $article->save();

        return new ArticleResource($article);
    }

    /**
     * Display the specified resource.
     *
     * @param Article $article
     * @return ArticleResource
     */
    public function show(Article $article)
    {

        return new ArticleResource($article);
    }
}
