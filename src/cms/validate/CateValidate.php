<?php

declare(strict_types=1);

namespace plugins\cms\validate;

use mon\util\Validate;

/**
 * 栏目分类验证器
 * 
 * @author Mon <985558837@qq.com>
 * @version 1.0.0
 */
class CateValidate extends Validate
{
    /**
     * 验证规则
     *
     * @var array
     */
    public $rule = [
        'idx'       => ['required', 'id'],
        'pid'       => ['required', 'int', 'min:0'],
        'type'      => ['required', 'in:0,1,2'],
        'title'     => ['required', 'str', 'maxLength:50'],
        'img'       => ['isset', 'str'],
        'url'       => ['isset', 'str'],
        'remark'    => ['isset', 'str', 'maxLength:250'],
        'sort'      => ['required', 'int', 'min:0', 'max:99'],
        'menu'      => ['required', 'in:0,1'],
        'status'    => ['required', 'in:0,1'],
    ];

    /**
     * 错误提示信息
     *
     * @var array
     */
    public $message = [
        'idx'       => '参数异常',
        'pid'       => '请选择合法的父级栏目',
        'type'      => '请选择栏目类型',
        'title'     => '请输入栏目分类名称',
        'img'       => '请上传图标',
        'url'       => '请输入跳转URL',
        'remark'    => '请输入备注信息',
        'sort'      => '请输入正确的排序权重值(0 <= x < 100)',
        'menu'      => '请选择是否导航显示',
        'status'    => '请选择状态',
    ];

    /**
     * 验证场景
     *
     * @var array
     */
    public $scope = [
        // 新增
        'add'  => ['pid', 'type', 'title', 'img', 'url', 'remark', 'sort', 'menu', 'status'],
        // 编辑
        'edit' => ['idx', 'pid', 'type', 'title', 'img', 'url', 'remark', 'sort', 'menu', 'status'],
    ];
}
