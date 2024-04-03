<?php

declare(strict_types=1);

namespace plugins\cms\dao;

use Throwable;
use mon\log\Logger;
use mon\thinkOrm\Dao;
use mon\http\Context;
use mon\http\Request;
use mon\util\Instance;
use plugins\ucenter\dao\UserDao;
use plugins\admin\dao\AdminLogDao;
use plugins\ucenter\dao\UserLogDao;
use plugins\cms\contract\CommentEmun;
use plugins\cms\validate\CommentValidate;

/**
 * 内容评论Dao操作
 * 
 * @author Mon <985558837@qq.com>
 * @version 1.0.0
 */
class CommentDao extends Dao
{
    use Instance;

    /**
     * 操作表
     *
     * @var string
     */
    protected $table = 'cms_comment';

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
    protected $validate = CommentValidate::class;

    /**
     * 模块属性
     *
     * @var array
     */
    protected $moduleAttr = [
        'app' => ['name' => '系统留言'],
        'doc' => [
            // 模块名
            'name' => '内容评论',
            // 关联配置
            'union' => [
                // 关联模型名
                'model' => ArticleDao::class,
                // 调用方法，空则直接调用get方法
                'handle' => '',
                // 预览URL根路径
                'link' => '/cms/article/preview',
            ]
        ],
        'page' => [
            'name' => '单页评论',
            // 关联配置
            'union' => [
                // 关联模型名
                'model' => PageDao::class,
                // 调用方法，空则直接调用get方法
                'handle' => '',
                // 预览URL根路径
                'link' => '',
            ]
        ]
    ];

    /**
     * 获取模块属性配置
     *
     * @return array
     */
    public function getModuleList(): array
    {
        return $this->moduleAttr;
    }

    /**
     * 获取评论信息
     *
     * @param integer $id
     * @return array
     */
    public function getInfo(int $id): array
    {
        $field = ['a.*', 'u.nickname'];
        $data = $this->alias('a')->join(UserDao::instance()->getTable() . ' u', 'a.uid=u.id', 'left')->where('a.id', $id)->field($field)->get();
        if (!$data) {
            $this->error = '评论信息不存在';
            return false;
        }
        $data['status_name'] = CommentEmun::COMMENT_STATUS_TITLE[$data['status']] ?? '未知';

        $module = $this->moduleAttr[$data['module']] ?? [];
        $data['module_name'] = $module['name'] ?? '未知';
        if (isset($module['union']) && !empty($module['union'])) {
            $unionDao = $module['union']['model'];
            /** @var Dao $unionDao */
            $unionDao = $unionDao::instance();
            $unionHandle = $module['union']['handle'];
            $unionData = [];
            if (empty($unionHandle)) {
                // 不存在union方法，直接使用find
                $unionData = $unionDao->where('id', $data['union_id'])->get();
            } else {
                $unionData = call_user_func([$unionDao, $unionHandle], $data['union_id']);
            }
            $data['union_data'] = $unionData;
            // 关联预览地址
            $data['union_link'] = $module['union']['link'] ?: '';
        }

        return $data;
    }

