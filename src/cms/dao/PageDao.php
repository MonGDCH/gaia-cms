<?php

declare(strict_types=1);

namespace plugins\cms\dao;

use Throwable;
use mon\log\Logger;
use mon\thinkOrm\Dao;
use mon\util\Instance;
use plugins\admin\dao\AdminLogDao;
use plugins\cms\contract\PageEmun;
use plugins\cms\validate\PageValidate;

/**
 * 单页Dao操作
 * 
 * @author Mon <985558837@qq.com>
 * @version 1.0.0
 */
class PageDao extends Dao
{
    use Instance;

    /**
     * 操作表
     *
     * @var string
     */
    protected $table = 'cms_page';

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
    protected $validate = PageValidate::class;

    /**
     * 获取内容
     *
     * @param integer $id   文章ID
     * @param boolean $isPublish   是否只查询已发布
     * @return array
     */
    public function getInfo(int $id, bool $isPublish = false): array
    {
        $field = ['a.*', 'b.content', 'b.type'];
        $where = ['a.id' => $id];
        if ($isPublish) {
            $where['a.status'] = PageEmun::PAGE_STATUS['publish'];
        }
        $contentTable = PageContentDao::instance()->getTable();
        $data = $this->alias('a')->field($field)->where($where)->join($contentTable . ' b', 'a.id=b.page_id')->get();
        if (!$data) {
            return [];
        }
        return $data;
    }

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

