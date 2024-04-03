<?php

declare(strict_types=1);

namespace plugins\cms\dao;

use Throwable;
use mon\log\Logger;
use mon\thinkOrm\Dao;
use mon\util\Instance;
use plugins\admin\dao\AdminLogDao;
use plugins\cms\validate\AdsValidate;

/**
 * 广告位资源Dao操作
 * 
 * @author Mon <985558837@qq.com>
 * @version 1.0.0
 */
class AdsImgDao extends Dao
{
    use Instance;

    /**
     * 操作表
     *
     * @var string
     */
    protected $table = 'cms_ads_img';

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
    protected $validate = AdsValidate::class;

    /**
     * 新增
     *
     * @param array $data     请求参数
     * @param integer $adminID  管理员ID
     * @return integer 广告资源ID
     */
    public function add(array $data, int $adminID): int
    {
        $check = $this->validate()->data($data)->scope('add_img')->check();
        if (!$check) {
            $this->error = $this->validate()->getError();
            return 0;
        }

        $this->startTrans();
        try {
            Logger::instance()->channel()->info('Add blog Ads img');
            $img_id = $this->allowField(['ads_id', 'url', 'img', 'target', 'sort', 'remark', 'status'])->save($data, true, true);
            if (!$img_id) {
                $this->rollback();
                $this->error = '添加广告位资源失败';
                return 0;
            }

            // 记录系统日志
            if ($adminID > 0) {
                $record = AdminLogDao::instance()->record([
                    'uid' => $adminID,
                    'module' => 'cms',
                    'action' => '添加广告位资源',
                    'content' => '添加广告位资源: ID => ' . $img_id,
                    'sid' => $img_id
                ]);
                if (!$record) {
                    $this->rollback();
                    $this->error = '记录操作日志失败, ' . AdminLogDao::instance()->getError();
                    return 0;
                }
            }

            $this->commit();
            return $img_id;
        } catch (Throwable $e) {
            $this->rollback();
            $this->error = '添加广告位资源异常';
            Logger::instance()->channel()->error('Add blog Ads img exception. msg: ' . $e->getMessage());
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
        $check = $this->validate()->data($data)->scope('edit_img')->check();
        if (!$check) {
            $this->error = $this->validate()->getError();
            return false;
        }
        $info = $this->where('id', $data['idx'])->get();
        if (!$info) {
            $this->error = '广告位资源不存在';
            return false;
        }

        $this->startTrans();
        try {
            Logger::instance()->channel()->info('Edit blog Ads img');
            $save = $this->allowField(['ads_id', 'url', 'img', 'target', 'sort', 'remark', 'status'])->where('id', $info['id'])->save($data);
            if (!$save) {
                $this->rollback();
                $this->error = '编辑广告位资源失败';
                return false;
            }

            // 记录系统日志
            if ($adminID > 0) {
                $record = AdminLogDao::instance()->record([
                    'uid' => $adminID,
                    'module' => 'cms',
                    'action' => '编辑广告位资源',
                    'content' => '编辑广告位资源: ID => ' . $info['id'],
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
            $this->error = '编辑广告位资源异常';
            Logger::instance()->channel()->error('Edit blog Ads img exception. msg: ' . $e->getMessage());
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
        // 按广告位
        if (isset($option['ads']) && $this->validate()->id($option['ads'])) {
            $query->where('ads_id', intval($option['ads']));
        }
        // 按状态
        if (isset($option['status']) && $this->validate()->int($option['status'])) {
            $query->where('status', intval($option['status']));
        }

        // 排序字段，默认id
        $order = 'sort';
        if (isset($option['order']) && in_array($option['order'], ['id', 'status', 'sort', 'create_time', 'update_time'])) {
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
