<?php

declare(strict_types=1);

namespace plugins\cms\controller;

use mon\http\Request;
use mon\http\Response;
use plugins\cms\dao\CommentDao;
use plugins\admin\comm\Controller;
use plugins\admin\comm\view\Template;
use plugins\cms\contract\CommentEmun;

/**
 * 评论控制器
 * 
 * @author Mon <985558837@qq.com>
 * @version 1.0.0
 */
class CommentController extends Controller
{
    /**
     * 查看列表
     *
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        if ($request->get('isApi')) {
            $option = $request->get();
            $result = CommentDao::instance()->getList($option);
            return $this->success('操作成功', $result['list'], ['count' => $result['count']]);
        }

        $module = CommentDao::instance()->getModuleList();
        foreach ($module as $app => &$value) {
            foreach ($value as $k => $v) {
                if ($k != 'name') {
                    unset($value[$k]);
                }
            }
        }
        return $this->fetch('comment/index', [
            'uid' => $request->uid,
            'module' => json_encode($module, JSON_UNESCAPED_UNICODE),
            'status' => json_encode(CommentEmun::COMMENT_STATUS_TITLE, JSON_UNESCAPED_UNICODE),
        ]);
    }

    /**
     * 编辑
     *
     * @param Request $request
     * @return Response
     */
    public function edit(Request $request)
    {
        if ($request->isPost()) {
            $option = $request->post();
            $save = CommentDao::instance()->edit($option, 0, $request->uid);
            if (!$save) {
                return $this->error(CommentDao::instance()->getError());
            }
            return $this->success('操作成功');
        }

        $id = $request->get('idx');
        if (!check('id', $id)) {
            return $this->error('params faild');
        }

        $info = CommentDao::instance()->where('id', $id)->get();
        if (!$info) {
            return $this->error('评论不存在');
        }

        return $this->fetch('comment/edit', ['data' => $info]);
    }

    /**
     * 编辑页面展示信息
     *
     * @param Request $request
     * @return mixed
     */
    public function displays(Request $request)
    {
        if ($request->isPost()) {
            $option = $request->post();
            $save = CommentDao::instance()->displays($option, $request->uid);
            if (!$save) {
                return $this->error(CommentDao::instance()->getError());
            }
            return $this->success('操作成功');
        }

        $id = $request->get('idx');
        if (!check('id', $id)) {
            return $this->error('params faild');
        }

        $info = CommentDao::instance()->where('id', $id)->get();
        if (!$info) {
            return $this->error('评论不存在');
        }

        return $this->fetch('comment/displays', ['data' => $info]);
    }

    /**
     * 编辑页面互动信息
     *
     * @param Request $request
     * @return mixed
     */
    public function interact(Request $request)
    {
        if ($request->isPost()) {
            $option = $request->post();
            $save = CommentDao::instance()->interact($option, $request->uid);
            if (!$save) {
                return $this->error(CommentDao::instance()->getError());
            }
            return $this->success('操作成功');
        }

        $id = $request->get('idx');
        if (!check('id', $id)) {
            return $this->error('params faild');
        }

        $info = CommentDao::instance()->where('id', $id)->get();
        if (!$info) {
            return $this->error('评论不存在');
        }

        return $this->fetch('comment/interact', ['data' => $info]);
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
            return $this->error('参数错误');
        }

        $option = $request->post();
        $save = CommentDao::instance()->status($option, $request->uid);
        if (!$save) {
            return $this->error(CommentDao::instance()->getError());
        }
        return $this->success('操作成功');
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

        $info = CommentDao::instance()->getInfo(intval($id));
        if (!$info) {
            return $this->error('评论不存在');
        }

        $info['title'] = '';
        if (!empty($info['union_data'])) {
            $info['title'] = $info['union_data']['title'];
        }
        $info['preview_link'] = '';
        if (!empty($info['union_link'])) {
            $info['preview_link'] = Template::buildURL($info['union_link'], ['idx' => $info['union_id']]);;
        }

        return $this->fetch('comment/preview', ['data' => $info]);
    }
}
