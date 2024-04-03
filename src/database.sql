CREATE TABLE `cms_ads` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '名称',
  `width` int(4) UNSIGNED NOT NULL DEFAULT 0 COMMENT '宽度',
  `height` int(4) UNSIGNED NOT NULL DEFAULT 0 COMMENT '高度',
  `remark` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '备注',
  `status` tinyint(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态: 1-有效 0-无效',
  `update_time` int(10) UNSIGNED NOT NULL,
  `create_time` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '广告表' ROW_FORMAT = DYNAMIC;

CREATE TABLE `cms_ads_img` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `ads_id` int(11) UNSIGNED NOT NULL COMMENT '所属广告ID',
  `img` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '图片地址',
  `url` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '链接地址',
  `target` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '_blank' COMMENT '打开方式',
  `sort` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '排序权重',
  `remark` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '备注',
  `status` tinyint(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态: 1-有效 0-无效',
  `update_time` int(10) UNSIGNED NOT NULL,
  `create_time` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '广告资源表' ROW_FORMAT = DYNAMIC;

CREATE TABLE `cms_article` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `uid` int(11) UNSIGNED NOT NULL COMMENT '创建人ID',
  `cate_id` int(11) UNSIGNED NOT NULL COMMENT '所属分类ID',
  `title` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '标题',
  `remark` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '备注描述',
  `img` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '封面图URL',
  `tags` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '标签，多个以 , 分割',
  `keywords` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'SEO keywords',
  `description` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'SEO description',
  `send_time` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '发布时间',
  `read` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '阅读数',
  `like` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '点赞数',
  `bad` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '点踩数',
  `is_hot` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否热门',
  `is_recommend` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否推荐',
  `is_top` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否置顶',
  `is_comment` tinyint(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '是否允许评论',
  `sort` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '排序权重',
  `status` tinyint(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态: 0-已下线 1-草稿 2-待审核 3-已发布 4-已拒绝',
  `update_time` int(10) UNSIGNED NOT NULL,
  `create_time` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '文章表' ROW_FORMAT = DYNAMIC;

CREATE TABLE `cms_article_content` (
  `acticle_id` int(11) UNSIGNED NOT NULL COMMENT '文章ID',
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '文章内容',
  `update_time` int(10) UNSIGNED NOT NULL,
  `create_time` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`acticle_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '文章内容表' ROW_FORMAT = DYNAMIC;

CREATE TABLE `cms_article_tag` (
  `acticle_id` int(11) UNSIGNED NOT NULL COMMENT '文章ID',
  `tag_id` int(11) UNSIGNED NOT NULL COMMENT '标签ID',
  PRIMARY KEY (`acticle_id`, `tag_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '文章标签表' ROW_FORMAT = DYNAMIC;

CREATE TABLE `cms_cate` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `pid` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '父级ID',
  `type` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '0-栏目主页 1-栏目列表 2-跳转链接',
  `title` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '名称',
  `img` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '图标',
  `url` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '跳转地址',
  `remark` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '备注',
  `sort` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '排序权重',
  `menu` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否菜单: 1-是 0-否',
  `status` tinyint(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态: 1-有效 0-无效',
  `update_time` int(10) UNSIGNED NOT NULL,
  `create_time` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '栏目分类表' ROW_FORMAT = DYNAMIC;

CREATE TABLE `cms_comment` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `uid` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '用户ID',
  `module` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'app' COMMENT '所属模块 app-系统留言 doc-内容评论 page-单页评论',
  `union_id` int(11) NOT NULL DEFAULT 0 COMMENT '模块关联记录ID',
  `pid` bigint(20) UNSIGNED NOT NULL DEFAULT 0 COMMENT '回复评论的ID',
  `pids` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '回复的父级ID 多个中间按顺序用逗号,分隔',
  `content` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '内容',
  `like` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '点赞数',
  `bad` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '被踩数',
  `is_hot` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否热门',
  `is_top` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否置顶',
  `ip` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '操作IP',
  `sort` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '排序权重',
  `status` tinyint(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '0-已删除 1-待审核 2-通过 3-未通过',
  `update_time` int(10) UNSIGNED NOT NULL,
  `create_time` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '评论表' ROW_FORMAT = COMPACT;

CREATE TABLE `cms_page` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `uid` int(11) UNSIGNED NOT NULL COMMENT '创建人ID',
  `title` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '名称',
  `remark` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '备注描述',
  `img` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '封面图URL',
  `keywords` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'SEO keywords',
  `description` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'SEO description',
  `view` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '视图模板名称',
  `read` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '阅读数',
  `like` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '点赞数',
  `bad` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '点踩数',
  `is_hot` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否热门',
  `is_recommend` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否推荐',
  `is_top` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否置顶',
  `is_comment` tinyint(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '是否允许评论',
  `sort` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '排序权重',
  `status` tinyint(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态: 0-已下线 1-待审核 2-已发布 3-已拒绝',
  `update_time` int(10) UNSIGNED NOT NULL,
  `create_time` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '独立页面表' ROW_FORMAT = DYNAMIC;

CREATE TABLE `cms_page_content` (
  `page_id` int(11) UNSIGNED NOT NULL COMMENT '单页ID',
  `type` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '0-HTML 1-MarkDown',
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '单页内容',
  `update_time` int(10) UNSIGNED NOT NULL,
  `create_time` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`page_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '单页内容表' ROW_FORMAT = DYNAMIC;