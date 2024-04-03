<?php

declare(strict_types=1);

namespace plugins\cms\contract;

/**
 * 栏目分类相关枚举属性
 * 
 * @author Mon <985558837@qq.com>
 * @version 1.0.0
 */
interface CateEmun
{
    /**
     * 分类状态
     * 
     * @var array
     */
    const CATE_STATUS = [
        // 禁用
        'disable'   => 0,
        // 正常
        'enable'    => 1,
    ];

    /**
     * 分类状态名称
     * 
     * @var array
     */
    const CATE_STATUS_TITLE = [
        // 禁用
        self::CATE_STATUS['disable']   => '禁用',
        // 正常
        self::CATE_STATUS['enable']    => '正常',
    ];

    /**
     * 栏目分类
     * 
     * @var array
     */
    const CATE_TYPE = [
        // 栏目主页
        'home'  => 0,
        // 栏目列表
        'list'  => 1,
        // 跳转链接
        'link'  => 2
    ];

    /**
     * 栏目分类名称
     * 
     * @var array
     */
    const CATE_TYPE_TITLE = [
        // 栏目主页
        self::CATE_TYPE['home'] => '栏目主页',
        // 栏目列表
        self::CATE_TYPE['list'] => '栏目列表',
        // 跳转链接
        self::CATE_TYPE['link'] => '跳转链接',
    ];
}
