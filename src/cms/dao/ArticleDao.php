<?php

declare(strict_types=1);

namespace plugins\cms\dao;

use Throwable;
use mon\log\Logger;
use mon\thinkOrm\Dao;
use mon\util\Instance;
use plugins\admin\dao\AdminLogDao;
use plugins\ucenter\dao\UserLogDao;
use plugins\cms\contract\ArticleEmun;
use plugins\cms\validate\ArticleValidate;

/**
 * 站内信内容Dao操作
 * 
 * @author Mon <985558837@qq.com>
 * @version 1.0.0
 */
class ArticleDao extends Dao
{
    use Instance;

    /**
     * 操作表
     *
     * @var string
     */
    protected $table = 'cms_article';

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
    protected $validate = ArticleValidate::class;

    /**
     * 获取文章内容
     *
     * @param integer $id   文章ID
     * @param boolean $isPublish   是否已发布
     * @return array
     */
    public function getInfo(int $id, bool $isPublish = false): array
    {
        $cateTable = CateDao::instance()->getTable();
        $contentTable = ArticleContentDao::instance()->getTable();
        $field = ['a.*', 'b.title AS cate_title', 'b.img AS cate_img', 'c.content'];
        $where = ['a.id' => $id];
        if ($isPublish) {
            $where['a.status'] = 3;
        }
        $data = $this->alias('a')->where($where)->field($field)->join($cateTable . ' b', 'a.cate_id=b.id')->join($contentTable . ' c', 'a.id=c.acticle_id')->get();
        if (!$data) {
            return [];
        }
        return $data;
    }

