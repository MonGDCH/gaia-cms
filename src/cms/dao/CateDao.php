<?php

declare(strict_types=1);

namespace plugins\cms\dao;

use Throwable;
use mon\util\Tree;
use mon\log\Logger;
use mon\thinkOrm\Dao;
use mon\util\Instance;
use plugins\admin\dao\AdminLogDao;
use plugins\cms\contract\CateEmun;
use plugins\cms\validate\CateValidate;

/**
 * 分类栏目Dao操作
 * 
 * @author Mon <985558837@qq.com>
 * @version 1.0.0
 */
class CateDao extends Dao
{
    use Instance;

    /**
     * 操作表
     *
     * @var string
     */
    protected $table = 'cms_cate';

    /**
     * 自动写入时间戳
     *
     * @var boolean
     */
    protected $autoWriteTimestamp = true;

    /**
     * 验证器
     *
     * @var string
     */
    protected $validate = CateValidate::class;


    /**
     * 新增
     *
     * @param array $data     请求参数
     * @param integer $adminID  管理员ID
     * @return integer
     */
    public function add(array $data, int $adminID): int
    {
        $check = $this->validate()->data($data)->scope('add')->check();
        if (!$check) {
            $this->error = $this->validate()->getError();
            return 0;
        }

        if ($data['type'] == CateEmun::CATE_TYPE['link'] && (!isset($data['url']) || empty($data['url']))) {
            $this->error = '请输入跳转链接地址';
            return 0;
        }

        if ($data['pid'] != 0) {
            $parentInfo = $this->where('id', $data['pid'])->get();
            if (!$parentInfo) {
                $this->error = '父级栏目分类不存在或无效';
                return 0;
            }
        }

        $this->startTrans();
        try {
            Logger::instance()->channel()->info('Add CMS cate');
            $cate_id = $this->allowField(['pid', 'type', 'title', 'img', 'url', 'remark', 'sort', 'menu', 'status'])->save($data, true, true);
            if (!$cate_id) {
                $this->rollback();
                $this->error = '添加分类失败';
                return 0;
            }

            // 记录系统日志
            if ($adminID > 0) {
                $record = AdminLogDao::instance()->record([
                    'uid' => $adminID,
                    'module' => 'cms',
                    'action' => '添加栏目分类',
                    'content' => '添加栏目分类: ' . $data['title'] . ', ID: ' . $cate_id,
                    'sid' => $cate_id
                ]);
                if (!$record) {
                    $this->rollback();
                    $this->error = '记录操作日志失败,' . AdminLogDao::instance()->getError();
                    return 0;
                }
            }

            $this->commit();
            return $cate_id;
        } catch (Throwable $e) {
            $this->rollback();
            $this->error = '添加分类异常';
            Logger::instance()->channel()->error('Add CMS cate exception. msg: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * 编辑
     *
     * @param array $data     请求参数
     * @param integer $adminID  管理员ID
     * @return boolean
     */
    public function edit(array $data, int $adminID): bool
    {
        $check = $this->validate()->data($data)->scope('edit')->check();
        if (!$check) {
            $this->error = $this->validate()->getError();
            return false;
        }
        if ($data['type'] == CateEmun::CATE_TYPE['link'] && (!isset($data['url']) || empty($data['url']))) {
            $this->error = '请输入跳转链接地址';
            return false;
        }

        $info = $this->where('id', $data['idx'])->get();
        if (!$info) {
            $this->error = '栏目分类不存在';
            return false;
        }
        if ($data['pid'] != 0) {
            $parentInfo = $this->where('id', $data['pid'])->get();
            if (!$parentInfo) {
                $this->error = '父级栏目分类不存在或无效';
                return false;
            }
        }

        $this->startTrans();
        try {
            Logger::instance()->channel()->info('Edit CMS Ads');
            $saveMessage = $this->allowField(['pid', 'type', 'title', 'img', 'url', 'remark', 'sort', 'menu', 'status'])->where('id', $info['id'])->save($data);
            if (!$saveMessage) {
                $this->rollback();
                $this->error = '编辑栏目分类失败';
                return false;
            }

            if ($adminID > 0) {
                $record = AdminLogDao::instance()->record([
                    'uid' => $adminID,
                    'module' => 'cms',
                    'action' => '编辑栏目分类',
                    'content' => '编辑栏目分类: ' . $data['title'] . ', ID: ' . $info['id'],
                    'sid' => $info['id']
                ]);
                if (!$record) {
                    $this->rollback();
                    $this->error = '记录操作日志失败,' . AdminLogDao::instance()->getError();
                    return false;
                }
            }

            $this->commit();
            return true;
        } catch (Throwable $e) {
            $this->rollback();
            $this->error = '编辑栏目分类异常';
            Logger::instance()->channel()->error('Edit CMS cate exception. msg: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 查询列表
     *
     * @param array $data   请求参数
     * @return array
     */
    public function getList(array $data): array
    {
        $limit = isset($data['limit']) ? intval($data['limit']) : 10;
        $page = isset($data['page']) && is_numeric($data['page']) ? intval($data['page']) : 1;

        $list = $this->scope('list', $data)->page($page, $limit)->all();
        $count = $this->scope('list', $data)->count('id');

        return [
            'list'      => $list,
            'count'     => $count,
            'pageSize'  => $limit,
            'page'      => $page
        ];
    }

    /**
     * 查询场景
     *
     * @param \mon\thinkOrm\extend\Query $query
     * @param array $option
     * @return mixed
     */
    public function scopeList($query, array $option)
    {
        // ID搜索
        if (isset($option['idx']) &&  $this->validate()->id($option['idx'])) {
            $query->where('id', intval($option['idx']));
        }
        // 按类型
        if (isset($option['type']) &&  $this->validate()->int($option['type'])) {
            $query->where('type', intval($option['type']));
        }
        // 按是否菜单
        if (isset($option['menu']) &&  $this->validate()->int($option['menu'])) {
            $query->where('menu', intval($option['menu']));
        }
        // 按名称
        if (isset($option['title']) && is_string($option['title']) && !empty($option['title'])) {
            $query->whereLike('title', trim($option['title']) . '%');
        }
        // 按状态
        if (isset($option['status']) && $this->validate()->int($option['status'])) {
            $query->where('status', intval($option['status']));
        }

        // 排序字段，默认id
        $order = 'id';
        if (isset($option['order']) && in_array($option['order'], ['create_time', 'update_time'])) {
            $order = $option['order'];
        }
        // 排序类型，默认 ASC
        $sort = 'ASC';
        if (isset($option['sort']) && in_array(strtoupper($option['sort']), ['ASC', 'DESC'])) {
            $sort = strtoupper($option['sort']);
        }

        return $query->order($order, $sort);
    }

    /**
     * 获取树状栏目分类列表
     *
     * @param integer $id   分类ID，0则所有都可选
     * @param string $childrenName
     * @return array
     */
    public function getTreeList(int $id, string $childrenName = 'children'): array
    {
        $field = ['id', 'pid', 'title', 'IF(id = ' . intval($id) . ', 1, 0) AS disabled'];
        $data = $this->where('status', CateEmun::CATE_STATUS['enable'])->field($field)->order('sort', 'DESC')->all();
        $dataArr = Tree::instance()->data($data)->getTree($childrenName);
        return $this->formatDataList($dataArr);
    }

    /**
     * 整理允许操作的权限菜单数据
     *
     * @param array $data
     * @param boolean $disable
     * @param string $childrenName
     * @return array
     */
    protected function formatDataList(array $data, bool $disable = false, string $childrenName = 'children'): array
    {
        foreach ($data as &$item) {
            $item['disabled'] = ($item['disabled'] == 1 || $disable);
            if (!empty($item[$childrenName])) {
                $item[$childrenName] = $this->formatDataList($item[$childrenName], $item['disabled'], $childrenName);
            }
        }

        return $data;
    }

    /**
     * 获取树状结构数据
     *
     * @param array $field  查询字段
     * @param array $where  where条件
     * @return array
     */
    public function getTreeData(array $field = ['id', 'pid', 'title'], array $where = []): array
    {
        $data = $this->where($where)->field($field)->order('sort', 'DESC')->select();
        return Tree::instance()->data($data)->getTree('children');
    }
}
