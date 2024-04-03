<?php

declare(strict_types=1);

namespace plugins\cms\validate;

use mon\util\Validate;
use plugins\cms\contract\ArticleEmun;

/**
 * 文章验证器
 * 
 * @author Mon <985558837@qq.com>
 * @version 1.0.0
 */
class ArticleValidate extends Validate
{
    /**
     * 验证规则
     *
     * @var array
     */
    public $rule = [
        'idx'           => ['required', 'id'],
        'cate_id'       => ['required', 'id'],
        'title'         => ['required', 'str', 'maxLength:50'],
        'remark'        => ['isset', 'str', 'maxLength:250'],
        'img'           => ['isset', 'str'],
        'keywords'      => ['isset', 'str'],
        'description'   => ['isset', 'str'],
        'send_time'     => ['required', 'timestamp'],
        'read'          => ['required', 'int', 'min:0'],
        'like'          => ['required', 'int', 'min:0'],
        'bad'           => ['required', 'int', 'min:0'],
        'is_hot'        => ['required', 'in:0,1'],
        'is_recommend'  => ['required', 'in:0,1'],
        'is_top'        => ['required', 'in:0,1'],
        'is_comment'    => ['required', 'in:0,1'],
        'sort'          => ['required', 'int', 'min:0', 'max:99'],
        'status'        => ['required', 'status'],
        'content'       => ['required', 'str'],
        'tags'          => ['isset', 'str'],
    ];

    /**
     * 错误提示信息
     *
     * @var array
     */
    public $message = [
        'idx'           => '参数异常',
        'cate_id'       => '请选择栏目分类',
        'title'         => '请输入文章标题',
        'remark'        => '请输入对应的简述',
        'img'           => '请上传文章封面',
        'keywords'      => '请输入合法的SEO关键字',
        'description'   => '请输入合法的SEO描述',
        'send_time'     => '请选择发布时间',
        'read'          => '请输入合法的阅读数',
        'like'          => '请输入合法的点赞数',
        'bad'           => '请输入合法的点踩数',
        'is_hot'        => '请选择是否热门',
        'is_recommend'  => '请选择是否推荐',
        'is_top'        => '请选择是否置顶',
        'is_comment'    => '请选择是否允许评论',
        'sort'          => '请输入正确的排序权重值(0 <= x < 100)',
        'status'        => '请选择状态',
        'content'       => '请输入文章内容',
        'tags'          => '请选择的文章标签'
    ];

    /**
     * 验证场景
     *
     * @var array
     */
    public $scope = [
        // 新增文章
        'add'       => ['cate_id', 'title', 'remark', 'img', 'keywords', 'description', 'send_time', 'content', 'tags', 'sort'],
        // 编辑文章
        'edit'      => ['idx', 'cate_id', 'title', 'remark', 'img', 'keywords', 'description', 'send_time', 'content', 'tags', 'sort'],
        // 修改展示数据
        'displays'  => ['idx', 'is_hot', 'is_recommend', 'is_top', 'is_comment'],
        // 修改互动数据
        'interact'  => ['idx', 'read', 'like', 'bad'],
        // 修改文章状态
        'status'    => ['idx', 'status']
    ];

    /**
     * 验证状态合法值
     *
     * @param string $value
     * @return boolean
     */
    public function status($value): bool
    {
        return isset(ArticleEmun::ARTICLE_STATUS_TITLE[$value]);
    }
}
