<?php

declare(strict_types=1);

namespace gaia\cms\command;

use Throwable;
use mon\util\Sql;
use ErrorException;
use mon\env\Config;
use think\facade\Db;
use mon\thinkORM\Dao;
use mon\console\Input;
use mon\console\Output;
use mon\console\Command;
use plugins\admin\dao\MenuDao;
use plugins\admin\dao\AuthRuleDao;

/**
 * 数据库初始化
 *
 * @author Mon <98555883@qq.com>
 * @version 1.0.0
 */
class InitCommand extends Command
{
    /**
     * 指令名
     *
     * @var string
     */
    protected static $defaultName = 'cms:init';

    /**
     * 指令描述
     *
     * @var string
     */
    protected static $defaultDescription = 'Initialization cms database';

    /**
     * 指令分组
     *
     * @var string
     */
    protected static $defaultGroup = 'Admin';

    /**
     * 菜单
     *
     * @var array
     */
    protected $menu = [
        ['name' => 'cms', 'title' => '内容管理', 'icon' => 'layui-icon layui-icon-template-1', 'chilid' => [
            ['name' => '/cms/ad', 'title' => '区块广告', 'icon' => 'layui-icon layui-icon-template-1'],
            ['name' => '/cms/cate', 'title' => '栏目分类', 'icon' => 'layui-icon layui-icon-template-1'],
            ['name' => '/cms/page', 'title' => '独立页面', 'icon' => 'layui-icon layui-icon-template-1'],
            ['name' => '/cms/article', 'title' => '文章管理', 'icon' => 'layui-icon layui-icon-template-1'],
            ['name' => '/cms/comment', 'title' => '用户评论', 'icon' => 'layui-icon layui-icon-template-1'],
        ]],
    ];

    /**
     * 权限
     *
     * @var array
     */
    protected $rule = [
        ['name' => 'cms', 'title' => '内容管理', 'chilid' => [
            ['name' => 'ad', 'title' => '区块广告', 'chilid' => [
                ['name' => '/cms/ad', 'title' => '查看'],
                ['name' => '/cms/ad/add', 'title' => '新增'],
                ['name' => '/cms/ad/edit', 'title' => '编辑'],
                ['name' => 'adAssets', 'title' => '区块资源', 'chilid' => [
                    ['name' => '/cms/ad/assets', 'title' => '查看'],
                    ['name' => '/cms/ad/assets/add', 'title' => '新增'],
                    ['name' => '/cms/ad/assets/edit', 'title' => '编辑'],
                ]],
            ]],
            ['name' => 'cate', 'title' => '栏目分类', 'chilid' => [
                ['name' => '/cms/cate', 'title' => '查看'],
                ['name' => '/cms/cate/add', 'title' => '新增'],
                ['name' => '/cms/cate/edit', 'title' => '编辑'],
            ]],
            ['name' => 'article', 'title' => '内容管理', 'chilid' => [
                ['name' => '/cms/article', 'title' => '查看'],
                ['name' => '/cms/article/add', 'title' => '新增'],
                ['name' => '/cms/article/edit', 'title' => '编辑'],
                ['name' => '/cms/article/detail', 'title' => '查看详情'],
                ['name' => '/cms/article/preview', 'title' => '预览'],
                ['name' => '/cms/article/interact', 'title' => '修改互动信息'],
                ['name' => '/cms/article/displays', 'title' => '修改展示信息'],
                ['name' => '/cms/article/toggle', 'title' => '修改状态'],
                ['name' => 'articleTag', 'title' => '内容标签', 'chilid' => [
                    ['name' => '/cms/article/tag', 'title' => '查看'],
                    ['name' => '/cms/article/tag/add', 'title' => '新增'],
                    ['name' => '/cms/article/tag/edit', 'title' => '编辑'],
                ]],
            ]],
            ['name' => 'page', 'title' => '独立页面', 'chilid' => [
                ['name' => '/cms/page', 'title' => '查看'],
                ['name' => '/cms/page/add', 'title' => '新增'],
                ['name' => '/cms/page/edit', 'title' => '编辑'],
                ['name' => '/cms/page/preview', 'title' => '预览'],
                ['name' => '/cms/page/interact', 'title' => '修改互动信息'],
                ['name' => '/cms/page/displays', 'title' => '修改展示信息'],
                ['name' => '/cms/page/toggle', 'title' => '修改状态'],
            ]],
            ['name' => 'comment', 'title' => '用户评论', 'chilid' => [
                ['name' => '/cms/comment', 'title' => '查看'],
                ['name' => '/cms/comment/add', 'title' => '新增'],
                ['name' => '/cms/comment/edit', 'title' => '编辑'],
                ['name' => '/cms/comment/preview', 'title' => '预览'],
                ['name' => '/cms/comment/interact', 'title' => '修改互动信息'],
                ['name' => '/cms/comment/displays', 'title' => '修改展示信息'],
                ['name' => '/cms/comment/toggle', 'title' => '修改状态'],
            ]]
        ]]
    ];

