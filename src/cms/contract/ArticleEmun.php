<?php

declare(strict_types=1);

namespace plugins\cms\contract;

/**
 * 文章相关枚举属性
 * 
 * @author Mon <985558837@qq.com>
 * @version 1.0.0
 */
interface ArticleEmun
{
    /**
     * 文章状态
     * 
     * @var array
     */
    const ARTICLE_STATUS = [
        // 已下线
        'disable'   => 0,
        // 草稿
        'draft'     => 1,
        // 待审核
        'audit'     => 2,
        // 已发布
        'publish'   => 3,
        // 已拒绝
        'reject'    => 4,
    ];

    /**
     * 文章状态名称
     * 
     * @var array
     */
    const ARTICLE_STATUS_TITLE = [
        // 已下线
        self::ARTICLE_STATUS['disable'] => '已下线',
        // 草稿
        self::ARTICLE_STATUS['draft']   => '草稿',
        // 待审核
        self::ARTICLE_STATUS['audit']   => '待审核',
        // 已发布
        self::ARTICLE_STATUS['publish'] => '已发布',
        // 已拒绝
        self::ARTICLE_STATUS['reject']  => '已拒绝',
    ];
}
