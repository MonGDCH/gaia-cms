<?php

declare(strict_types=1);

namespace plugins\cms\controller;

use mon\http\Request;
use plugins\cms\dao\CateDao;
use plugins\cms\contract\CateEmun;
use plugins\admin\comm\Controller;

/**
 * 栏目分类控制器
 * 
 * @author Mon <985558837@qq.com>
 * @version 1.0.0
 */
class CateController extends Controller
{
    /**
     * 列表
     *
     * @param Request $request  请求实例
     * @return mixed
     */
    public function index(Request $request)
    {
        if ($request->get('isApi')) {
            $data = CateDao::instance()->order('sort', 'DESC')->all();
            return $this->success('ok', $data);
        }

        return $this->fetch('cate/index', [
            'uid' => $request->uid,
            'cateType' => json_encode(CateEmun::CATE_TYPE_TITLE, JSON_UNESCAPED_UNICODE)
        ]);
    }

    /**
     * 新增
     *
     * @param Request $request
     * @return mixed
     */
    public function add(Request $request)
    {
        if ($request->isPost()) {
            $option = $request->post();
            $edit = CateDao::instance()->add($option, $request->uid);
            if (!$edit) {
                return $this->error(CateDao::instance()->getError());
            }

            return $this->success('操作成功');
        }

        $cate = CateDao::instance()->getTreeList(0);
        array_unshift($cate, ['id' => 0, 'title' => '无', 'disabled' => false, 'children' => []]);
        $this->assign('cate', json_encode($cate, JSON_UNESCAPED_UNICODE));
        $this->assign('idx', $request->get('idx', 0));
        $this->assign('type', CateEmun::CATE_TYPE_TITLE);
        $this->assign('status', CateEmun::CATE_STATUS_TITLE);
        return $this->fetch('cate/add');
    }

    /**
     * 编辑
     *
     * @param Request $request
     * @return mixed
     */
    public function edit(Request $request)
    {
        // post更新操作
        if ($request->isPost()) {
            $option = $request->post();
            $edit = CateDao::instance()->edit($option, $request->uid);
            if (!$edit) {
                return $this->error(CateDao::instance()->getError());
            }

            return $this->success('操作成功');
        }

        $id = $request->get('idx');
        if (!check('id', $id)) {
            return $this->error('参数错误');
        }
        // 查询规则
        $data = CateDao::instance()->where('id', $id)->get();
        if (!$data) {
            return $this->error('栏目分类不存在');
        }

        $cate = CateDao::instance()->getTreeList($data['id']);
        array_unshift($cate, ['id' => 0, 'title' => '无', 'disabled' => false, 'children' => []]);
        $this->assign('cate', json_encode($cate, JSON_UNESCAPED_UNICODE));
        $this->assign('type', CateEmun::CATE_TYPE_TITLE);
        $this->assign('status', CateEmun::CATE_STATUS_TITLE);
        $this->assign('data', $data);
        return $this->fetch('cate/edit');
    }
}
