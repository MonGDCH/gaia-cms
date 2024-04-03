<?php

declare(strict_types=1);

namespace plugins\cms\controller;

use mon\http\Request;
use plugins\cms\dao\AdsDao;
use plugins\cms\dao\AdsImgDao;
use plugins\cms\contract\AdsEmun;
use plugins\admin\comm\Controller;

/**
 * 广告控制器
 * 
 * @author Mon <985558837@qq.com>
 * @version 1.0.0
 */
class AdController extends Controller
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
            $result = AdsDao::instance()->getList($option);
            return $this->success('操作成功', $result['list'], ['count' => $result['count']]);
        }

        return $this->fetch('ad/index', [
            'uid' => $request->uid,
            'status' => AdsEmun::ADS_STATUS_TITLE
        ]);
    }

    /**
     * 新增广告位
     *
     * @param Request $request
     * @return mixed
     */
    public function add(Request $request)
    {
        if ($request->isPost()) {
            $option = $request->post();
            $save = AdsDao::instance()->add($option, $request->uid);
            if (!$save) {
                return $this->error(AdsDao::instance()->getError());
            }
            return $this->success('操作成功');
        }

        return $this->fetch('ad/add', ['status' => AdsEmun::ADS_STATUS_TITLE]);
    }

    /**
     * 编辑广告位
     *
     * @param Request $request
     * @return mixed
     */
    public function edit(Request $request)
    {
        if ($request->isPost()) {
            $option = $request->post();
            $save = AdsDao::instance()->edit($option, $request->uid);
            if (!$save) {
                return $this->error(AdsDao::instance()->getError());
            }
            return $this->success('操作成功');
        }

        $id = $request->get('idx');
        if (!check('id', $id)) {
            return $this->error('params faild');
        }

        $info = AdsDao::instance()->where('id', $id)->get();
        if (!$info) {
            return $this->error('广告位不存在');
        }

        return $this->fetch('ad/edit', ['data' => $info, 'status' => AdsEmun::ADS_STATUS_TITLE]);
    }

    /**
     * 广告资源
     *
     * @param Request $request
     * @return mixed
     */
    public function assets(Request $request)
    {
        $ads = $request->get('ads');
        if (!check('id', $ads)) {
            return $this->error('params faild');
        }
        if ($request->get('isApi')) {
            $option = $request->get();
            $result = AdsImgDao::instance()->getList($option);
            return $this->success('操作成功', $result['list'], ['count' => $result['count']]);
        }

        return $this->fetch('ad/assets', [
            'ads' => $ads,
            'uid' => $request->uid,
            'target' => json_encode(AdsEmun::ADS_TARGET, JSON_UNESCAPED_UNICODE)
        ]);
    }

    /**
     * 新增广告资源
     *
     * @param Request $request
     * @return mixed
     */
    public function addAssets(Request $request)
    {
        $ads = $request->get('ads');
        if (!check('id', $ads)) {
            return $this->error('params faild');
        }

        if ($request->isPost()) {
            $option = $request->post();
            $save = AdsImgDao::instance()->add($option, $request->uid);
            if (!$save) {
                return $this->error(AdsImgDao::instance()->getError());
            }
            return $this->success('操作成功');
        }

        return $this->fetch('ad/addAssets', [
            'ads' => $ads,
            'target' => AdsEmun::ADS_TARGET,
            'status' => AdsEmun::ADS_STATUS_TITLE
        ]);
    }

    /**
     * 编辑广告资源
     *
     * @param Request $request
     * @return mixed
     */
    public function editAssets(Request $request)
    {
        if ($request->isPost()) {
            $option = $request->post();
            $save = AdsImgDao::instance()->edit($option, $request->uid);
            if (!$save) {
                return $this->error(AdsImgDao::instance()->getError());
            }
            return $this->success('操作成功');
        }

        $id = $request->get('idx');
        if (!check('id', $id)) {
            return $this->error('params faild');
        }

        $info = AdsImgDao::instance()->where('id', $id)->get();
        if (!$info) {
            return $this->error('广告资源不存在');
        }

        return $this->fetch('ad/editAssets', [
            'data' => $info,
            'target' => AdsEmun::ADS_TARGET,
            'status' => AdsEmun::ADS_STATUS_TITLE
        ]);
    }
}
