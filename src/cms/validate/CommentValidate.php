<?php

declare(strict_types=1);

namespace plugins\cms\validate;

use mon\util\Validate;
use plugins\cms\dao\Comment;
use plugins\cms\contract\CommentEmun;

/**
 * 评论验证器
 * 
 * @author Mon <985558837@qq.com>
 * @version 1.0.0
 */
class CommentValidate extends Validate
{
    /**
     * 验证规则
     *
     * @var array
     */
    public $rule = [
        'idx'       => ['required', 'id'],
        'module'    => ['required', 'str', 'module'],
        'union_id'  => ['required', 'int', 'min:0'],
        'pid'       => ['required', 'int', 'min:0'],
        'content'   => ['required', 'str', 'maxLength:500'],
        'like'      => ['required', 'int', 'min:0'],
        'bad'       => ['required', 'int', 'min:0'],
        'is_hot'    => ['required', 'in:0,1'],
        'is_top'    => ['required', 'in:0,1'],
        'sort'      => ['required', 'int', 'min:0', 'max:99'],
        'status'    => ['required', 'status'],
    ];

    /**
     * 错误提示信息
     *
     * @var array
     */
    public $message = [
        'idx'       => '参数异常',
        'module'    => '请选择所属模块',
        'union_id'  => '关联参数异常',
        'pid'       => '回复评论参数异常',
        'content'   => [
            'required'  => '请输入评论内容',
            'str'       => '请输入合法的评论内容',
            'maxLength' => '评论内容长度不能超过500'
        ],
        'like'      => '请输入合法的点赞数',
        'bad'       => '请输入合法的点踩数',
        'is_hot'    => '请选择是否热门',
        'is_top'    => '请选择是否置顶',
        'sort'      => '请输入正确的排序权重值(0 <= x < 100)',
        'status'    => '请选择状态',
    ];

    /**
     * 验证场景
     *
     * @var array
     */
    public $scope = [
        // 新增
        'add'       => ['module', 'union_id', 'pid', 'content'],
        // 编辑
        'edit'      => ['idx', 'content', 'status'],
        // 修改展示数据
        'displays'  => ['idx', 'is_hot', 'is_top', 'sort'],
        // 修改互动数据
        'interact'  => ['idx', 'like', 'bad'],
        // 修改状态
        'status'    => ['idx', 'status']
    ];

    /**
     * 判断是否允许的模块值
     *
     * @param string $value
     * @return boolean
     */
    public function module($value): bool
    {
        $moduleList = Comment::instance()->getModuleList();
        return isset($moduleList[$value]);
    }

    /**
     * 验证状态合法值
     *
     * @param string $value
     * @return boolean
     */
    public function status($value): bool
    {
        return isset(CommentEmun::COMMENT_STATUS_TITLE[$value]);
    }
}
