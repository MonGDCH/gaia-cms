<?php
/*
|--------------------------------------------------------------------------
| 定义应用请求路由
|--------------------------------------------------------------------------
| 通过Route类进行注册
|
*/

use mon\http\Route;
use plugins\cms\controller\AdController;
use app\admin\middleware\AuthMiddleware;
use app\admin\middleware\LoginMiddleware;
use plugins\cms\controller\CateController;
use plugins\cms\controller\PageController;
use plugins\cms\controller\ArticleController;
use plugins\cms\controller\CommentController;


Route::instance()->group(Config::instance()->get('admin.app.root_path', ''), function (Route $route) {
    // 权限验证
    $route->group(['path' => '/cms', 'middleware' => [LoginMiddleware::class, AuthMiddleware::class]], function (Route $route) {
        // 区块广告
        $route->group('/ad', function (Route $route) {
            // 列表
            $route->get('', [AdController::class, 'index']);
            // 新增
            $route->map(['GET', 'POST'], '/add', [AdController::class, 'add']);
            // 编辑
            $route->map(['GET', 'POST'], '/edit', [AdController::class, 'edit']);
            // 区块资源
            $route->group('/assets', function (Route $route) {
                // 列表
                $route->get('', [AdController::class, 'assets']);
                // 新增
                $route->map(['GET', 'POST'], '/add', [AdController::class, 'addAssets']);
                // 编辑
                $route->map(['GET', 'POST'], '/edit', [AdController::class, 'editAssets']);
            });
        });

        // 栏目分类
        $route->group('/cate', function (Route $route) {
            // 列表
            $route->get('', [CateController::class, 'index']);
            // 新增
            $route->map(['GET', 'POST'], '/add', [CateController::class, 'add']);
            // 编辑
            $route->map(['GET', 'POST'], '/edit', [CateController::class, 'edit']);
        });

        // 文章
        $route->group('/article', function (Route $route) {
            // 列表
            $route->get('', [ArticleController::class, 'index']);
            // 新增
            $route->map(['GET', 'POST'], '/add', [ArticleController::class, 'add']);
            // 编辑
            $route->map(['GET', 'POST'], '/edit', [ArticleController::class, 'edit']);
            // 互动信息
            $route->map(['GET', 'POST'], '/interact', [ArticleController::class, 'interact']);
            // 展示信息
            $route->map(['GET', 'POST'], '/displays', [ArticleController::class, 'displays']);
            // 查看文章信息
            $route->get('/detail', [ArticleController::class, 'detail']);
            // 预览
            $route->get('/preview', [ArticleController::class, 'preview']);
            // 修改状态
            $route->post('/toggle', [ArticleController::class, 'toggle']);
        });

        // 独立页面
        $route->group('/page', function (Route $route) {
            // 列表
            $route->get('', [PageController::class, 'index']);
            // 新增
            $route->map(['GET', 'POST'], '/add', [PageController::class, 'add']);
            // 编辑
            $route->map(['GET', 'POST'], '/edit', [PageController::class, 'edit']);
            // 互动信息
            $route->map(['GET', 'POST'], '/interact', [PageController::class, 'interact']);
            // 展示信息
            $route->map(['GET', 'POST'], '/displays', [PageController::class, 'displays']);
            // 预览
            $route->get('/preview', [PageController::class, 'preview']);
            // 修改状态
            $route->post('/toggle', [PageController::class, 'toggle']);
        });

        // 评论
        $route->group('/comment', function (Route $route) {
            // 列表
            $route->get('', [CommentController::class, 'index']);
            // 查看
            $route->get('/preview', [CommentController::class, 'preview']);
            // 新增
            // $route->map(['GET', 'POST'], '/add', [CommentController::class, 'add']);
            // 编辑
            $route->map(['GET', 'POST'], '/edit', [CommentController::class, 'edit']);
            // 互动信息
            $route->map(['GET', 'POST'], '/interact', [CommentController::class, 'interact']);
            // 展示信息
            $route->map(['GET', 'POST'], '/displays', [CommentController::class, 'displays']);
            // 修改状态
            $route->post('/toggle', [CommentController::class, 'toggle']);
        });
    });
});
