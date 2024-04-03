<?php

declare(strict_types=1);

namespace plugins\cms\contract;

/**
 * 广告相关枚举属性
 * 
 * @author Mon <985558837@qq.com>
 * @version 1.0.0
 */
interface AdsEmun
{
    /**
     * 广告位状态
     * 
     * @var array
     */
    const ADS_STATUS = [
        // 禁用
        'disable'   => 0,
        // 正常
        'enable'    => 1,
    ];

    /**
     * 广告位状态名称
     * 
     * @var array
     */
    const ADS_STATUS_TITLE = [
        // 禁用
        self::ADS_STATUS['disable']   => '禁用',
        // 正常
        self::ADS_STATUS['enable']    => '正常',
    ];


    /**
     * 广告位打开方式
     * 
     * @var array
     */
    const ADS_TARGET = [
        // 新窗口打开
        '_blank'    => '新窗口打开',
        // 当前窗口打开
        '_self'     => '当前窗口打开',
        // 在整个窗口打开
        '_top'      => '在整个窗口打开',
    ];
}
