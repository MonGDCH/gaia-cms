<?php

declare(strict_types=1);

namespace plugins\cms\contract;

/**
 * 单页相关枚举属性
 * 
 * @author Mon <985558837@qq.com>
 * @version 1.0.0
 */
interface PageEmun
{
    /**
     * 单页状态
     * 
     * @var array
     */
    const PAGE_STATUS = [
        // 已下线
        'disable'   => 0,
        // 草稿
        'draft'     => 1,
        // 审核通过，发布
        'publish'   => 2,
        // 审核拒绝
        'reject'    => 3,
    ];

    /**
     * 单页状态名称
     * 
     * @var array
     */
    const PAGE_STATUS_TITLE = [
        // 已下线
        self::PAGE_STATUS['disable']    => '已下线',
        // 草稿
        self::PAGE_STATUS['draft']      => '草稿',
        // 审核通过，发布
        self::PAGE_STATUS['publish']    => '已发布',
        // 审核拒绝
        self::PAGE_STATUS['reject']     => '审核拒绝',
    ];

    /**
     * 单页视图类型
     * 
     * @var array
     */
    const PAGE_VIEW = [
        'empty' => '无'
    ];
}
