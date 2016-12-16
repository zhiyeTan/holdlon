DROP INDEX `account` ON `z_admin`;
DROP INDEX `password` ON `z_admin`;
DROP INDEX `parent_id` ON `z_arc_category`;
DROP INDEX `arc_id` ON `z_arc_status`;
DROP INDEX `cat_id` ON `z_arc_status`;
DROP INDEX `user_id` ON `z_arc_status`;
DROP INDEX `agree` ON `z_arc_status`;
DROP INDEX `against` ON `z_arc_status`;
DROP INDEX `neutral` ON `z_arc_status`;
DROP INDEX `pv` ON `z_arc_status`;
DROP INDEX `ip` ON `z_arc_status`;
DROP INDEX `addtime` ON `z_arc_status`;
DROP INDEX `edittime` ON `z_arc_status`;
DROP INDEX `arc_id` ON `z_arc_images`;
DROP INDEX `recordtime` ON `z_statistics_ip`;
DROP INDEX `ipaddress` ON `z_statistics_ip`;
DROP INDEX `recordtime` ON `z_statistics_uv`;

ALTER TABLE `z_admin`DROP PRIMARY KEY;
ALTER TABLE `z_articles`DROP PRIMARY KEY;
ALTER TABLE `z_arc_category`DROP PRIMARY KEY;
ALTER TABLE `z_arc_gather`DROP PRIMARY KEY;

DROP TABLE `z_admin`;
DROP TABLE `z_articles`;
DROP TABLE `z_arc_category`;
DROP TABLE `z_arc_status`;
DROP TABLE `z_arc_images`;
DROP TABLE `z_statistics_ip`;
DROP TABLE `z_statistics_uv`;
DROP TABLE `z_arc_gather`;

CREATE TABLE `z_admin` (
`id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT,
`account` varchar(60) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '账户名',
`password` char(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '密码',
`name` varchar(60) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '姓名',
`sex` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '性别（0保密1男2女）',
`tel` varchar(60) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '电话',
`email` varchar(60) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '邮箱',
`addtime` int(10) UNSIGNED NOT NULL COMMENT '注册时间',
`lasttime` int(10) UNSIGNED NOT NULL COMMENT '最后登录时间',
PRIMARY KEY (`id`) ,
INDEX `account` (`account`),
INDEX `password` (`password`)
)
ENGINE=MyISAM
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci
AUTO_INCREMENT=1
COMMENT='管理员表';

CREATE TABLE `z_articles` (
`id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT,
`title` varchar(150) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '标题',
`brief_title` varchar(60) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '简略标题',
`source` varchar(150) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '来源',
`author` varchar(60) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '作者',
`imgurl` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '封面图',
`abstract` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '内容摘要',
`content` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '内容',
`keywords` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '网页关键词',
`description` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '网页描述',
PRIMARY KEY (`id`) 
)
ENGINE=MyISAM
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci
AUTO_INCREMENT=1
COMMENT='文章表';

