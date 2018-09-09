<?php

namespace Modules\Shop\Http\Controllers;

use Modules\Article\Entities\Article;
use Illuminate\Support\Facades\Request;
use Modules\Core\Http\Controllers\BasePublicController;
use Modules\Article\Repositories\ArticleRepository;

/**
 * ArticleController
 */
class ArticleController extends BasePublicController
{
    /**
     * @var ArticleRepository
     */
    private $article;

    /**
     * @param ArticleRepository $article
     */
    public function __construct(ArticleRepository $article)
    {
        parent::__construct();

        $this->article = $article;
    }

    /**
     * Article View
     * @param  Request $request
     * @return \Illuminate\View\View
     */
    public function articles(Request $request)
    {
        return view('shop.article.index');
    }

    /**
     * Article View
     * @param  Article $article
     * @param  Request $request
     * @return \Illuminate\View\View
     */
    public function articleView(Article $article, Request $request)
    {
        return view('shop.article.view', compact('article'));
    }

}
