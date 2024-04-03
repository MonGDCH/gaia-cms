<?php

declare(strict_types=1);

namespace plugins\cms\dao;

use mon\thinkOrm\Dao;
use mon\util\Instance;

/**
 * 文章内容Dao操作
 * 
 * @author Mon <985558837@qq.com>
 * @version 1.0.0
 */
class ArticleContentDao extends Dao
{
    use Instance;

    /**
     * 操作表
     *
     * @var string
     */
    protected $table = 'cms_article_content';

    /**
     * 自动写入时间戳
     *
     * @var boolean
     */
    protected $autoWriteTimestamp = true;

    /**
     * 新增文章内容
     *
     * @param integer $acticle_id   文章ID
     * @param string $content       文章内容
     * @return boolean
     */
    public function add(int $acticle_id, string $content): bool
    {
        $save = $this->save([
            'acticle_id' => $acticle_id,
            'content' => $content
        ], true);
        if (!$save) {
            $this->error = '保存文章内容失败';
            return false;
        }

        return true;
    }

    /**
     * 编辑文章内容
     *
     * @param integer $message_id
     * @param string $content
     * @return boolean
     */
    public function edit(int $acticle_id, string $content): bool
    {
        $save = $this->where('acticle_id', $acticle_id)->save(['content' => $content]);
        if (!$save) {
            $this->error = '保存文章内容失败';
            return false;
        }

        return true;
    }
}
