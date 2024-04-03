<?php

declare(strict_types=1);

namespace plugins\cms\dao;

use mon\thinkOrm\Dao;
use mon\util\Instance;

/**
 * 单页内存Dao操作
 * 
 * @author Mon <985558837@qq.com>
 * @version 1.0.0
 */
class PageContentDao extends Dao
{
    use Instance;

    /**
     * 操作表
     *
     * @var string
     */
    protected $table = 'cms_page_content';

    /**
     * 自动写入时间戳
     *
     * @var boolean
     */
    protected $autoWriteTimestamp = true;

    /**
     * 新增单页内容
     *
     * @param integer $page_id  单页ID
     * @param string $content   单页内容
     * @param integer $type     内容类型0-HTMl 1-MarkDown
     * @return boolean
     */
    public function add(int $page_id, string $content, int $type): bool
    {
        $save = $this->save([
            'page_id' => $page_id,
            'type' => $type,
            'content' => $content
        ], true);
        if (!$save) {
            $this->error = '保存单页内容失败';
            return false;
        }

        return true;
    }

    /**
     * 编辑单页内容
     *
     * @param integer $page_id
     * @param string $content
     * @param integer $type     内容类型0-HTMl 1-MarkDown
     * @return boolean
     */
    public function edit(int $page_id, string $content, int $type): bool
    {
        $save = $this->where('page_id', $page_id)->save(['content' => $content, 'type' => $type]);
        if (!$save) {
            $this->error = '保存单页内容失败';
            return false;
        }

        return true;
    }
}
