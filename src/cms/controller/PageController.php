<?php

declare(strict_types=1);

namespace plugins\cms\controller;

use mon\http\Request;
use plugins\cms\dao\PageDao;
use plugins\cms\contract\PageEmun;
use plugins\admin\comm\Controller;

/**
 * 独立页面控制器
 * 
 * @author Mon <985558837@qq.com>
 * @version 1.0.0
 */
class PageController extends Controller
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
            $result = PageDao::instance()->getList($option);
            return $this->success('操作成功', $result['list'], ['count' => $result['count']]);
        }

        return $this->fetch('page/index', [
            'uid' => $request->uid,
            'status' => json_encode(PageEmun::PAGE_STATUS, JSON_UNESCAPED_UNICODE)
        ]);
    }

    /**
     * 新增单独页面
     *
     * @param Request $request
     * @return mixed
     */
    public function add(Request $request)
    {
        if ($request->isPost()) {
            $option = $request->post();
            $option['content'] = $request->post('content', null, false);
            $save = PageDao::instance()->add($option, $request->uid);
            if (!$save) {
                return $this->error(PageDao::instance()->getError());
            }
            return $this->success('操作成功');
        }

        return $this->fetch('page/add', ['tmp' => PageEmun::PAGE_VIEW]);
    }

    /**
     * 编辑单独页面
     *
     * @param Request $request
     * @return mixed
     */
    public function edit(Request $request)
    {
        if ($request->isPost()) {
            $option = $request->post();
            $option['content'] = $request->post('content', null, false);
            $save = PageDao::instance()->edit($option, $request->uid);
            if (!$save) {
                return $this->error(PageDao::instance()->getError());
            }
            return $this->success('操作成功');
        }

        $id = $request->get('idx');
        if (!check('id', $id)) {
            return $this->error('params faild');
        }
        $info = PageDao::instance()->getInfo(intval($id));
        if (!$info) {
            return $this->error('单独页面不存在');
        }

        return $this->fetch('page/edit', ['tmp' => PageEmun::PAGE_VIEW, 'data' => $info]);
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
            $save = PageDao::instance()->displays($option, $request->uid);
            if (!$save) {
                return $this->error(PageDao::instance()->getError());
            }
            return $this->success('操作成功');
        }

        $id = $request->get('idx');
        if (!check('id', $id)) {
            return $this->error('params faild');
        }

        $info = PageDao::instance()->where('id', $id)->get();
        if (!$info) {
            return $this->error('单独页面不存在');
        }

        return $this->fetch('page/displays', ['data' => $info]);
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
            $save = PageDao::instance()->interact($option, $request->uid);
            if (!$save) {
                return $this->error(PageDao::instance()->getError());
            }
            return $this->success('操作成功');
        }

        $id = $request->get('idx');
        if (!check('id', $id)) {
            return $this->error('params faild');
        }

        $info = PageDao::instance()->where('id', $id)->get();
        if (!$info) {
            return $this->error('单独页面不存在');
        }

        return $this->fetch('page/interact', ['data' => $info]);
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
        $save = PageDao::instance()->status($option, $request->uid);
        if (!$save) {
            return $this->error(PageDao::instance()->getError());
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
            return $this->error('参数错误');
        }

        $info = PageDao::instance()->getInfo(intval($id));
        if (!$info) {
            return $this->error('页面不存在');
        }
        // 修复视图字段名重复的BUG
        $data = $info;
        $data['tmp'] = $info['view'];
        unset($data['view']);

        $viewAttr = PageEmun::PAGE_VIEW;
        $tmp = isset($viewAttr[$info['view']]) ? $info['view'] : 'empty';
        $view = 'page/tmp/' . $tmp;
        return $this->fetch($view, $data);
    }
}
