<?php

namespace App\Http\Controllers\Api;

use App\helpers\Shingler;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Article\StoreRequest;
use App\Http\Requests\Api\Base\IndexRequest;
use App\Http\Resources\ArticleResource;
use App\Models\Article;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ArticleController extends Controller
{

    protected $uniquePercent;

    protected $shingleLength;

    public function __construct()
    {
        $this->uniquePercent = config('shingler.unique_percent');
        $this->shingleLength = config('shingler.shingle_length');
    }

    /**
     * Display a listing of the resource.
     *
     * @param IndexRequest $request
     * @return AnonymousResourceCollection
     */
    public function index(IndexRequest $request)
    {
        $articles = Article::original()->paginate($request->getPerPage());

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

        $articles = Article::where('id', '!=', $article->id)->get();

        $duplicates = [];
        foreach ($articles as $originalArticle) {
            $shingler = new Shingler($this->shingleLength);

            $duplicatePercent = $shingler->compare($article->content, $originalArticle->content);

            if ($duplicatePercent >= $this->uniquePercent) {
                $duplicates[] = $originalArticle->id;
                $originalArticle->duplicates()->attach($article->id);
            }
        }

        if (count($duplicates)) {
            $article->duplicates()->sync($duplicates);
            $article->is_original = false;
            $article->save();
        }

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

    public function duplicateGroups()
    {

        $articles = Article::original()->paginate();

        $duplicateGroups = [];

        foreach ($articles as $article) {
            $group = $article->getDuplicateIds()->toArray();
            array_unshift($group, $article->id);
            $duplicateGroups[] = $group;
        }

        return response()->json([
            'duplicate_groups' => $duplicateGroups,
        ], 200);
    }
}