    /**
     * 新增
     *
     * @param array $data   评论数据
     * @param integer $uid  创建人用户ID，系统创建则为0，大于0则记录用户日志
     * @param integer $adminID  系统用户ID，大于0则记录系统日志
     * @param string $ip  IP地址
     * @return integer 评论ID 0则失败
     */
    public function add(array $data, int $uid, int $adminID = 0, string $ip = ''): int
    {
        $check = $this->validate()->data($data)->scope('add')->check();
        if (!$check) {
            $this->error = $this->validate()->getError();
            return 0;
        }

        $data['uid'] = $uid;
        if (empty($ip)) {
            $ip = Context::get(Request::class)->ip();
        }
        $data['ip'] = $ip;
        // pid > 0，回复
        if ($data['pid'] > 0) {
            $parentInfo = $this->where('id', $data['pid'])->get();
            if (!$parentInfo) {
                $this->error = '获取回复信息失败';
                return 0;
            }
            $data['pids'] = $parentInfo['pids'] . ',' . $data['pid'];
        } else {
            $data['pids'] = $data['pid'];
        }

        $this->startTrans();
        try {
            Logger::instance()->channel()->info('Add CMS comment');
            // 保存基本信息
            $field = ['module', 'union_id', 'pid', 'content', 'uid', 'ip', 'pids', 'status'];
            $comment_id = $this->allowField($field)->save($data, true, true);
            if (!$comment_id) {
                $this->rollback();
                $this->error = '发表评论失败';
                return 0;
            }

            // 记录用户日志
            if ($uid > 0) {
                $record = UserLogDao::instance()->record([
                    'uid' => $uid,
                    'action' => '发表评论',
                    'content' => '用户发表评论，module：' . $data['module'] . ', union：' . $data['union_id'],
                    'sid' => $comment_id
                ]);
                if (!$record) {
                    $this->rollback();
                    $this->error = '记录用户日志失败, ' . UserLogDao::instance()->getError();
                    return 0;
                }
            }

            // 记录系统日志
            if ($adminID > 0) {
                $record = AdminLogDao::instance()->record([
                    'uid' => $adminID,
                    'module' => 'cms',
                    'action' => '发表评论',
                    'content' => '发表评论，module：' . $data['module'] . ', union：' . $data['union_id'],
                    'sid' => $comment_id
                ]);
                if (!$record) {
                    $this->rollback();
                    $this->error = '记录操作日志失败, ' . AdminLogDao::instance()->getError();
                    return 0;
                }
            }

            $this->commit();
            return $comment_id;
        } catch (Throwable $e) {
            $this->rollback();
            $this->error = '发表评论异常';
            Logger::instance()->channel()->error('Add CMS comment exception. msg: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * 编辑
     *
     * @param array $data   评论数据
     * @param integer $uid  创建人用户ID，系统创建则为0，大于0则记录用户日志
     * @param integer $adminID  系统用户ID，大于0则记录系统日志
     * @param string $ip  IP地址
     * @return boolean
     */
    public function edit(array $data, int $uid, int $adminID = 0, string $ip = ''): bool
    {
        $check = $this->validate()->data($data)->scope('edit')->check();
        if (!$check) {
            $this->error = $this->validate()->getError();
            return false;
        }
        $info = $this->where('id', $data['idx'])->get();
        if (!$info) {
            $this->error = '评论不存在';
            return false;
        }
        if ($adminID <= 0 && $info['uid'] != $uid) {
            $this->error = '只允许修改自己的评论信息';
            return false;
        }

        if (empty($ip)) {
            $ip = Context::get(Request::class)->ip();
        }
        $data['ip'] = $ip;

        $this->startTrans();
        try {
            Logger::instance()->channel()->info('Edit CMS comment');
            // 保存
            $save = $this->allowField(['content', 'status', 'ip'])->where('id', $info['id'])->save($data);
            if (!$save) {
                $this->rollback();
                $this->error = '修改评论失败';
                return false;
            }

            // 记录用户日志
            if ($uid > 0) {
                $record = UserLogDao::instance()->record([
                    'uid' => $uid,
                    'action' => '编辑评论',
                    'content' => '用户编辑评论：' . $info['id'],
                    'sid' => $info['id']
                ]);
                if (!$record) {
                    $this->rollback();
                    $this->error = '记录用户日志失败, ' . UserLogDao::instance()->getError();
                    return false;
                }
            }

            // 记录系统日志
            if ($adminID > 0) {
                $record = AdminLogDao::instance()->record([
                    'uid' => $adminID,
                    'module' => 'cms',
                    'action' => '编辑评论',
                    'content' => '编辑评论：' . $info['id'],
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
            $this->error = '编辑评论异常';
            Logger::instance()->channel()->error('Edit CMS comment exception. msg: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * 修改展示数据
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
            $this->error = '评论不存在';
            return false;
        }

        $this->startTrans();
        try {
            Logger::instance()->channel()->info('Edit CMS comment displays');
            // 保存基本信息
            $field = ['is_hot', 'is_top', 'sort'];
            $save = $this->allowField($field)->where('id', $info['id'])->save($data);
            if (!$save) {
                $this->rollback();
                $this->error = '编辑评论展示数据失败';
                return false;
            }

            // 保存日志
            if ($adminID > 0) {
                $record = AdminLogDao::instance()->record([
                    'uid' => $adminID,
                    'module' => 'cms',
                    'action' => '编辑评论展示数据',
                    'content' => '编辑评论展示数据：' . $info['id'],
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
            $this->error = '编辑评论展示数据异常';
            Logger::instance()->channel()->error('Edit CMS comment displays exception. msg: ' . $e->getMessage());
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
            $this->error = '评论不存在';
            return false;
        }

        $this->startTrans();
        try {
            Logger::instance()->channel()->info('Edit CMS comment interact');
            // 保存基本信息
            $field = ['like', 'bad'];
            $save = $this->allowField($field)->where('id', $info['id'])->save($data);
            if (!$save) {
                $this->rollback();
                $this->error = '编辑评论交互数据失败';
                return false;
            }

            // 保存日志
            if ($adminID > 0) {
                $record = AdminLogDao::instance()->record([
                    'uid' => $adminID,
                    'module' => 'cms',
                    'action' => '编辑评论交互数据',
                    'content' => '编辑评论交互数据：' . $info['id'],
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
            $this->error = '编辑评论交互数据异常';
            Logger::instance()->channel()->error('Edit CMS comment interact exception. msg: ' . $e->getMessage());
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
            $this->error = '评论不存在';
            return false;
        }

        if ($info['status'] == $data['status']) {
            $this->error = '状态已修改';
            return false;
        }
        switch ($data['status']) {
            case CommentEmun::COMMENT_STATUS['audit']:
                // 重新审核
                if (!in_array($info['status'], [CommentEmun::COMMENT_STATUS['disable'], CommentEmun::COMMENT_STATUS['reject']])) {
                    $this->error = '评论无法重新提交审核!';
                    return false;
                }
                break;
            case '2':
                // 审核通过
                if (!in_array($info['status'], [CommentEmun::COMMENT_STATUS['audit'], CommentEmun::COMMENT_STATUS['reject']])) {
                    $this->error = '评论无法审核!';
                    return false;
                }
                break;
            case '3':
                // 审核不通过
                if ($info['status'] != CommentEmun::COMMENT_STATUS['audit']) {
                    $this->error = '评论无法审核';
                    return false;
                }
        }

        $statusMsg = CommentEmun::COMMENT_STATUS_TITLE[$data['status']];
        $this->startTrans();
        try {
            Logger::instance()->channel()->info('Edit CMS comment status');
            $saveMessage = $this->where('id', $info['id'])->save(['status' => $data['status']]);
            if (!$saveMessage) {
                $this->rollback();
                $this->error = '评论审核【' . $statusMsg . '】失败';
                return false;
            }

            if ($adminID > 0) {
                $record = AdminLogDao::instance()->record([
                    'uid' => $adminID,
                    'module' => 'cms',
                    'action' => '修改评论状态',
                    'content' => '修改评论状态【' . $statusMsg . '】, ID: ' . $info['id'],
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
            $this->error = '修改评论状态【' . $statusMsg . '】异常';
            Logger::instance()->channel()->error('Edit CMS comment status exception. msg: ' . $e->getMessage());
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
        $count = $this->scope('list', $data)->count('a.id');

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
        $field = ['a.*', 'u.nickname', 'u.avatar', 'u.level', 'u.sex'];
        $query->alias('a')->join(UserDao::instance()->getTable() . ' u', 'a.uid=u.id', 'left')->field($field);
        // ID搜索
        if (isset($option['idx']) &&  $this->validate()->id($option['idx'])) {
            $query->where('a.id', intval($option['idx']));
        }
        // 按模块
        if (isset($option['module']) && is_string($option['module']) && !empty($option['module'])) {
            $query->where('a.module', trim($option['module']));
        }
        // 按关联ID
        if (isset($option['union']) && $this->validate()->int($option['union'])) {
            $query->where('a.union_id', intval($option['union']));
        }
        // 按用户ID
        if (isset($option['uid']) && $this->validate()->int($option['uid'])) {
            $query->where('a.uid', intval($option['uid']));
        }
        // 按pid
        if (isset($option['pid']) && $this->validate()->int($option['pid'])) {
            $query->where('a.pid', intval($option['pid']));
        }
        // 按状态
        if (isset($option['hot']) && $this->validate()->int($option['hot'])) {
            $query->where('a.is_hot', intval($option['hot']));
        }
        // 按状态
        if (isset($option['top']) && $this->validate()->int($option['top'])) {
            $query->where('a.is_top', intval($option['top']));
        }
        // 按状态
        if (isset($option['status']) && $this->validate()->int($option['status'])) {
            $query->where('a.status', intval($option['status']));
        }
        // 创建时间搜索
        if (isset($option['start_time']) && $this->validate()->int($option['start_time'])) {
            $query->where('a.create_time', '>=', intval($option['start_time']));
        }
        if (isset($option['end_time']) && $this->validate()->int($option['end_time'])) {
            $query->where('a.create_time', '<=', intval($option['end_time']));
        }

        // 排序字段，默认id
        $order = 'a.id';
        if (isset($option['order']) && in_array($option['order'], ['id', 'create_time', 'like', 'bad', 'sort'])) {
            $order = 'a.' . $option['order'];
        }
        // 排序类型，默认 DESC
        $sort = 'DESC';
        if (isset($option['sort']) && in_array(strtoupper($option['sort']), ['ASC', 'DESC'])) {
            $sort = strtoupper($option['sort']);
        }

        return $query->order($order, $sort);
    }
}
