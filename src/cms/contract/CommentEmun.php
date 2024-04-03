<?php

declare(strict_types=1);

namespace plugins\cms\contract;

/**
 * 评论相关枚举属性
 * 
 * @author Mon <985558837@qq.com>
 * @version 1.0.0
 */
interface CommentEmun
{
    /**
     * 评论状态
     * 
     * @var array
     */
    const COMMENT_STATUS = [
        // 已删除
        'disable'   => 0,
        // 待审核
        'audit'     => 1,
        // 已通过
        'pass'      => 2,
        // 已拒绝
        'reject'    => 3,
    ];

    /**
     * 评论状态名称
     * 
     * @var array
     */
    const COMMENT_STATUS_TITLE = [
        // 已删除
        self::COMMENT_STATUS['disable'] => '已删除',
        // 待审核
        self::COMMENT_STATUS['audit']   => '待审核',
        // 已通过
        self::COMMENT_STATUS['pass']    => '已通过',
        // 已拒绝
        self::COMMENT_STATUS['reject']  => '已拒绝',
    ];

    /**
     * 评论模块
     * 
     * @var array
     */
    const COMMENT_MODULE = [
        // 文章评论模块名
        'doc'   => 'doc',
        // 单页评论模块名
        'page'  => 'page',
    ];
}