CREATE TABLE `z_arc_category` (
`id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT,
`name` varchar(150) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '分类名',
`type` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '类型(0文章、1相册)',
`parent_id` smallint(5) UNSIGNED NOT NULL DEFAULT 0 COMMENT '上级关联',
`keep` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '保留栏目（0否1是）',
`status` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '状态（0软删除、1不显示、2显示）',
`sort` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '排序',
`seotitle` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '网页标题',
`keywords` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '网页关键词',
`description` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '网页描述',
PRIMARY KEY (`id`) ,
INDEX `parent_id` (`parent_id`)
)
ENGINE=MyISAM
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci
AUTO_INCREMENT=1
COMMENT='文章分类表';

CREATE TABLE `z_arc_status` (
`arc_id` mediumint(8) UNSIGNED NOT NULL COMMENT '文章id',
`cat_id` smallint(5) UNSIGNED NOT NULL DEFAULT 0 COMMENT '文章分类id',
`user_id` mediumint(8) UNSIGNED NOT NULL DEFAULT 0 COMMENT '用户id',
`hasimg` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否有封面（0否、1是）',
`status` tinyint(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态（0软删除、1不显示、2显示）',
`comment` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '评论状态（0不可评论、1可评论）',
`is_new` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否最新（0否1是）',
`is_hot` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否热门（0否1是）',
`is_top` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否置顶（0否1是）',
`is_push` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否推荐（0否1是）',
`is_best` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否精华（0否1是）',
`agree` mediumint(8) UNSIGNED NOT NULL DEFAULT 0 COMMENT '赞同数',
`against` mediumint(8) UNSIGNED NOT NULL DEFAULT 0 COMMENT '反对数',
`neutral` mediumint(8) UNSIGNED NOT NULL DEFAULT 0 COMMENT '中立数',
`pv` mediumint(8) UNSIGNED NOT NULL DEFAULT 0 COMMENT '访问量（刷新计数）',
`ip` mediumint(8) UNSIGNED NOT NULL DEFAULT 0 COMMENT '独立ip（永不重复ip）',
`addtime` int(10) UNSIGNED NOT NULL COMMENT '添加时间',
`edittime` int(10) UNSIGNED NOT NULL COMMENT '修改时间',
INDEX `arc_id` (`arc_id`),
INDEX `cat_id` (`cat_id`),
INDEX `user_id` (`user_id`),
INDEX `agree` (`agree`),
INDEX `against` (`against`),
INDEX `neutral` (`neutral`),
INDEX `pv` (`pv`),
INDEX `ip` (`ip`),
INDEX `addtime` (`addtime`),
INDEX `edittime` (`edittime`)
)
ENGINE=MyISAM
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci
AUTO_INCREMENT=1
COMMENT='文章状态表';

CREATE TABLE `z_arc_images` (
`arc_id` mediumint(8) UNSIGNED NOT NULL COMMENT '文章id',
`imgurl` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '图片路径',
`imgdesc` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '图片描述',
`isfirst` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否首图（0否1是）',
INDEX `arc_id` (`arc_id`)
)
ENGINE=MyISAM
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci
AUTO_INCREMENT=1
COMMENT='文章相册表';

CREATE TABLE `z_statistics_ip` (
`recordtime` int(10) UNSIGNED NOT NULL COMMENT '记录时间',
`ipaddress` int(10) UNSIGNED NOT NULL COMMENT 'ip地址',
INDEX `recordtime` (`recordtime`),
INDEX `ipaddress` (`ipaddress`)
)
ENGINE=MyISAM
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci
AUTO_INCREMENT=1
COMMENT='ip统计信息';

CREATE TABLE `z_statistics_uv` (
`recordtime` int(10) UNSIGNED NOT NULL COMMENT '记录时间',
`dailyuv` mediumint(8) UNSIGNED NOT NULL COMMENT '日均uv',
INDEX `recordtime` (`recordtime`)
)
ENGINE=MyISAM
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci
AUTO_INCREMENT=1
COMMENT='uv统计信息';

CREATE TABLE `z_arc_gather` (
`id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT,
`status` tinyint(1) UNSIGNED NOT NULL COMMENT '状态（0未采用、1已采用）',
`reedit` tinyint(1) UNSIGNED NOT NULL COMMENT '是否重新编辑（0否、1是）',
`hasimg` tinyint(1) UNSIGNED NOT NULL COMMENT '是否有封面图（0否、1是）',
`title` varchar(150) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '标题',
`brief_title` varchar(60) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '简略标题',
`source` varchar(150) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '来源',
`author` varchar(60) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '作者',
`imgurl` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '封面图',
`abstract` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '内容摘要',
`content` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '内容',
`keywords` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '网页关键词',
`description` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '网页描述',
PRIMARY KEY (`id`) 
)
ENGINE=MyISAM
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci
AUTO_INCREMENT=1
COMMENT='文章采集表';