        $data['uid'] = $adminID;
        $this->startTrans();
        try {
            Logger::instance()->channel()->info('Add CMS page');
            // 保存基本信息
            $field = ['title', 'remark', 'img', 'keywords', 'description', 'view', 'sort', 'uid'];
            $page_id = $this->allowField($field)->save($data, true, true);
            if (!$page_id) {
                $this->rollback();
                $this->error = '添加页面失败';
                return 0;
            }
            // 保存内容
            $saveContent = PageContentDao::instance()->add($page_id, $data['content'], intval($data['type']));
            if (!$saveContent) {
                $this->rollback();
                $this->error = PageContentDao::instance()->getError();
                return 0;
            }

            // 保存系统日志
            if ($adminID > 0) {
                $record = AdminLogDao::instance()->record([
                    'uid' => $adminID,
                    'module' => 'cms',
                    'action' => '添加独立页面',
                    'content' => '添加独立页面: ' . $data['title'] . ', ID: ' . $page_id,
                    'sid' => $page_id
                ]);
                if (!$record) {
                    $this->rollback();
                    $this->error = '记录操作日志失败, ' . AdminLogDao::instance()->getError();
                    return 0;
                }
            }

            $this->commit();
            return $page_id;
        } catch (Throwable $e) {
            $this->rollback();
            $this->error = '添加页面异常';
            Logger::instance()->channel()->error('Add CMS page article exception. msg: ' . $e->getMessage());
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
        $info = $this->where('id', $data['idx'])->get();
        if (!$info) {
            $this->error = '页面不存在';
            return false;
        }
        if ($info['status'] == PageEmun::PAGE_STATUS['publish']) {
            $this->error = '页面已发布，不能修改';
            return false;
        }

        $data['uid'] = $adminID;
        $this->startTrans();
        try {
            Logger::instance()->channel()->info('Edit CMS page');
            // 保存基本信息
            $field = ['title', 'remark', 'img', 'keywords', 'description', 'view',  'sort', 'uid'];
            $save = $this->allowField($field)->where('id', $info['id'])->save($data);
            if (!$save) {
                $this->rollback();
                $this->error = '编辑页面失败';
                return false;
            }
            // 保存内容
            $saveContent = PageContentDao::instance()->edit($info['id'], $data['content'], intval($data['type']));
            if (!$saveContent) {
                $this->rollback();
                $this->error = PageContentDao::instance()->getError();
                return false;
            }

            // 保存系统日志
            if ($adminID > 0) {
                $record = AdminLogDao::instance()->record([
                    'uid' => $adminID,
                    'module' => 'cms',
                    'action' => '编辑独立页面',
                    'content' => '编辑独立页面: ' . $data['title'] . ', ID: ' . $info['id'],
                    'sid' => $info['id']
                ]);
                if (!$record) {
                    $this->rollback();
                    $this->error = '记录操作日志失败, ' . AdminLogDao::instance()->getError();
                    return false;
                }
            }

            $this->commit();
            return true;
        } catch (Throwable $e) {
            $this->rollback();
            $this->error = '编辑页面异常';
            Logger::instance()->channel()->error('Edit CMS page exception. msg: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 修改状态
     *
     * @param array $data
     * @param integer $adminID
     * @return boolean
     */
    public function status(array $data, int $adminID): bool
    {
        $check = $this->validate()->data($data)->scope('status')->check();
        if (!$check) {
            $this->error = $this->validate()->getError();
            return false;
        }

        $info = $this->where('id', $data['idx'])->get();
        if (!$info) {
            $this->error = '页面不存在';
            return false;
        }

        if ($info['status'] == $data['status']) {
            $this->error = '状态已修改';
            return false;
        }
        switch ($data['status']) {
            case PageEmun::PAGE_STATUS['publish']:
            case PageEmun::PAGE_STATUS['reject']:
                // 审核
                if ($info['status'] != PageEmun::PAGE_STATUS['draft']) {
                    $this->error = '页面无法审核';
                    return false;
                }
                break;
            case PageEmun::PAGE_STATUS['draft']:
                // 恢复为草稿
                if (!in_array($info['status'], [PageEmun::PAGE_STATUS['disable'], PageEmun::PAGE_STATUS['reject']])) {
                    $this->error = '页面不支持恢复为草稿';
                    return false;
                }
        }

        $statusMsg = PageEmun::PAGE_STATUS_TITLE[$data['status']];
        $this->startTrans();
        try {
            Logger::instance()->channel()->info('Edit CMS page status');
            $saveMessage = $this->where('id', $info['id'])->save(['status' => $data['status']]);
            if (!$saveMessage) {
                $this->rollback();
                $this->error = '页面' . $statusMsg . '失败';
                return false;
            }

            // 记录系统日志
            if ($adminID > 0) {
                $record = AdminLogDao::instance()->record([
                    'uid' => $adminID,
                    'module' => 'cms',
                    'action' => '页面' . $statusMsg,
                    'content' => '页面' . $statusMsg . ', 标题: ' . $info['title'] . ', ID: ' . $info['id'],
                    'sid' => $info['id']
                ]);
                if (!$record) {
                    $this->rollback();
                    $this->error = '记录操作日志失败, ' . AdminLogDao::instance()->getError();
                    return false;
                }
            }

            $this->commit();
            return true;
        } catch (Throwable $e) {
            $this->rollback();
            $this->error = '页面' . $statusMsg . '异常';
            Logger::instance()->channel()->error('Edit CMS page status exception. msg: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 修改互动数据
     *
     * @param array $data   请求参数
     * @param integer $adminID  管理员ID
     * @return boolean
     */
    public function interact(array $data, int $adminID): bool
    {
        $check = $this->validate()->data($data)->scope('interact')->check();
        if (!$check) {
            $this->error = $this->validate()->getError();
            return false;
        }
        $info = $this->where('id', $data['idx'])->get();
        if (!$info) {
            $this->error = '页面不存在';
            return false;
        }

        $this->startTrans();
        try {
            Logger::instance()->channel()->info('Edit CMS page interact');
            // 保存基本信息
            $field = ['read', 'like', 'bad'];
            $save = $this->allowField($field)->where('id', $info['id'])->save($data);
            if (!$save) {
                $this->rollback();
                $this->error = '编辑页面交互数据失败';
                return false;
            }

            // 保存日志
            if ($adminID > 0) {
                $record = AdminLogDao::instance()->record([
                    'uid' => $adminID,
                    'module' => 'cms',
                    'action' => '编辑独立页面交互数据',
                    'content' => '编辑独立页面交互数据: ' . $info['title'] . ', ID: ' . $info['id'],
                    'sid' => $info['id']
                ]);
                if (!$record) {
                    $this->rollback();
                    $this->error = '记录操作日志失败, ' . AdminLogDao::instance()->getError();
                    return false;
                }
            }

            $this->commit();
            return true;
        } catch (Throwable $e) {
            $this->rollback();
            $this->error = '编辑页面交互数据异常';
            Logger::instance()->channel()->error('Edit CMS page interact exception. msg: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 修改展示设置
     *
     * @param array $data   请求参数
     * @param integer $adminID  管理员ID
     * @return boolean
     */
    public function displays(array $data, int $adminID): bool
    {
        $check = $this->validate()->data($data)->scope('displays')->check();
        if (!$check) {
            $this->error = $this->validate()->getError();
            return false;
        }
        $info = $this->where('id', $data['idx'])->get();
        if (!$info) {
            $this->error = '页面不存在';
            return false;
        }

        $this->startTrans();
        try {
            Logger::instance()->channel()->info('Edit CMS page displays');
            // 保存基本信息
            $field = ['is_hot', 'is_recommend', 'is_top', 'is_comment'];
            $save = $this->allowField($field)->where('id', $info['id'])->save($data);
            if (!$save) {
                $this->rollback();
                $this->error = '编辑独立页面展示信息失败';
                return false;
            }

            // 保存系统日志
            if ($adminID > 0) {
                $record = AdminLogDao::instance()->record([
                    'uid' => $adminID,
                    'module' => 'cms',
                    'action' => '编辑独立页面展示信息',
                    'content' => '编辑独立页面展示信息: ' . $info['title'] . ', ID: ' . $info['id'],
                    'sid' => $info['id']
                ]);
                if (!$record) {
                    $this->rollback();
                    $this->error = '记录操作日志失败, ' . AdminLogDao::instance()->getError();
                    return false;
                }
            }

            $this->commit();
            return true;
        } catch (Throwable $e) {
            $this->rollback();
            $this->error = '编辑独立页面展示信息异常';
            Logger::instance()->channel()->error('Edit CMS page displays exception. msg: ' . $e->getMessage());
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
        // 按用户ID
        if (isset($option['uid']) && $this->validate()->int($option['uid'])) {
            $query->where('uid', intval($option['uid']));
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
        $order = 'sort';
        if (isset($option['order']) && in_array($option['order'], ['id', 'create_time', 'read', 'like', 'bad'])) {
            $order = $option['order'];
        }
        // 排序类型，默认 DESC
        $sort = 'DESC';
        if (isset($option['sort']) && in_array(strtoupper($option['sort']), ['ASC', 'DESC'])) {
            $sort = $option['sort'];
        }

        return $query->order($order, $sort);
    }
}
