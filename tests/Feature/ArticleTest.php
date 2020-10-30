<?php

namespace Tests\Feature;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ArticleTest extends TestCase
{
    use DatabaseTransactions;
    use HasFactory;

    protected $createArticleUrl;
    protected $getArticleListUrl;
    protected $getDuplicateGroupListUrl;

    public function setUp(): void
    {
        parent::setUp();

        $this->getArticleListUrl = '/articles';
        $this->getDuplicateGroupListUrl = '/duplicate_groups';
        $this->createArticleUrl = '/articles';
    }

    public function test_create_article()
    {
        $data = [
            'content' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.'
        ];

        $response = $this->postJson($this->getArticleListUrl, $data);

        $response->assertStatus(201);

        $response->assertJsonFragment([
            'content' => $data['content'],
            'duplicate_article_ids' => [],
        ]);
    }

    public function test_create_article_without_content()
    {
        $response = $this->postJson($this->getArticleListUrl, ['content' => '']);

        $response->assertStatus(422);

        $response->assertSee(__('validation.required', [
            'attribute' => 'content',
        ]));
    }

    public function test_create_duplicate_article()
    {
        $data = [
            'content' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.'
        ];

        $originalArticle = createArticle($data);

        $response = $this->postJson($this->getArticleListUrl, $data);

        $response->assertStatus(201);

        $response->assertJsonFragment([
            'content' => $data['content'],
            'duplicate_article_ids' => [$originalArticle->id],
        ]);
    }

    public function test_create_modified_irregular_verb_duplicate_article()
    {
        $data1 = ['content' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.'];
        $data2 = ['content' => 'Lorem Ipsum was simply dummy text of the printing and typesetting industry.'];

        $originalArticle = createArticle($data1);

        $response = $this->postJson($this->getArticleListUrl, $data2);

        $response->assertStatus(201);

        $response->assertJsonFragment([
            'content' => $data2['content'],
            'duplicate_article_ids' => [$originalArticle->id],
        ]);
    }

    public function test_article_list()
    {

        $article1 = createArticle();
        $article2 = createArticle();
        $article3 = createArticle();

        $response = $this->get($this->getArticleListUrl);

        $response->assertStatus(200);

        $response->assertJson([
            'data' => [
                ['id' => $article1->id, 'content' => $article1->content],
                ['id' => $article2->id, 'content' => $article2->content],
                ['id' => $article3->id, 'content' => $article3->content],
            ]
        ]);
    }

    public function test_article_list_pagination()
    {

        $article1 = createArticle();
        $article2 = createArticle();
        $article3 = createArticle();

        $responsePage1 = $this->getJson($this->getArticleListUrl . '?per_page=2');
        $responsePage1->assertStatus(200);
        $responsePage1->assertJsonMissing(['data' => [['id' => $article3->id, 'content' => $article3->content]]]);
        $responsePage1->assertJson([
            'data' => [
                ['id' => $article1->id, 'content' => $article1->content],
                ['id' => $article2->id, 'content' => $article2->content],
            ],
        ]);

        $responsePage2 = $this->getJson($this->getArticleListUrl . '?per_page=2&page=2');
        $responsePage2->assertStatus(200);
        $responsePage2->assertJson(['data' => [['id' => $article3->id, 'content' => $article3->content]]]);
        $responsePage2->assertJsonMissing([
            'data' => [
                ['id' => $article1->id, 'content' => $article1->content],
                ['id' => $article2->id, 'content' => $article2->content],
            ],
        ]);
    }

    public function test_duplicate_group_list()
    {

        $data1 = ['content' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.'];
        $data2 = ['content' => 'Some other text here to check for duplication'];

        $response1 = $this->postJson($this->getArticleListUrl, $data1);
        $article1 = $response1->getOriginalContent();

        $response2 = $this->postJson($this->getArticleListUrl, $data1);
        $article2 = $response2->getOriginalContent();

        $response3 = $this->postJson($this->getArticleListUrl, $data1);
        $article3 = $response3->getOriginalContent();

        $response4 = $this->postJson($this->getArticleListUrl, $data2);
        $article4 = $response4->getOriginalContent();

        $response5 = $this->postJson($this->getArticleListUrl, $data2);
        $article5 = $response5->getOriginalContent();

        $response6 = $this->postJson($this->getArticleListUrl, $data2);
        $article6 = $response6->getOriginalContent();

        $response = $this->get($this->getDuplicateGroupListUrl);

        $response->assertStatus(200);

        $response->assertJson([
            'duplicate_groups' => [
                [$article1->id, $article2->id, $article3->id],
                [$article4->id, $article5->id, $article6->id],
            ],
        ]);
    }
}