    /**
     * 新增
     *
     * @param array $data   请求参数
     * @param integer $uid  创建人用户ID，系统创建则为0，大于0则记录用户日志
     * @param integer $adminID  系统用户ID，大于0则记录系统日志
     * @return integer  文章ID, 0则表示失败
     */
    public function add(array $data, int $uid, int $adminID = 0): int
    {
        $check = $this->validate()->data($data)->scope('add')->check();
        if (!$check) {
            $this->error = $this->validate()->getError();
            return 0;
        }

        $data['uid'] = $uid;
        $this->startTrans();
        try {
            Logger::instance()->channel()->info('Add CMS article');
            // 保存基本信息
            $field = ['uid', 'cate_id', 'title', 'remark', 'img', 'tags', 'keywords', 'description', 'send_time', 'sort'];
            $article_id = $this->allowField($field)->save($data, true, true);
            if (!$article_id) {
                $this->rollback();
                $this->error = '创建文章失败';
                return 0;
            }
            // 保存内容
            $saveContent = ArticleContentDao::instance()->add($article_id, $data['content']);
            if (!$saveContent) {
                $this->rollback();
                $this->error = ArticleContentDao::instance()->getError();
                return 0;
            }

            // 记录用户日志
            if ($uid > 0) {
                $record = UserLogDao::instance()->record([
                    'uid' => $uid,
                    'action' => '创建文章',
                    'content' => '用户创建文章：' . $data['title'],
                    'sid' => $article_id
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
                    'action' => '添加文章',
                    'content' => '添加文章: ' . $data['title'] . ', ID: ' . $article_id,
                    'sid' => $article_id
                ]);
                if (!$record) {
                    $this->rollback();
                    $this->error = '记录操作日志失败, ' . AdminLogDao::instance()->getError();
                    return false;
                }
            }

            $this->commit();
            return $article_id;
        } catch (Throwable $e) {
            $this->rollback();
            $this->error = '创建文章异常';
            Logger::instance()->channel()->error('Add CMS article exception. msg: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * 编辑文章
     *
     * @param array $data   请求参数
     * @param integer $uid  创建人用户ID，系统创建则为0，大于0则记录用户日志
     * @param integer $adminID  系统用户ID，大于0则记录系统日志
     * @return boolean
     */
    public function edit(array $data, int $uid = 0, int $adminID = 0): bool
    {
        $check = $this->validate()->data($data)->scope('edit')->check();
        if (!$check) {
            $this->error = $this->validate()->getError();
            return false;
        }
        $info = $this->where('id', $data['idx'])->get();
        if (!$info) {
            $this->error = '文章不存在';
            return false;
        }
        if (!in_array($info['status'], [ArticleEmun::ARTICLE_STATUS['draft'], ArticleEmun::ARTICLE_STATUS['audit']])) {
            $this->error = '文章不是草稿或待审核状态，不能修改';
            return false;
        }
        if ($adminID <= 0 && $info['uid'] != $uid) {
            // 用户只能修改自己的文章
            $this->error = '文章不存在！';
            return false;
        }

        $data['uid'] = $adminID > 0 ? $info['uid'] : $uid;
        $this->startTrans();
        try {
            Logger::instance()->channel()->info('Edit CMS article');
            // 保存基本信息
            $field = ['uid', 'cate_id', 'title', 'remark', 'img', 'tags', 'keywords', 'description', 'send_time', 'sort'];
            $save = $this->allowField($field)->where('id', $info['id'])->save($data);
            if (!$save) {
                $this->rollback();
                $this->error = '编辑文章失败';
                return false;
            }
            // 保存内容
            $saveContent = ArticleContentDao::instance()->edit($info['id'], $data['content']);
            if (!$saveContent) {
                $this->rollback();
                $this->error = ArticleContentDao::instance()->getError();
                return false;
            }

            // 记录用户日志
            if ($uid > 0) {
                $record = UserLogDao::instance()->record([
                    'uid' => $uid,
                    'action' => '编辑文章',
                    'content' => '用户编辑文章：' . $data['title'],
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
                    'action' => '编辑文章',
                    'content' => '编辑文章: ' . $data['title'] . ', ID: ' . $info['id'],
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
            $this->error = '编辑文章异常';
            Logger::instance()->channel()->error('Edit CMS article exception. msg: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 修改文章状态
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
            $this->error = '文章不存在';
            return false;
        }

        if ($info['status'] == $data['status']) {
            $this->error = '状态已修改';
            return false;
        }
        switch ($data['status']) {
            case '1':
                // 恢复草稿
                if (!in_array($info['status'], [ArticleEmun::ARTICLE_STATUS['disable'], ArticleEmun::ARTICLE_STATUS['audit'], ArticleEmun::ARTICLE_STATUS['reject']])) {
                    $this->error = '文章状态不能修改为草稿';
                    return false;
                }
                break;
            case '2':
                // 提交审核
                if ($info['status'] != ArticleEmun::ARTICLE_STATUS['draft']) {
                    $this->error = '只能提交草稿状态的文章';
                    return false;
                }
                break;
            case '3':
            case '4':
                // 审核
                if ($info['status'] != ArticleEmun::ARTICLE_STATUS['audit']) {
                    $this->error = '只能审核状态为审核中的文章';
                    return false;
                }
                break;
        }

        $statusMsg = ArticleEmun::ARTICLE_STATUS_TITLE[$data['status']];
        $this->startTrans();
        try {
            Logger::instance()->channel()->info('Edit CMS article status');
            $saveMessage = $this->where('id', $info['id'])->save(['status' => $data['status']]);
            if (!$saveMessage) {
                $this->rollback();
                $this->error = '修改文章状态【' . $statusMsg . '】失败';
                return false;
            }

            if ($adminID > 0) {
                $record = AdminLogDao::instance()->record([
                    'uid' => $adminID,
                    'module' => 'cms',
                    'action' => '修改文章状态【' . $statusMsg . '】',
                    'content' => '修改文章状态【' . $statusMsg . '】' . ', 标题: ' . $info['title'] . ', ID: ' . $info['id'],
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
            $this->error = '修改文章状态【' . $statusMsg . '】异常';
            Logger::instance()->channel()->error('Edit CMS article status exception. msg: ' . $e->getMessage());
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
            $this->error = '文章不存在';
            return false;
        }

        $this->startTrans();
        try {
            Logger::instance()->channel()->info('Edit CMS article displays');
            // 保存基本信息
            $field = ['is_hot', 'is_recommend', 'is_top', 'is_comment'];
            $save = $this->allowField($field)->where('id', $info['id'])->save($data);
            if (!$save) {
                $this->rollback();
                $this->error = '编辑文章展示信息失败';
                return false;
            }

            // 保存系统日志
            if ($adminID > 0) {
                $record = AdminLogDao::instance()->record([
                    'uid' => $adminID,
                    'module' => 'cms',
                    'action' => '编辑文章展示信息',
                    'content' => '编辑文章展示信息: ' . $info['title'] . ', ID: ' . $info['id'],
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
            $this->error = '编辑文章展示信息异常';
            Logger::instance()->channel()->error('Edit CMS article displays exception. msg: ' . $e->getMessage());
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
            $this->error = '文章不存在';
            return false;
        }

        $this->startTrans();
        try {
            Logger::instance()->channel()->info('Edit CMS article interact');
            // 保存基本信息
            $field = ['read', 'like', 'bad'];
            $save = $this->allowField($field)->where('id', $info['id'])->save($data);
            if (!$save) {
                $this->rollback();
                $this->error = '编辑文章交互数据失败';
                return false;
            }

            // 保存日志
            if ($adminID > 0) {
                $record = AdminLogDao::instance()->record([
                    'uid' => $adminID,
                    'module' => 'cms',
                    'action' => '编辑单文章交互数据',
                    'content' => '编辑单文章交互数据: ' . $info['title'] . ', ID: ' . $info['id'],
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
            $this->error = '编辑文章交互数据异常';
            Logger::instance()->channel()->error('Edit CMS article interact exception. msg: ' . $e->getMessage());
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
        $field = ['a.*', 'c.title AS cate'];
        $query->alias('a')->join(CateDao::instance()->getTable() . ' c', 'a.cate_id=c.id')->field($field);
        // ID搜索
        if (isset($option['idx']) &&  $this->validate()->id($option['idx'])) {
            $query->where('a.id', intval($option['idx']));
        }
        // 按用户ID
        if (isset($option['uid']) && $this->validate()->int($option['uid'])) {
            $query->where('a.uid', intval($option['uid']));
        }
        // 按分类
        if (isset($option['cate_id']) &&  $this->validate()->id($option['cate_id'])) {
            $query->where('a.cate_id', intval($option['cate_id']));
        }
        // 按名称
        if (isset($option['title']) && is_string($option['title']) && !empty($option['title'])) {
            $query->whereLike('a.title', trim($option['title']) . '%');
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
        // 发布时间搜索
        if (isset($option['send_start_time']) && $this->validate()->int($option['send_start_time'])) {
            $query->where('a.send_time', '>=', intval($option['send_start_time']));
        }
        if (isset($option['send_end_time']) && $this->validate()->int($option['send_end_time'])) {
            $query->where('a.send_time', '<=', intval($option['send_end_time']));
        }

        // 排序字段，默认id
        $order = 'a.id';
        if (isset($option['order']) && in_array($option['order'], ['id', 'send_time', 'read', 'like', 'bad'])) {
            $order = 'a.' . $option['order'];
        }
        // 排序类型，默认 DESC
        $sort = 'DESC';
        if (isset($option['sort']) && in_array(strtoupper($option['sort']), ['ASC', 'DESC'])) {
            $sort = $option['sort'];
        }

        return $query->order($order, $sort);
    }
}