    /**
     * 执行指令
     *
     * @param  Input  $in  输入实例
     * @param  Output $out 输出实例
     * @return integer  exit状态码
     */
    public function execute(Input $in, Output $out)
    {
        // 读取sql文件
        $file = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'database.sql';
        $sqls = Sql::instance()->parseFile($file);
        // 执行sql
        Db::setConfig(Config::instance()->get('database', []));
        $out->block('Installation bootstrap');
        $out->spinBegiin();
        foreach ($sqls as $i => $sql) {
            Db::execute($sql);
            if ($i % 5 == 0) {
                $out->spin();
            }
        }

        $out->spin();

        $this->createMenu($this->menu, MenuDao::instance());
        $out->spin();
        $this->createRule($this->rule, AuthRuleDao::instance());

        $out->spinEnd();
        $out->block('Installation done!', 'SUCCESS');
    }

    /**
     * 创建菜单
     *
     * @param array $list   菜单列表
     * @param Dao $dao      菜单Dao操作实例
     * @param integer $pid  父级ID
     * @return void
     */
    public function createMenu(array $list, Dao $dao, int $pid = 0)
    {
        $dao->startTrans();
        try {
            foreach ($list as $item) {
                // 判断是否存在后代
                $hasChild = isset($item['chilid']) && $item['chilid'] ? true : false;
                // 写入记录
                $data = [
                    'pid'   => $pid,
                    'name'  => $item['name'],
                    'title' => $item['title'],
                    'icon'  => $item['icon'],
                    'type'  => $hasChild ? '0' : '1',
                ];
                $menu_id = $dao->save($data, true, true);
                if (!$menu_id) {
                    $dao->rollback();
                    throw new ErrorException('新增菜单失败：' . $item['name']);
                }
                // 判断是否存在后代，存在则递归执行
                if ($hasChild) {
                    $this->createMenu($item['chilid'], $dao, $menu_id);
                }
            }

            $dao->commit();
            return;
        } catch (Throwable $e) {
            $dao->rollback();
            throw $e;
        }
    }

    /**
     * 创建权限
     *
     * @param array $list   权限列表
     * @param Dao $dao      权限Dao操作实例
     * @param integer $pid  父级ID
     * @return void
     */
    public function createRule(array $list, Dao $dao, int $pid = 0)
    {
        $dao->startTrans();
        try {
            foreach ($list as $item) {
                // 判断是否存在后代
                $hasChild = isset($item['chilid']) && $item['chilid'] ? true : false;
                // 写入记录
                $data = [
                    'pid'   => $pid,
                    'name'  => $item['name'],
                    'title' => $item['title'],
                ];
                $rule_id = $dao->save($data, true, true);
                if (!$rule_id) {
                    $dao->rollback();
                    throw new ErrorException('新增权限失败：' . $item['name']);
                }
                // 判断是否存在后代，存在则递归执行
                if ($hasChild) {
                    $this->createRule($item['chilid'], $dao, $rule_id);
                }
            }

            $dao->commit();
            return;
        } catch (Throwable $e) {
            $dao->rollback();
            throw $e;
        }
    }
}
