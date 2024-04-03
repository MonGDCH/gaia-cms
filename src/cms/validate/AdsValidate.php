<?php

declare(strict_types=1);

namespace plugins\cms\validate;

use mon\util\Validate;

/**
 * 广告验证器
 * 
 * @author Mon <985558837@qq.com>
 * @version 1.0.0
 */
class AdsValidate extends Validate
{
    /**
     * 验证规则
     *
     * @var array
     */
    public $rule = [
        'idx'       => ['required', 'id'],
        'ads_id'    => ['required', 'id'],
        'title'     => ['required', 'str', 'maxLength:50'],
        'width'     => ['required', 'int', 'min:0', 'max:9999'],
        'height'    => ['required', 'int', 'min:0', 'max:9999'],
        'remark'    => ['isset', 'str', 'maxLength:250'],
        'img'       => ['required', 'str'],
        'url'       => ['required', 'str'],
        'target'    => ['required', 'str'],
        'sort'      => ['required', 'int', 'min:0', 'max:99'],
        'status'    => ['required', 'in:0,1'],
    ];

    /**
     * 提示信息
     *
     * @var array
     */
    public $messgae = [
        'idx'       => '参数异常',
        'ads_id'    => '请选择广告位',
        'title'     => '请输入标题名称',
        'width'     => '请输入合法的宽度',
        'height'    => '请输入合法的高度',
        'remark'    => '请输入合法的备注',
        'img'       => '请上传广告资源图片',
        'url'       => '请输入合法的跳转地址',
        'target'    => '请选择跳转方式',
        'sort'      => '请输入正确的排序权重值(0 <= x < 100)',
        'status'    => '请选择正确的状态',
    ];

    /**
     * 验证场景
     *
     * @var array
     */
    public $scope = [
        // 新增广告位
        'add'       => ['title', 'width', 'height', 'remark', 'status'],
        // 编辑广告位
        'edit'      => ['idx', 'title', 'width', 'height', 'remark', 'status'],
        // 新增广告位资源
        'add_img'   => ['ads_id', 'img', 'url', 'target', 'sort', 'remark', 'status'],
        // 编辑广告位资源
        'edit_img'  => ['idx', 'ads_id', 'img', 'url', 'target', 'sort', 'remark', 'status'],
    ];
}
