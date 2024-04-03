<?php

declare(strict_types=1);

namespace plugins\cms\controller;

use mon\util\Tree;
use mon\http\Request;
use mon\http\Response;
use plugins\cms\dao\CateDao;
use plugins\cms\dao\CommentDao;
use plugins\cms\dao\ArticleDao;
use plugins\admin\comm\Controller;
use plugins\admin\comm\view\Template;
use plugins\cms\contract\ArticleEmun;
use plugins\cms\contract\CommentEmun;

/**
 * 内容管理控制器
 * 
 * @author Mon <985558837@qq.com>
 * @version 1.0.0
 */
class ArticleController extends Controller
{
    /**
     * 列表
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        if ($request->get('getCate') == '1') {
            $cateData = CateDao::instance()->field(['id', 'pid', 'title'])->order('sort', 'DESC')->all();
            $cateTree = Tree::instance()->data($cateData)->getTree('children');
            return $this->success('ok', $cateTree);
        }
        if ($request->get('isApi')) {
            $option = $request->get();
            $result = ArticleDao::instance()->getList($option);
            return $this->success('操作成功', $result['list'], ['count' => $result['count']]);
        };

        return $this->fetch('article/index', [
            'uid' => $request->uid,
            'status' => ArticleEmun::ARTICLE_STATUS_TITLE
        ]);
    }

    /**
     * 新增文章
     *
     * @param Request $request
     * @return mixed
     */
    public function add(Request $request)
    {
        if ($request->isPost()) {
            $option = $request->post();
            $save = ArticleDao::instance()->add($option, 0, $request->uid);
            if (!$save) {
                return $this->error(ArticleDao::instance()->getError());
            }
            return $this->success('操作成功');
        }

        // 输出栏目分类树select
        $cate = CateDao::instance()->getTreeList(0);
        return $this->fetch('article/add', ['cate' => json_encode($cate, JSON_UNESCAPED_UNICODE)]);
    }

    /**
     * 编辑文章
     *
     * @param Request $request
     * @return mixed
     */
    public function edit(Request $request)
    {
        if ($request->isPost()) {
            $option = $request->post();
            $save = ArticleDao::instance()->edit($option, 0, $request->uid);
            if (!$save) {
                return $this->error(ArticleDao::instance()->getError());
            }
            return $this->success('操作成功');
        }
        $id = $request->get('idx');
        if (!check('id', $id)) {
            return $this->error('params faild');
        }

        $info = ArticleDao::instance()->getInfo(intval($id));
        if (!$info) {
            return $this->error('文章不存在');
        }
        // 输出栏目分类树select
        $cate = CateDao::instance()->getTreeList($info['id']);
        return $this->fetch('article/edit', ['data' => $info, 'cate' => $cate]);
    }

    /**
     * 编辑展示信息
     *
     * @param Request $request
     * @return mixed
     */
    public function displays(Request $request)
    {
        if ($request->isPost()) {
            $option = $request->post();
            $save = ArticleDao::instance()->displays($option, $request->uid);
            if (!$save) {
                return $this->error(ArticleDao::instance()->getError());
            }
            return $this->success('操作成功');
        }

        $id = $request->get('idx');
        if (!check('id', $id)) {
            return $this->error('params faild');
        }

        $info = ArticleDao::instance()->getInfo(intval($id));
        if (!$info) {
            return $this->error('文章不存在');
        }

        return $this->fetch('article/displays', ['data' => $info]);
    }

    /**
     * 交互信息
     *
     * @param Request $request
     * @return mixed
     */
    public function interact(Request $request)
    {
        if ($request->isPost()) {
            $option = $request->post();
            $save = ArticleDao::instance()->interact($option, $request->uid);
            if (!$save) {
                return $this->error(ArticleDao::instance()->getError());
            }
            return $this->success('操作成功');
        }

        $id = $request->get('idx');
        if (!check('id', $id)) {
            return $this->error('params faild');
        }

        $info = ArticleDao::instance()->getInfo(intval($id));
        if (!$info) {
            return $this->error('文章不存在');
        }

        return $this->fetch('article/interact', ['data' => $info]);
    }

    /**
     * 查看详情
     *
     * @param Request $request
     * @return Response
     */
    public function detail(Request $request)
    {
        $id = $request->get('idx');
        if (!check('id', $id)) {
            return $this->error('params faild');
        }

        $info = ArticleDao::instance()->getInfo(intval($id));
        if (!$info) {
            return $this->error('文章不存在');
        }

        // 图片地址
        $info['img_url'] = $info['img'] ? Template::buildAssets($info['img']) : '';
        // 评论数
        $commentCount = CommentDao::instance()->where(['module' => CommentEmun::COMMENT_MODULE['doc'], 'union_id' => $id])->count('id');
        $info['comment_count'] = $commentCount;

        return $this->fetch('article/detail', ['data' => $info]);
    }

    /**
     * 预览
     *
     * @param Request $request
     * @return mixed
     */
    public function preview(Request $request)
    {
        $id = $request->get('idx');
        if (!check('id', $id)) {
            return $this->error('params faild');
        }

        $info = ArticleDao::instance()->getInfo(intval($id));
        if (!$info) {
            return $this->error('文章不存在');
        }

        dump($info);

        return new Response(200, [], '功能暂未开发');
    }

    /**
     * 修改状态
     *
     * @param Request $request
     * @return mixed
     */
    public function toggle(Request $request)
    {
        $id = $request->post('idx');
        if (!check('id', $id)) {
            return $this->error('params faild');
        }

        $option = $request->post();
        $save = ArticleDao::instance()->status($option, $request->uid);
        if (!$save) {
            return $this->error(ArticleDao::instance()->getError());
        }
        return $this->success('操作成功');
    }
}
