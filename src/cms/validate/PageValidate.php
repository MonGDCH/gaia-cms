<?php

declare(strict_types=1);

namespace plugins\cms\validate;

use mon\util\Validate;

/**
 * 单页验证器
 * 
 * @author Mon <985558837@qq.com>
 * @version 1.0.0
 */
class PageValidate extends Validate
{
    /**
     * 验证规则
     *
     * @var array
     */
    public $rule = [
        'idx'           => ['required', 'id'],
        'title'         => ['required', 'str', 'maxLength:50'],
        'remark'        => ['isset', 'str', 'maxLength:250'],
        'img'           => ['isset', 'str'],
        'keywords'      => ['isset', 'str'],
        'description'   => ['isset', 'str'],
        'view'          => ['isset', 'str'],
        'read'          => ['required', 'int', 'min:0'],
        'like'          => ['required', 'int', 'min:0'],
        'bad'           => ['required', 'int', 'min:0'],
        'is_hot'        => ['required', 'in:0,1'],
        'is_recommend'  => ['required', 'in:0,1'],
        'is_top'        => ['required', 'in:0,1'],
        'is_comment'    => ['required', 'in:0,1'],
        'sort'          => ['required', 'int', 'min:0', 'max:99'],
        'status'        => ['required', 'in:0,1,2,3'],
        'content'       => ['required', 'str'],
        'type'          => ['required', 'in:0,1'],
    ];

    /**
     * 错误提示信息
     *
     * @var array
     */
    public $message = [
        'idx'           => '参数异常',
        'title'         => [
            'required'  => '请输入页面标题',
            'str'       => '请输入合法的页面标题',
            'maxLength' => '页面标题长度不能超过50'
        ],
        'remark'        => '请输入对应的简述',
        'img'           => '请上传封面图片',
        'keywords'      => '请输入合法的SEO关键字',
        'description'   => '请输入合法的SEO描述',
        'view'          => '请输入对应的视图模板名称',
        'read'          => '请输入合法的阅读数',
        'like'          => '请输入合法的点赞数',
        'bad'           => '请输入合法的点踩数',
        'is_hot'        => '请选择是否热门',
        'is_recommend'  => '请选择是否推荐',
        'is_top'        => '请选择是否置顶',
        'is_comment'    => '请选择是否允许评论',
        'sort'          => '请输入正确的排序权重值(0 <= x < 100)',
        'status'        => '请选择状态',
        'content'       => '请编辑页面内容',
        'type'          => '请选择内容类型',
    ];

    /**
     * 验证场景
     *
     * @var array
     */
    public $scope = [
        // 新增
        'add'       => ['title', 'remark', 'img', 'keywords', 'description', 'view', 'sort', 'content', 'type'],
        // 编辑
        'edit'      => ['idx', 'title', 'remark', 'img', 'keywords', 'description', 'view',  'sort', 'content', 'type'],
        // 修改展示数据
        'displays'  => ['idx', 'is_hot', 'is_recommend', 'is_top', 'is_comment'],
        // 修改互动数据
        'interact'  => ['idx', 'read', 'like', 'bad'],
        // 修改状态
        'status'    => ['idx', 'status']
    ];
}
