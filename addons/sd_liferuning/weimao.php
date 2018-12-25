<?php

$installSql = <<<sql

DROP TABLE IF EXISTS `135k_activity`;
CREATE TABLE `135k_activity` (
  `activity_id` int(11) NOT NULL AUTO_INCREMENT,
  `activity_title` varchar(255) NOT NULL,
  `activity_adress` varchar(255) NOT NULL,
  `activity_img` varchar(255) NOT NULL,
  `activity_time` int(11) NOT NULL,
  `activity_content` longtext NOT NULL,
  PRIMARY KEY (`activity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `135k_alysend`;
CREATE TABLE `135k_alysend` (
  `sid` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `bid` int(11) NOT NULL COMMENT '商户id',
  `keyid` varchar(255) NOT NULL COMMENT '阿里云商户keyid',
  `keysecret` varchar(255) NOT NULL COMMENT '阿里云商户secret',
  `signname` varchar(255) NOT NULL COMMENT '短信签名    阿里云控制台设置',
  `templatecode` varchar(255) NOT NULL COMMENT '短信模板ID 阿里云控制台设置',
  `time` int(11) NOT NULL DEFAULT '0' COMMENT '添加时间',
  PRIMARY KEY (`sid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `135k_apply`;
CREATE TABLE `135k_apply` (
  `apply_id` int(11) NOT NULL AUTO_INCREMENT,
  `apply_name` varchar(255) NOT NULL,
  `apply_sex` bit(1) NOT NULL,
  `apply_phone` varchar(255) NOT NULL,
  `apply_profession` varchar(255) NOT NULL,
  `apply_standard` text NOT NULL,
  `apply_images` varchar(10000) NOT NULL,
  `apply_time` int(11) NOT NULL,
  `apply_height` varchar(255) NOT NULL,
  `apply_weight` varchar(255) NOT NULL,
  `apply_native` varchar(255) NOT NULL,
  PRIMARY KEY (`apply_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `135k_appointment`;
CREATE TABLE `135k_appointment` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `name` varchar(30) NOT NULL COMMENT '用户名',
  `tel` varchar(20) NOT NULL,
  `uid` int(11) NOT NULL COMMENT '用户表id',
  `project` varchar(100) NOT NULL COMMENT '选择服务项目',
  `ads` varchar(150) NOT NULL COMMENT '选择门店地址',
  `createtime` int(11) NOT NULL COMMENT '创建时间',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态(1：预约，0：取消）',
  `arrivetime` varchar(50) NOT NULL COMMENT '到店时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `135k_backstage`;
CREATE TABLE `135k_backstage` (
  `admid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `tel` varchar(11) DEFAULT NULL,
  `phone` varchar(11) DEFAULT NULL,
  PRIMARY KEY (`admid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



DROP TABLE IF EXISTS `135k_banner`;
CREATE TABLE `135k_banner` (
  `banid` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `bid` int(11) NOT NULL DEFAULT '0' COMMENT '商家id',
  `pic` char(100) NOT NULL DEFAULT '' COMMENT '图片',
  `url` varchar(250) NOT NULL DEFAULT '' COMMENT 'banner链接',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `createtime` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `uint` int(11) NOT NULL,
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '标题',
  `banids` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`banid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='首页banner表';

INSERT INTO `135k_banner` (`banid`, `bid`, `pic`, `url`, `sort`, `createtime`, `uint`, `name`, `banids`) VALUES
(77,	38,	'20180515BggMBx15263862946203.png',	'',	1,	1526386294,	0,	'#',	0),
(78,	39,	'201805155mQhEv15263884490375.jpg',	'',	1,	1526388449,	0,	'#',	0),
(79,	45,	'20180516JcUKQR15264517981782.jpg',	'',	1,	1526451798,	0,	'',	0),
(83,	46,	'20180518QA1nhQ15266121497158.jpg',	'',	1,	1526612149,	0,	'',	0);

SET NAMES utf8mb4;

DROP TABLE IF EXISTS `135k_business`;
CREATE TABLE `135k_business` (
  `bid` int(11) NOT NULL AUTO_INCREMENT COMMENT '商家bid',
  `password` varchar(100) NOT NULL DEFAULT '' COMMENT '密码',
  `name` varchar(200) NOT NULL DEFAULT '' COMMENT '商铺名称',
  `welcome` varchar(500) NOT NULL DEFAULT '' COMMENT '欢迎语',
  `open_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'open id',
  `sell_money` float(11,2) NOT NULL DEFAULT '0.00' COMMENT '销售金额',
  `mobile` varchar(100) NOT NULL DEFAULT '' COMMENT '商铺负责人电话',
  `address` varchar(200) NOT NULL DEFAULT '' COMMENT '商铺位置',
  `logo` varchar(100) NOT NULL DEFAULT '' COMMENT '商家logo',
  `pics` varchar(100) NOT NULL DEFAULT '' COMMENT '图片集',
  `pic` varchar(100) NOT NULL DEFAULT '' COMMENT '主图',
  `stid` int(11) NOT NULL DEFAULT '0' COMMENT '商家分类id  对应type主键id',
  `open` varchar(100) NOT NULL DEFAULT '' COMMENT '营业时间',
  `qq` varchar(50) NOT NULL DEFAULT '' COMMENT 'qq',
  `phone` varchar(20) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '客服电话',
  `email` varchar(20) NOT NULL DEFAULT '' COMMENT '邮箱',
  `longitude` double(10,6) NOT NULL DEFAULT '0.000000' COMMENT '经度',
  `latitude` double(10,6) NOT NULL DEFAULT '0.000000' COMMENT '纬度',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1：营业中 2：休息中',
  `status1` tinyint(1) DEFAULT '1' COMMENT '1:正常 -1删除',
  `pay_way` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0：微信支付   1：货到付款    2：都支持',
  `subscribe_money` float(2,0) NOT NULL DEFAULT '0' COMMENT '预约 预支付金额',
  `subscribe_num` text CHARACTER SET utf8 NOT NULL COMMENT '预约数量  （序列化数据存储）',
  `sales` int(11) NOT NULL DEFAULT '0' COMMENT '销量',
  `province` varchar(6) NOT NULL DEFAULT '' COMMENT '省',
  `city` varchar(6) NOT NULL DEFAULT '' COMMENT '市',
  `area` varchar(6) NOT NULL DEFAULT '' COMMENT '区',
  `iid` varchar(200) NOT NULL DEFAULT '' COMMENT '服务项目 id(''1'',''2'',''3'')',
  `solt` char(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '密码随机为',
  `regtime` int(11) NOT NULL DEFAULT '0' COMMENT '注册时间',
  `updatetime` int(11) NOT NULL DEFAULT '0' COMMENT '修改时间',
  `content` text NOT NULL COMMENT '商家介绍',
  `login_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0:未登录 1:登录',
  `member` int(11) NOT NULL DEFAULT '0' COMMENT '会员所需积分',
  `pick_is` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1： 是  0：否  送货上门',
  `province_money` float(11,2) NOT NULL DEFAULT '0.00' COMMENT '配送费/KG  （同省）',
  `outer_money` float(11,2) NOT NULL DEFAULT '0.00' COMMENT '配送费/KG  （外省）',
  `help` text CHARACTER SET utf8 NOT NULL COMMENT '使用帮助',
  `about_us` text NOT NULL COMMENT '关于我们',
  `aftermarket` varchar(100) NOT NULL DEFAULT '' COMMENT '售后保障',
  `appid` varchar(60) CHARACTER SET utf8 NOT NULL,
  `secret` varchar(100) NOT NULL,
  `site_ip` varchar(100) NOT NULL DEFAULT '' COMMENT 'ip地址',
  `key` varchar(100) DEFAULT NULL,
  `mchid` varchar(100) CHARACTER SET utf8 DEFAULT '' COMMENT '商户号',
  `account` varchar(50) NOT NULL DEFAULT '' COMMENT '账号',
  `up` int(1) DEFAULT '1' COMMENT '1: 开店    0： 关店',
  `uniacid` int(11) NOT NULL DEFAULT '0' COMMENT '小程序id',
  `apiclient_key` text,
  `apiclient_cert` text,
  `front_color` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `background_color` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '#ffffff',
  `area_limit` int(1) unsigned NOT NULL COMMENT '区域限制，1开启，0关闭(人力资源工作状态改变)',
  `auto_pay` int(2) NOT NULL DEFAULT '0' COMMENT '自动到账',
  `open_pay` int(2) NOT NULL DEFAULT '0' COMMENT '企业付款',
  `num` int(11) NOT NULL DEFAULT '0' COMMENT '限制',
  `distype` tinyint(11) NOT NULL DEFAULT '0' COMMENT '限制2',
  PRIMARY KEY (`bid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='商家信息';

DROP TABLE IF EXISTS `135k_business_info`;
CREATE TABLE `135k_business_info` (
  `bid` int(11) NOT NULL COMMENT '商家 主键 id',
  `iid` tinyint(1) NOT NULL COMMENT '服务id',
  `price_type` text CHARACTER SET utf8 NOT NULL COMMENT '服务项目收费内容 ',
  `content` text CHARACTER SET utf8 NOT NULL COMMENT '服务内容',
  `creattime` int(11) NOT NULL DEFAULT '0' COMMENT '添加时间'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='商家 附加信息表';


DROP TABLE IF EXISTS `135k_business_pay`;
CREATE TABLE `135k_business_pay` (
  `out_id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `cid` int(11) DEFAULT '0',
  `order_no` varchar(255) NOT NULL,
  `out_time` int(11) NOT NULL,
  `type` int(1) NOT NULL DEFAULT '1' COMMENT '1是用户2是配送员',
  `bid` int(11) NOT NULL,
  `status` int(11) NOT NULL DEFAULT '0' COMMENT '0未支付1支付',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `update_time` int(11) NOT NULL,
  PRIMARY KEY (`out_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `135k_card`;
CREATE TABLE `135k_card` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bid` int(11) NOT NULL,
  `uname` varchar(255) NOT NULL,
  `pic` text NOT NULL,
  `status` int(2) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `135k_card_form`;
CREATE TABLE `135k_card_form` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cardnumber` varchar(255) NOT NULL,
  `uid` int(255) NOT NULL,
  `name` varchar(30) NOT NULL,
  `cid` int(11) NOT NULL,
  `click` int(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `135k_cart`;
CREATE TABLE `135k_cart` (
  `cartid` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `uid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `bid` int(11) NOT NULL COMMENT '商家id',
  `goodsid` char(100) NOT NULL DEFAULT '' COMMENT '商品id',
  `price` float(5,2) DEFAULT '0.00' COMMENT '商品价格',
  `rule` varchar(50) DEFAULT '' COMMENT '类型（颜色,其他）',
  `rule1` varchar(50) DEFAULT '' COMMENT '尺寸',
  `rule2` varchar(50) DEFAULT '' COMMENT '规格3',
  `num` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '数量',
  `pic` text,
  `updatetime` int(11) NOT NULL DEFAULT '0' COMMENT '修改时间',
  `createtime` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`cartid`),
  KEY `uid` (`uid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='购物车表';


DROP TABLE IF EXISTS `135k_cc_user`;
CREATE TABLE `135k_cc_user` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `openid` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `addtime` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `135k_class`;
CREATE TABLE `135k_class` (
  `cid` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `bid` int(11) NOT NULL COMMENT '商户id',
  `name` varchar(100) NOT NULL COMMENT '服务分类名称',
  `status` int(1) NOT NULL DEFAULT '0' COMMENT '分类状态 1 正常   0为关闭',
  `moban` int(5) NOT NULL COMMENT '|模板id ',
  `paixu` int(11) DEFAULT NULL COMMENT '|分类排序',
  PRIMARY KEY (`cid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `135k_clause`;
CREATE TABLE `135k_clause` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bid` int(11) NOT NULL,
  `content` text,
  `updatetime` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  `title` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `135k_collect`;
CREATE TABLE `135k_collect` (
  `colid` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0' COMMENT '用户id',
  `goodsid` int(11) NOT NULL DEFAULT '0' COMMENT '商品id',
  `createtime` int(11) NOT NULL DEFAULT '0' COMMENT '时间',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '状态（0:正常,1:取消）',
  `bid` int(11) unsigned NOT NULL COMMENT 'bid',
  PRIMARY KEY (`colid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='收藏表';


DROP TABLE IF EXISTS `135k_comment`;
CREATE TABLE `135k_comment` (
  `coid` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0' COMMENT '用户id',
  `bid` int(11) NOT NULL DEFAULT '0' COMMENT '商家id',
  `goodsid` int(11) NOT NULL DEFAULT '0' COMMENT '商品id',
  `ogid` int(11) NOT NULL COMMENT '订单id',
  `content` varchar(500) NOT NULL DEFAULT '' COMMENT '内容',
  `sum_grade` tinyint(1) NOT NULL DEFAULT '5' COMMENT '总体评价',
  `express_grade` tinyint(1) NOT NULL DEFAULT '5' COMMENT '物流评价',
  `goods_grade` tinyint(1) NOT NULL DEFAULT '5' COMMENT '商品评价',
  `serve_grade` tinyint(1) NOT NULL DEFAULT '5' COMMENT '服务态度评价',
  `pics` varchar(500) NOT NULL DEFAULT '' COMMENT '图片集',
  `isshow` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0：显示  1：不显示',
  `reply` varchar(500) NOT NULL DEFAULT '' COMMENT '回复评论',
  `reply_pics` varchar(500) NOT NULL DEFAULT '' COMMENT '回复评论图片集',
  `createtime` int(11) NOT NULL DEFAULT '0' COMMENT '评论时间',
  `replytime` int(11) NOT NULL DEFAULT '0' COMMENT '回复时间',
  PRIMARY KEY (`coid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='评论表';


DROP TABLE IF EXISTS `135k_comm_type`;
CREATE TABLE `135k_comm_type` (
  `ptid` int(11) NOT NULL AUTO_INCREMENT,
  `bid` int(11) NOT NULL DEFAULT '0' COMMENT '商家id',
  `name` varchar(200) NOT NULL DEFAULT '' COMMENT '分类名称',
  `level` tinyint(1) NOT NULL DEFAULT '0' COMMENT '级别',
  `pid` int(11) NOT NULL DEFAULT '0' COMMENT '父级 id',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0：停用 1：正常',
  `pic` varchar(50) NOT NULL DEFAULT '' COMMENT '图片',
  `nid` varchar(100) NOT NULL DEFAULT '' COMMENT '拼音',
  `mid` int(11) NOT NULL DEFAULT '0' COMMENT '顶级父级 id',
  `solt` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `tid` int(11) NOT NULL DEFAULT '0' COMMENT '分类id（对应menus表id）',
  `createtime` int(11) NOT NULL DEFAULT '0' COMMENT '添加时间',
  `updatetime` int(11) NOT NULL DEFAULT '0' COMMENT '修改时间',
  PRIMARY KEY (`ptid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商品分类表';


DROP TABLE IF EXISTS `135k_coupon`;
CREATE TABLE `135k_coupon` (
  `useid` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0' COMMENT '用户id',
  `disid` int(11) NOT NULL DEFAULT '0' COMMENT '发放表id',
  `ctitle` varchar(100) NOT NULL DEFAULT '' COMMENT '优惠券',
  `money` int(11) NOT NULL COMMENT '优惠券金额',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0：未使用   1：已使用',
  `createtime` int(11) NOT NULL DEFAULT '0' COMMENT '领取时间',
  `pasttime` int(11) NOT NULL DEFAULT '0' COMMENT '过期时间',
  PRIMARY KEY (`useid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='优惠券使用记录表';


DROP TABLE IF EXISTS `135k_coupon_add`;
CREATE TABLE `135k_coupon_add` (
  `disid` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '优惠券名称',
  `number` int(5) NOT NULL DEFAULT '99999' COMMENT '数量',
  `money` float(11,2) NOT NULL DEFAULT '0.00' COMMENT '面值金额',
  `full_money` float(11,2) NOT NULL DEFAULT '0.00' COMMENT '满多少使用',
  `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '优惠券类型（1普通，2分类，3单商品，4注册，5转发，6活动）',
  `goodsid` int(11) NOT NULL DEFAULT '0' COMMENT '商品id',
  `bid` int(11) NOT NULL DEFAULT '0' COMMENT '商家id',
  `commid` int(11) NOT NULL DEFAULT '0' COMMENT '对应商品分类id',
  `starttime` int(11) NOT NULL DEFAULT '0' COMMENT '优惠券开始时间',
  `endtime` int(11) NOT NULL DEFAULT '0' COMMENT '优惠券结束时间',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0：开启   1：关闭',
  `delete` int(11) NOT NULL DEFAULT '0',
  `sort` int(11) unsigned NOT NULL COMMENT '排序',
  `timelong` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '优惠券有效天数',
  `coupontype` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0：时间段使用，1：领取几天内使用',
  PRIMARY KEY (`disid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='优惠券发放表';


DROP TABLE IF EXISTS `135k_cust_user`;
CREATE TABLE `135k_cust_user` (
  `cid` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `pwd` varchar(40) NOT NULL DEFAULT '',
  `money` decimal(11,2) NOT NULL DEFAULT '0.00' COMMENT '0.00',
  `uname` varchar(30) DEFAULT NULL,
  `card` varchar(30) DEFAULT NULL,
  `tel` varchar(11) NOT NULL DEFAULT '',
  `content` varchar(60) DEFAULT NULL,
  `createtime` int(11) NOT NULL DEFAULT '0',
  `cashstatus` smallint(2) NOT NULL DEFAULT '0',
  `status` smallint(2) NOT NULL DEFAULT '-1' COMMENT '1需要审核2审核中3审核成功-1封禁',
  `updatetime` int(11) DEFAULT NULL,
  `cardimg` varchar(255) DEFAULT NULL COMMENT '身份证正面照片',
  `cardimgf` varchar(255) DEFAULT NULL COMMENT '身份证反面照片',
  `promisemoney` decimal(11,2) NOT NULL DEFAULT '0.00' COMMENT '保证金',
  `bank` varchar(30) DEFAULT NULL COMMENT '开户行',
  `bankname` varchar(30) DEFAULT NULL COMMENT '开户行姓名',
  `bankaccount` varchar(30) DEFAULT NULL COMMENT '银行卡号',
  `manejilu` float(11,2) NOT NULL DEFAULT '0.00' COMMENT '满额记录',
  `mandanjilu` int(11) NOT NULL DEFAULT '0' COMMENT '满单记录',
  `inside` int(11) NOT NULL DEFAULT '0' COMMENT '内部人员1非0',
  `open_id` varchar(255) DEFAULT NULL,
  `play` int(11) DEFAULT NULL,
  `longitude` varchar(30) DEFAULT NULL COMMENT '经度',
  `latitude` varchar(30) DEFAULT NULL COMMENT '纬度',
  `license` varchar(255) DEFAULT '' COMMENT '驾驶证',
  `is_status` int(1) DEFAULT '1' COMMENT '1 跑腿  2家政  3代驾',
  `carcardimg` varchar(255) DEFAULT NULL COMMENT '健康证  驾驶证正面',
  `carcodes` varchar(255) DEFAULT NULL COMMENT '车架号',
  PRIMARY KEY (`cid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `135k_directory`;
CREATE TABLE `135k_directory` (
  `directory_id` int(11) NOT NULL AUTO_INCREMENT,
  `directory_name` varchar(255) NOT NULL,
  `directory_phone` varchar(255) NOT NULL,
  `directory_address` varchar(255) NOT NULL,
  `directory_content` longtext NOT NULL,
  `directory_img` varchar(255) NOT NULL,
  PRIMARY KEY (`directory_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `135k_diy`;
CREATE TABLE `135k_diy` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `bid` int(11) unsigned NOT NULL,
  `diy` text NOT NULL,
  `update_time` varchar(25) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



DROP TABLE IF EXISTS `135k_diy_img`;
CREATE TABLE `135k_diy_img` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `bid` int(11) unsigned DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `135k_diy_order`;
CREATE TABLE `135k_diy_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `diy` text NOT NULL,
  `update_time` varchar(255) NOT NULL DEFAULT '0',
  `bid` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `135k_duijie`;
CREATE TABLE `135k_duijie` (
  `ab_id` int(11) NOT NULL AUTO_INCREMENT,
  `is_data_base` int(1) DEFAULT '0' COMMENT '是否是同一数据库',
  `data_base_ip` varchar(100) DEFAULT NULL COMMENT '数据库IP地址',
  `port_number` varchar(50) DEFAULT NULL COMMENT '端口号',
  `data_base_username` varchar(50) DEFAULT NULL COMMENT '数据库用户名',
  `data_base_name` varchar(50) DEFAULT NULL COMMENT '数据库名',
  `password` varchar(255) DEFAULT NULL COMMENT '数据库密码',
  `code` varchar(50) DEFAULT NULL COMMENT '数据库编码',
  PRIMARY KEY (`ab_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `135k_express`;
CREATE TABLE `135k_express` (
  `exid` int(11) NOT NULL AUTO_INCREMENT,
  `bid` int(11) NOT NULL DEFAULT '0',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '快递',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0：删除   1：正常',
  `createtime` int(11) NOT NULL DEFAULT '0' COMMENT '添加时间',
  PRIMARY KEY (`exid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='快递表';


DROP TABLE IF EXISTS `135k_freight`;
CREATE TABLE `135k_freight` (
  `freid` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `bid` int(11) NOT NULL DEFAULT '0' COMMENT '商家id',
  `title` varchar(50) NOT NULL DEFAULT '' COMMENT '标题',
  `city` varchar(3000) NOT NULL COMMENT '地址',
  `unit` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0：kg  1：个',
  `first` int(11) NOT NULL DEFAULT '0' COMMENT '首重',
  `freight` float(11,2) NOT NULL DEFAULT '0.00' COMMENT '首重运费',
  `next` int(11) NOT NULL DEFAULT '0' COMMENT '次重',
  `freight1` float(11,2) NOT NULL DEFAULT '0.00' COMMENT '次重运费费',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1：正常  0：删除',
  `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0：通用型  1：普通型',
  `createtime` int(11) NOT NULL DEFAULT '0' COMMENT '添加时间',
  `handletime` int(11) NOT NULL DEFAULT '0' COMMENT '修改时间',
  PRIMARY KEY (`freid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商家运费模板';


DROP TABLE IF EXISTS `135k_goods`;
CREATE TABLE `135k_goods` (
  `goodsid` int(11) NOT NULL AUTO_INCREMENT COMMENT '商品id',
  `bid` int(11) NOT NULL DEFAULT '0' COMMENT '对应商家bid',
  `pic` char(50) NOT NULL DEFAULT '' COMMENT '商品链表页图片',
  `name` char(200) NOT NULL DEFAULT '' COMMENT '商品名称',
  `unit` char(50) NOT NULL DEFAULT '' COMMENT '单位',
  `max_price` float(11,2) NOT NULL DEFAULT '0.00' COMMENT '某宝价/最高价',
  `min_price` float(11,2) NOT NULL DEFAULT '0.00' COMMENT '售价/最低价',
  `readnum` int(11) NOT NULL DEFAULT '0' COMMENT '阅读数',
  `favournum` int(11) NOT NULL DEFAULT '0' COMMENT '收藏数',
  `stock` int(11) NOT NULL DEFAULT '0' COMMENT '总库存',
  `sales` int(11) NOT NULL DEFAULT '0' COMMENT '销量',
  `recom` tinyint(1) NOT NULL DEFAULT '2' COMMENT '0：正常   1：首页推荐  2：推荐  ',
  `special_offer` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0：正常   1：特价商品',
  `explain` varchar(2000) NOT NULL DEFAULT '' COMMENT '产品说明',
  `content` text NOT NULL COMMENT '产品介绍',
  `shelves_time` int(11) NOT NULL DEFAULT '0' COMMENT '上架时间',
  `updatetime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '修改时间',
  `createtime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `integral` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0：商品  1：积分商品',
  `need_integral` int(11) NOT NULL DEFAULT '0' COMMENT '所需积分',
  `gain_integral` int(11) NOT NULL DEFAULT '0' COMMENT '商品积分',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态（1：开启 0：关闭）',
  `discount` float(5,2) NOT NULL DEFAULT '1.00' COMMENT '会员折扣',
  `pics` text NOT NULL COMMENT '详情页滚动图片',
  `type` int(11) NOT NULL DEFAULT '0' COMMENT '对应商品分类表  comm_type',
  `menu` int(11) NOT NULL DEFAULT '0' COMMENT '对应菜单',
  `province` varchar(6) NOT NULL DEFAULT '' COMMENT '省份',
  `city` varchar(6) NOT NULL DEFAULT '' COMMENT '城市',
  `area` varchar(6) NOT NULL DEFAULT '' COMMENT '市区',
  `weight` float(6,1) NOT NULL DEFAULT '0.0' COMMENT '商品重量',
  `seemingly` char(20) NOT NULL DEFAULT '' COMMENT '同类商品',
  `template` int(11) NOT NULL DEFAULT '0' COMMENT '运费模板',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `freight_count` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0：按重量   1：按件',
  `freight_unify` float(11,0) NOT NULL DEFAULT '0' COMMENT '统一邮费',
  `open_rule` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0：关闭    1：开启（商品规格）',
  `yieldtime` varchar(100) NOT NULL DEFAULT '' COMMENT '生产日期',
  `delete` int(11) DEFAULT '0',
  `goodsname` varchar(100) DEFAULT NULL,
  `time_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '限时（0：不限时，1：限时）',
  `start_time` varchar(30) DEFAULT NULL COMMENT '开始时间',
  `end_time` varchar(30) DEFAULT NULL COMMENT '结束时间',
  PRIMARY KEY (`goodsid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商品表';


DROP TABLE IF EXISTS `135k_goods_attr`;
CREATE TABLE `135k_goods_attr` (
  `attrid` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `goodsid` int(11) NOT NULL COMMENT '商品id',
  `rule` varchar(50) NOT NULL COMMENT '类型（颜色,其他）',
  `rule1` varchar(50) NOT NULL DEFAULT '' COMMENT '尺码，大小等',
  `rule2` varchar(50) NOT NULL DEFAULT '' COMMENT '规格3',
  `price` decimal(8,2) NOT NULL DEFAULT '0.00' COMMENT '售价',
  `stock` mediumint(8) NOT NULL DEFAULT '0' COMMENT '库存',
  `pic` char(50) NOT NULL DEFAULT '' COMMENT '图片',
  `sales` mediumint(8) NOT NULL DEFAULT '0' COMMENT '总销量',
  PRIMARY KEY (`attrid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商品尺码颜色库存表';


DROP TABLE IF EXISTS `135k_goods_order`;
CREATE TABLE `135k_goods_order` (
  `orderid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL COMMENT '用户id',
  `order_no` char(100) NOT NULL DEFAULT '' COMMENT '订单编号',
  `bid` char(50) NOT NULL DEFAULT '' COMMENT '商家id',
  `num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '商品数量',
  `pay_way` char(50) NOT NULL DEFAULT '' COMMENT '支付方式',
  `money` float(11,2) NOT NULL DEFAULT '0.00' COMMENT '支付金额',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态（1：待付款 2：代发货  3：待收货 4：已完成  -1：关闭）',
  `name` varchar(20) NOT NULL DEFAULT '' COMMENT '姓名',
  `phone` varchar(20) NOT NULL DEFAULT '' COMMENT '手机号',
  `address` varchar(200) NOT NULL DEFAULT '' COMMENT '地址',
  `pay_user` char(100) NOT NULL DEFAULT '' COMMENT '支付用户',
  `pay_serial_number` char(100) NOT NULL DEFAULT '' COMMENT '交易流水号',
  `createtime` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `paytime` int(11) NOT NULL DEFAULT '0' COMMENT '支付时间',
  `sendtime` int(11) NOT NULL DEFAULT '0' COMMENT '发货时间',
  `taketime` int(11) NOT NULL DEFAULT '0' COMMENT '收货时间',
  `wxpayparms` text NOT NULL COMMENT '微信支付参数',
  `distribution_pay` tinyint(1) NOT NULL DEFAULT '1' COMMENT '配送方式(0：快递 1：自提)',
  `take_bid` mediumint(8) NOT NULL DEFAULT '0' COMMENT '自提商家id',
  `remark` varchar(200) NOT NULL DEFAULT '' COMMENT '用户备注',
  `take_time` int(11) NOT NULL DEFAULT '0' COMMENT '取消时间',
  `express_number` varchar(50) NOT NULL DEFAULT '' COMMENT '运单号',
  `exid` int(11) NOT NULL DEFAULT '0' COMMENT '快递表id',
  `send` int(1) NOT NULL DEFAULT '0' COMMENT '是否响铃 1：是 0： 否',
  PRIMARY KEY (`orderid`),
  UNIQUE KEY `orderNo` (`order_no`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商品订单表';


DROP TABLE IF EXISTS `135k_history`;
CREATE TABLE `135k_history` (
  `ghid` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0' COMMENT '用户id',
  `id` int(11) NOT NULL DEFAULT '0' COMMENT '商品id',
  `createtime` int(11) NOT NULL DEFAULT '0' COMMENT '添加时间',
  PRIMARY KEY (`ghid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='我的足迹表';


DROP TABLE IF EXISTS `135k_hour`;
CREATE TABLE `135k_hour` (
  `hid` int(11) NOT NULL AUTO_INCREMENT,
  `hour` varchar(255) NOT NULL COMMENT '时间  小时',
  `bid` int(11) NOT NULL,
  `wid` int(11) NOT NULL COMMENT '星期 id',
  `hprice` float(10,2) DEFAULT '0.00' COMMENT '小时金额',
  PRIMARY KEY (`hid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `135k_humans_apply`;
CREATE TABLE `135k_humans_apply` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `job` int(11) unsigned NOT NULL COMMENT 'job id',
  `user` int(11) unsigned NOT NULL COMMENT 'user id',
  `business` int(11) unsigned NOT NULL COMMENT 'bussiness id',
  `state` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '1，正常；2，待定；3，删除',
  `time` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `135k_humans_business`;
CREATE TABLE `135k_humans_business` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` char(40) COLLATE utf8_unicode_ci NOT NULL COMMENT '用户名',
  `account` char(50) COLLATE utf8_unicode_ci NOT NULL COMMENT '商家名称',
  `phone` char(12) COLLATE utf8_unicode_ci NOT NULL COMMENT '电话',
  `bid` int(5) unsigned NOT NULL DEFAULT '47',
  `time` int(11) unsigned NOT NULL COMMENT '创建时间',
  `status1` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '1使用，0冻结',
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=COMPACT;


DROP TABLE IF EXISTS `135k_humans_job_cat`;
CREATE TABLE `135k_humans_job_cat` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `cat` varchar(20) COLLATE utf8_unicode_ci NOT NULL COMMENT '分类名',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=COMPACT;


DROP TABLE IF EXISTS `135k_humans_pjob`;
CREATE TABLE `135k_humans_pjob` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` char(25) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '姓名',
  `tel` char(12) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '电话',
  `type` char(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '区分',
  `time` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `135k_humans_text`;
CREATE TABLE `135k_humans_text` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` char(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `text` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '内容',
  `sort` int(11) unsigned NOT NULL COMMENT '排序',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `135k_hzp_news`;
CREATE TABLE `135k_hzp_news` (
  `neid` int(11) NOT NULL AUTO_INCREMENT,
  `bid` int(11) NOT NULL DEFAULT '0' COMMENT '商家id',
  `title` varchar(100) NOT NULL DEFAULT '' COMMENT '标题',
  `content` text NOT NULL COMMENT '内容',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0：正常   -1：删除',
  `createtime` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updatetime` int(11) NOT NULL DEFAULT '0' COMMENT '修改时间',
  `pic` varchar(100) NOT NULL COMMENT '图片',
  PRIMARY KEY (`neid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='化妆品新闻';


DROP TABLE IF EXISTS `135k_id`;
CREATE TABLE `135k_id` (
  `id` int(4) NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `u_id` int(4) NOT NULL COMMENT '上级id',
  `m_id` int(4) NOT NULL COMMENT '自己的id',
  `s_id` int(4) NOT NULL COMMENT '下级id',
  `createtime` tinyint(4) NOT NULL COMMENT '时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `135k_information`;
CREATE TABLE `135k_information` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET latin1 NOT NULL COMMENT '名字',
  `kftell` varchar(15) CHARACTER SET latin1 NOT NULL COMMENT '客服电话',
  `kaytime` int(11) NOT NULL COMMENT '开业时间',
  `zxtime` int(11) NOT NULL COMMENT '装修时间',
  `yitime` int(11) NOT NULL COMMENT '营业时间',
  `tftime` int(11) NOT NULL COMMENT '退房时间',
  `kf` int(11) NOT NULL COMMENT '客房数',
  `payment` int(11) NOT NULL COMMENT '设施',
  `yanj` decimal(10,2) NOT NULL COMMENT '押金',
  `weiz` varchar(100) CHARACTER SET latin1 NOT NULL COMMENT '位置',
  `louc` int(11) NOT NULL COMMENT '楼层',
  `jies` varchar(255) CHARACTER SET latin1 NOT NULL COMMENT '介绍',
  `miaos` text CHARACTER SET latin1 NOT NULL COMMENT '描述',
  `dit` varchar(255) CHARACTER SET latin1 NOT NULL COMMENT '地铁',
  `gongj` varchar(255) CHARACTER SET latin1 NOT NULL COMMENT '公交',
  `yiaj` int(11) NOT NULL COMMENT '押金',
  `time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `135k_integral`;
CREATE TABLE `135k_integral` (
  `uid` int(11) NOT NULL DEFAULT '0' COMMENT '用户id',
  `bid` int(11) NOT NULL DEFAULT '0' COMMENT '商家id',
  `integral` int(11) NOT NULL DEFAULT '0' COMMENT '积分',
  `use_integral` int(11) NOT NULL DEFAULT '0' COMMENT '可使用积分',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0：正常   1：冻结',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0：正常   1：会员'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='会员积分表';


DROP TABLE IF EXISTS `135k_items`;
CREATE TABLE `135k_items` (
  `iid` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `bid` int(11) NOT NULL DEFAULT '0' COMMENT '商家id',
  `title` varchar(50) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '标签名称',
  `pic` varchar(50) NOT NULL DEFAULT '' COMMENT '图片',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `createtime` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updatetime` int(11) NOT NULL DEFAULT '0' COMMENT '修改时间',
  PRIMARY KEY (`iid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='商家服务项目表';


DROP TABLE IF EXISTS `135k_joint`;
CREATE TABLE `135k_joint` (
  `joint_id` int(11) NOT NULL AUTO_INCREMENT,
  `j_name` varchar(255) NOT NULL COMMENT '对接模块的名称',
  `photo` varchar(255) NOT NULL COMMENT '对接链接',
  `status` int(1) NOT NULL DEFAULT '0' COMMENT '状态 0 没有开启，1为开启',
  PRIMARY KEY (`joint_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



DROP TABLE IF EXISTS `135k_joint_order`;
CREATE TABLE `135k_joint_order` (
  `jid` int(11) NOT NULL AUTO_INCREMENT,
  `bid` int(11) NOT NULL COMMENT '跑腿小程序id',
  `hjmall_id` int(11) NOT NULL COMMENT '提交申请的小程序id',
  `uid` int(11) NOT NULL COMMENT '跑腿上用户id',
  `status` int(1) NOT NULL DEFAULT '-1' COMMENT '是否通过申请 0是拒绝   1是通过 -1是未审核',
  `shenhe_time` int(11) NOT NULL DEFAULT '0' COMMENT '审核的时间',
  `name` varchar(255) NOT NULL COMMENT '小程序名称',
  `appid` varchar(255) NOT NULL COMMENT '小程序的appid',
  `is_zguanli` int(1) NOT NULL DEFAULT '1' COMMENT '是否为为平台总管理员 0 no 1 yes',
  `shop_name` varchar(255) DEFAULT NULL COMMENT '入住禾匠时候的店铺名称',
  `type` int(11) NOT NULL DEFAULT '1' COMMENT '1为禾匠，2智慧外卖',
  PRIMARY KEY (`jid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `135k_keywords`;
CREATE TABLE `135k_keywords` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `name` varchar(50) NOT NULL COMMENT '关键词',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态（0：正常，1：关闭）',
  `createtime` int(11) unsigned NOT NULL COMMENT '时间',
  `bid` int(11) unsigned NOT NULL COMMENT '公司id',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='关键词';


DROP TABLE IF EXISTS `135k_member`;
CREATE TABLE `135k_member` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `bid` int(11) NOT NULL DEFAULT '0' COMMENT '商户id',
  `member_grade` int(10) NOT NULL DEFAULT '0' COMMENT '会员等级',
  `grade_name` varchar(30) NOT NULL COMMENT '等级名称',
  `shegnji_where` float(11,2) NOT NULL COMMENT '升级条件',
  `zhekou` float(11,2) NOT NULL COMMENT '折扣',
  `pic` varchar(255) NOT NULL COMMENT '会员图片',
  `stute` int(10) NOT NULL DEFAULT '1' COMMENT '状态(1开启,0关闭)',
  `quanyi` varchar(255) NOT NULL COMMENT '会员权益提示',
  `tishi` varchar(255) NOT NULL COMMENT '会员购买提示',
  `createtime` int(11) NOT NULL COMMENT '会员申请时间',
  `edittime` int(11) NOT NULL COMMENT '修改时间',
  `growth` int(10) DEFAULT NULL COMMENT '所需成长值',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `135k_membermoney`;
CREATE TABLE `135k_membermoney` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `bid` int(10) NOT NULL COMMENT '商户id',
  `hymoney` float(11,2) NOT NULL COMMENT '会员所需金额',
  `xsmoney` float(11,2) NOT NULL COMMENT '悬赏金额',
  `comment` varchar(255) NOT NULL COMMENT '备注',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `135k_menus`;
CREATE TABLE `135k_menus` (
  `tid` int(11) NOT NULL AUTO_INCREMENT,
  `bid` int(11) NOT NULL DEFAULT '0' COMMENT '商家id',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '类型名称',
  `logo` varchar(100) NOT NULL DEFAULT '' COMMENT '类型logo',
  `solt` tinyint(1) NOT NULL DEFAULT '0' COMMENT '排序',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1：正常   0：删除',
  `createtime` int(11) NOT NULL DEFAULT '0' COMMENT '添加时间',
  `updatetime` int(11) NOT NULL DEFAULT '0' COMMENT '修改时间',
  `url` varchar(255) DEFAULT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `active_icon` varchar(255) DEFAULT NULL,
  `text` varchar(255) DEFAULT NULL,
  `color` varchar(255) DEFAULT NULL,
  `active_color` varchar(255) DEFAULT NULL,
  `open_type` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`tid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商家分类菜单';


DROP TABLE IF EXISTS `135k_mess`;
CREATE TABLE `135k_mess` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `apply` varchar(60) DEFAULT NULL COMMENT '支付消息',
  `cancel` varchar(60) DEFAULT NULL COMMENT '取消订单',
  `order` varchar(60) DEFAULT NULL COMMENT '接单消息',
  `bid` int(11) DEFAULT NULL,
  `token` varchar(255) DEFAULT NULL,
  `tokentime` int(12) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `135k_message`;
CREATE TABLE `135k_message` (
  `msid` int(11) NOT NULL AUTO_INCREMENT COMMENT '消息id',
  `phone` char(13) COLLATE utf8_unicode_ci NOT NULL COMMENT '电话',
  `uid` int(11) NOT NULL DEFAULT '0' COMMENT '用户id',
  `title` char(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '标题',
  `content` text COLLATE utf8_unicode_ci NOT NULL COMMENT '内容',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '消息类型（0：系统消息 1：用户消息 ）',
  `status` char(1) CHARACTER SET utf8 NOT NULL DEFAULT '0' COMMENT '0：未读 1：已读 2：删除 ',
  `createtime` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `bid` int(11) unsigned NOT NULL COMMENT 'bid',
  PRIMARY KEY (`msid`),
  KEY `list` (`uid`,`status`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=COMPACT COMMENT='消息表';


DROP TABLE IF EXISTS `135k_message_log`;
CREATE TABLE `135k_message_log` (
  `msid` int(11) NOT NULL DEFAULT '0' COMMENT '消息表 主键id',
  `uid` int(11) NOT NULL DEFAULT '0' COMMENT '操作用户uid',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1: 已读  2: 删除',
  `createtime` int(11) NOT NULL DEFAULT '0' COMMENT '添加时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='标记 删除 已读  消息日志';


DROP TABLE IF EXISTS `135k_notice`;
CREATE TABLE `135k_notice` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(30) NOT NULL,
  `content` text NOT NULL,
  `times` int(11) NOT NULL,
  `bid` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `135k_online`;
CREATE TABLE `135k_online` (
  `onid` int(11) NOT NULL AUTO_INCREMENT COMMENT '网上报名id',
  `name` varchar(50) DEFAULT '' COMMENT '姓名',
  `phone` varchar(255) DEFAULT NULL COMMENT '电话',
  `age` int(11) DEFAULT NULL COMMENT '年龄',
  `lover` varchar(255) DEFAULT NULL COMMENT '爱好',
  `stage` int(11) DEFAULT NULL COMMENT '阶段',
  `address` varchar(255) DEFAULT '' COMMENT '住址',
  `createtime` int(10) DEFAULT NULL COMMENT '报名时间',
  PRIMARY KEY (`onid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='网上报名';


DROP TABLE IF EXISTS `135k_option`;
CREATE TABLE `135k_option` (
  `id` int(11) NOT NULL,
  `name` varchar(60) NOT NULL,
  `value` text NOT NULL,
  `bid` int(11) NOT NULL,
  `time` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `135k_order_goods`;
CREATE TABLE `135k_order_goods` (
  `ogid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `orderid` int(11) unsigned NOT NULL DEFAULT '0',
  `uid` int(11) NOT NULL DEFAULT '0' COMMENT '用户id',
  `goodsid` char(100) NOT NULL DEFAULT '' COMMENT '商品id',
  `num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '数量',
  `rule` varchar(50) NOT NULL DEFAULT '' COMMENT '类型（颜色,其他）',
  `rule1` varchar(50) NOT NULL DEFAULT '' COMMENT '尺码',
  `rule2` varchar(50) NOT NULL DEFAULT '' COMMENT '规格3',
  `attrid` int(11) NOT NULL DEFAULT '0' COMMENT '规则表 主键id',
  `price` decimal(8,2) NOT NULL DEFAULT '0.00' COMMENT '单价',
  `price1` decimal(8,2) NOT NULL DEFAULT '0.00' COMMENT '折扣',
  `total_price` decimal(8,2) NOT NULL DEFAULT '0.00' COMMENT '总价格',
  `createtime` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`ogid`),
  KEY `orderid` (`orderid`),
  KEY `goods` (`goodsid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='订单（商品列表）';


DROP TABLE IF EXISTS `135k_order_no`;
CREATE TABLE `135k_order_no` (
  `out_trade_no` char(21) NOT NULL DEFAULT '' COMMENT '支付单号',
  `order_no` varchar(4000) NOT NULL COMMENT '订单编号',
  `uid` int(10) DEFAULT NULL COMMENT '用户id',
  PRIMARY KEY (`out_trade_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='多订单 支付日志表';


DROP TABLE IF EXISTS `135k_out_balance`;
CREATE TABLE `135k_out_balance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cid` int(11) NOT NULL,
  `bid` int(11) NOT NULL,
  `moeny` decimal(11,2) NOT NULL DEFAULT '0.00',
  `status` varchar(2) NOT NULL,
  `createtime` int(11) NOT NULL,
  `updatetime` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `135k_out_price`;
CREATE TABLE `135k_out_price` (
  `oid` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `bid` int(11) NOT NULL,
  `money` decimal(11,2) NOT NULL DEFAULT '0.00',
  `status` int(11) NOT NULL COMMENT '0未提现1通过提现-1拒绝',
  `name` varchar(30) NOT NULL,
  `card` varchar(255) NOT NULL,
  `cardname` varchar(60) NOT NULL,
  `createtime` int(11) NOT NULL,
  `updatetime` int(11) DEFAULT NULL,
  `alipay` varchar(50) DEFAULT NULL COMMENT '支付宝账户',
  PRIMARY KEY (`oid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `135k_percent`;
CREATE TABLE `135k_percent` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增主键',
  `bid` int(11) unsigned NOT NULL,
  `percent` float(10,3) unsigned NOT NULL COMMENT '佣金设置',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `135k_point`;
CREATE TABLE `135k_point` (
  `yuid` int(11) NOT NULL DEFAULT '0' COMMENT '预约id',
  `user` varchar(255) DEFAULT '' COMMENT '姓名',
  `tell` int(11) DEFAULT NULL COMMENT '电话',
  `email` varchar(11) DEFAULT '' COMMENT '邮箱',
  `createtime` int(10) DEFAULT NULL COMMENT '预约时间',
  PRIMARY KEY (`yuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='预约表';


DROP TABLE IF EXISTS `135k_position`;
CREATE TABLE `135k_position` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT '名字',
  `unit` int(11) NOT NULL COMMENT '排序',
  `time` int(11) NOT NULL COMMENT '事件',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='图片位置';


DROP TABLE IF EXISTS `135k_price_msg`;
CREATE TABLE `135k_price_msg` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `order_no` varchar(100) DEFAULT NULL,
  `paytype` varchar(20) NOT NULL COMMENT '消费类型',
  `msg` varchar(60) NOT NULL COMMENT '消息',
  `money` decimal(11,2) NOT NULL DEFAULT '0.00' COMMENT '金额',
  `createtime` int(11) NOT NULL,
  `oid` int(11) DEFAULT NULL,
  `type` int(2) NOT NULL DEFAULT '0',
  `cust` int(2) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='消费记录表';


DROP TABLE IF EXISTS `135k_price_order`;
CREATE TABLE `135k_price_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL COMMENT '用户ID',
  `bid` int(11) NOT NULL COMMENT '商户ID',
  `gid` int(11) NOT NULL COMMENT '商品表ID',
  `money` decimal(11,2) NOT NULL DEFAULT '0.00',
  `status` int(2) NOT NULL DEFAULT '0' COMMENT '0未支付1已支付',
  `order_no` varchar(30) NOT NULL COMMENT '订单号',
  `createtime` int(11) NOT NULL COMMENT '创建时间',
  `updatetime` int(11) DEFAULT NULL,
  `payway` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `135k_proxy`;
CREATE TABLE `135k_proxy` (
  `proxy_id` int(11) NOT NULL AUTO_INCREMENT,
  `proxy_uname` varchar(30) NOT NULL DEFAULT '' COMMENT '代理名称',
  `proxy_name` varchar(30) NOT NULL DEFAULT '' COMMENT '姓名',
  `proxy_tel` varchar(20) NOT NULL COMMENT '电话',
  `proxy_pass` char(40) NOT NULL,
  `proxy_cretime` int(11) NOT NULL,
  `proxy_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `proxy_status` int(2) NOT NULL DEFAULT '1',
  `bid` int(11) NOT NULL,
  `proxy_region` varchar(255) NOT NULL,
  `share` float(11,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`proxy_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `135k_qikucomment`;
CREATE TABLE `135k_qikucomment` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `name` varchar(30) NOT NULL COMMENT '分类名',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态（0：正常。1：不正常）',
  `createtime` int(11) NOT NULL COMMENT '时间',
  `bid` int(11) unsigned NOT NULL COMMENT '公司id',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='企库分类信息';


DROP TABLE IF EXISTS `135k_qikuinfor`;
CREATE TABLE `135k_qikuinfor` (
  `id` tinyint(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `name` varchar(150) NOT NULL COMMENT '企业名称',
  `contact` varchar(30) NOT NULL COMMENT '联系方式',
  `address` varchar(100) NOT NULL COMMENT '公司地址',
  `pic` varchar(150) NOT NULL,
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '状态（0：正常。1：取消）',
  `createtime` int(11) NOT NULL COMMENT '时间',
  `bid` int(11) unsigned NOT NULL COMMENT '公司id',
  `q_id` int(11) unsigned NOT NULL COMMENT '分类id',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='企库信息表';


DROP TABLE IF EXISTS `135k_qikuinformation`;
CREATE TABLE `135k_qikuinformation` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `q_id` int(11) NOT NULL COMMENT '企库分类id',
  `accounts` varchar(50) NOT NULL COMMENT '帐号',
  `password` varchar(100) NOT NULL COMMENT '密码',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态（0：使用中，1：弃用）',
  `createtime` int(11) NOT NULL COMMENT '时间',
  `bid` int(11) unsigned NOT NULL COMMENT '公司id',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='企库帐号表';


DROP TABLE IF EXISTS `135k_refund`;
CREATE TABLE `135k_refund` (
  `reid` int(11) NOT NULL AUTO_INCREMENT,
  `order_no` char(100) NOT NULL DEFAULT '' COMMENT '订单号',
  `pay_way` varchar(50) NOT NULL DEFAULT '' COMMENT '支付方式',
  `money` float(11,2) NOT NULL DEFAULT '0.00' COMMENT '退款金额',
  `explain` varchar(1000) NOT NULL DEFAULT '' COMMENT '退款说明',
  `pics` varchar(400) NOT NULL DEFAULT '' COMMENT '图片集',
  `reason` varchar(1000) NOT NULL DEFAULT '' COMMENT '拒绝理由',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0：提交退款   1：同意    2拒绝',
  `createtime` int(11) NOT NULL DEFAULT '0' COMMENT '提交时间',
  `handletime` int(11) NOT NULL DEFAULT '0' COMMENT '处理时间',
  PRIMARY KEY (`reid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='退款表';


DROP TABLE IF EXISTS `135k_region`;
CREATE TABLE `135k_region` (
  `region_id` int(11) NOT NULL AUTO_INCREMENT,
  `int_km` int(11) NOT NULL,
  `int_km_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `on_km` int(11) NOT NULL,
  `on_km_price` decimal(10,2) NOT NULL,
  `int_kg` int(11) NOT NULL,
  `int_kg_price` decimal(10,2) NOT NULL,
  `on_kg` int(11) NOT NULL,
  `on_kg_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `proxy_id` int(10) NOT NULL,
  `location` text,
  `bid` int(10) NOT NULL,
  `status` int(2) NOT NULL DEFAULT '1',
  PRIMARY KEY (`region_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `135k_reward`;
CREATE TABLE `135k_reward` (
  `fulfil_the_quota` float(11,2) NOT NULL DEFAULT '0.00' COMMENT '满额数',
  `reward` float(11,2) NOT NULL DEFAULT '0.00' COMMENT '奖励钱数',
  `restartime` int(11) NOT NULL DEFAULT '0' COMMENT '满减有效日期',
  `reendtime` int(11) NOT NULL DEFAULT '0' COMMENT '满减结束日期',
  `type` int(2) NOT NULL DEFAULT '0' COMMENT '奖励类型 1为满额 2 为满单',
  `status` int(2) NOT NULL DEFAULT '0' COMMENT '开启状态0未关闭1未开启',
  `bid` int(11) DEFAULT NULL COMMENT '商户id',
  `rid` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`rid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `135k_runorder`;
CREATE TABLE `135k_runorder` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '订单id',
  `goodsname` varchar(255) NOT NULL COMMENT '商品名',
  `mudadds` varchar(255) NOT NULL COMMENT '目的地地址',
  `myadds` varchar(255) NOT NULL COMMENT '收货地址',
  `price` float(10,2) NOT NULL COMMENT '价格',
  `times` varchar(255) NOT NULL COMMENT '配送时间',
  `time` int(11) NOT NULL COMMENT '下单时间',
  `uid` int(11) NOT NULL COMMENT '用户',
  `order_no` varchar(255) DEFAULT NULL COMMENT '订单号',
  `status` int(11) NOT NULL DEFAULT '0' COMMENT '订单状态：  0未付款   1待接单  2配送中    3已完成',
  `type` varchar(30) NOT NULL DEFAULT '1' COMMENT '类型',
  `payway` varchar(255) DEFAULT NULL COMMENT '支付方式',
  `redbao` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '红包',
  `tip` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '小费',
  `weight` int(11) NOT NULL DEFAULT '0' COMMENT '重量',
  `worth` int(11) DEFAULT '0' COMMENT '商品价值',
  `bid` int(11) DEFAULT '0' COMMENT '小程序id',
  `rid` int(11) DEFAULT '0' COMMENT '抢单者id',
  `f_price` float(10,2) DEFAULT '0.00' COMMENT '跑腿者返现金额',
  `p_price` float(10,2) DEFAULT '0.00' COMMENT '平台提现金额',
  `ins` float(10,2) DEFAULT '0.00' COMMENT '保险金额',
  `givetime` int(11) DEFAULT NULL COMMENT '抢单时间',
  `prepay_id` varchar(60) DEFAULT NULL,
  `distype` int(1) DEFAULT '0' COMMENT '是否议价',
  `message` text COMMENT '备注',
  `outtime` int(11) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL COMMENT '用户名',
  `phone` varchar(50) DEFAULT NULL COMMENT '电话',
  `oktime` int(11) DEFAULT NULL COMMENT '订单完成',
  `order_type` varchar(255) DEFAULT NULL,
  `old_order_no` varchar(255) DEFAULT NULL COMMENT '订单来源的order_no',
  `xphoto` varchar(255) DEFAULT NULL COMMENT '图片',
  `yinpin` varchar(255) DEFAULT NULL COMMENT '音频',
  `proxy_id` int(11) NOT NULL DEFAULT '0',
  `proxy_price` decimal(11,2) NOT NULL DEFAULT '0.00',
  `audiotime` int(10) DEFAULT '0' COMMENT '音频',
  `imgurl` text COMMENT '图片集',
  `sendinfo` varchar(2000) NOT NULL DEFAULT '0',
  `code` int(4) DEFAULT '0' COMMENT '验证码',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `135k_run_area`;
CREATE TABLE `135k_run_area` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `province` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '省',
  `city` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '市',
  `area` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '区',
  `bid` int(11) unsigned NOT NULL COMMENT 'bid',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `135k_run_dis_freight`;
CREATE TABLE `135k_run_dis_freight` (
  `freid` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `bid` int(11) NOT NULL DEFAULT '0' COMMENT '商家id',
  `title` varchar(50) NOT NULL DEFAULT '' COMMENT '标题',
  `city` varchar(3000) NOT NULL COMMENT '地址',
  `unit` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0：kg  1：个',
  `first` int(11) NOT NULL DEFAULT '0' COMMENT '首重',
  `freight` float(11,2) NOT NULL DEFAULT '0.00' COMMENT '首重运费',
  `next` int(11) NOT NULL DEFAULT '0' COMMENT '次重',
  `freight1` float(11,2) NOT NULL DEFAULT '0.00' COMMENT '次重运费费',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1：正常  0：删除',
  `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0：通用型  1：普通型',
  `createtime` int(11) NOT NULL DEFAULT '0' COMMENT '添加时间',
  `handletime` int(11) NOT NULL DEFAULT '0' COMMENT '修改时间',
  PRIMARY KEY (`freid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商家运费模板';


DROP TABLE IF EXISTS `135k_run_rules`;
CREATE TABLE `135k_run_rules` (
  `appname` varchar(150) DEFAULT NULL COMMENT '小程序名称',
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `distype` int(1) NOT NULL DEFAULT '0' COMMENT '是否开启议价 0： 未开启 ，1：开启  ',
  `sernum` int(5) NOT NULL DEFAULT '4' COMMENT '首页个数',
  `bid` int(10) NOT NULL COMMENT '小程序id',
  `poster` varchar(100) NOT NULL COMMENT '海报',
  `homepage` varchar(100) NOT NULL COMMENT '首页转发图片',
  `insprice` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '保单金额',
  `instype` int(1) NOT NULL DEFAULT '0' COMMENT '是否开启保单金额  0：未开启 ， 1： 开启',
  `copyright_status` int(1) NOT NULL DEFAULT '0' COMMENT '版权文字是否开启   0 为关闭 1 为开启',
  `reward` float(10,2) DEFAULT '0.00' COMMENT '悬赏金额',
  `copyright_wzs` varchar(100) NOT NULL COMMENT '版权文字内容',
  `jifen` int(10) NOT NULL COMMENT '积分',
  `jifenguize` varchar(255) NOT NULL COMMENT '积分规则',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `135k_run_setdate`;
CREATE TABLE `135k_run_setdate` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL COMMENT '标题',
  `date` text COMMENT '时间',
  `storeid` int(11) DEFAULT NULL COMMENT '小程序id',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `135k_run_yards`;
CREATE TABLE `135k_run_yards` (
  `bid` int(11) NOT NULL DEFAULT '0' COMMENT '小程序id',
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `keyid` varchar(100) DEFAULT NULL COMMENT '短信key',
  `keysecret` varchar(100) DEFAULT NULL COMMENT '短信秘钥',
  `signname` varchar(100) DEFAULT NULL COMMENT '签名',
  `templatecode` varchar(100) DEFAULT NULL COMMENT '模板id',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `135k_service`;
CREATE TABLE `135k_service` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '服务id',
  `name` varchar(100) DEFAULT NULL COMMENT '服务名称',
  `bid` int(11) DEFAULT NULL COMMENT '商家id',
  `pic` varchar(255) DEFAULT NULL COMMENT '服务图片',
  `cid` int(11) NOT NULL COMMENT '分类id',
  `status` int(1) DEFAULT '1' COMMENT '状态 1上线，0下线',
  `title` varchar(255) DEFAULT NULL COMMENT '服务简述',
  `biaoqian` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `135k_shang`;
CREATE TABLE `135k_shang` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL COMMENT '小程序名称',
  `appid` varchar(50) NOT NULL COMMENT '小程序appid',
  `appsecret` varchar(100) NOT NULL COMMENT '小程序appsecrement',
  `wxmchid` varchar(100) NOT NULL COMMENT '商户号',
  `wxpaysignkey` varchar(100) NOT NULL COMMENT '商户支付密钥',
  `wxpayapi` varchar(100) NOT NULL COMMENT '商户证书',
  `addtime` int(11) NOT NULL COMMENT '商户添加时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `135k_shes`;
CREATE TABLE `135k_shes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `url` varchar(255) NOT NULL,
  `time` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `135k_sign`;
CREATE TABLE `135k_sign` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `uid` int(10) NOT NULL COMMENT '用户id',
  `points` int(6) NOT NULL COMMENT '签到积分',
  `num` int(6) NOT NULL COMMENT '连续签到次数',
  `addtime` int(11) NOT NULL COMMENT '签到时间',
  `comment` varchar(255) NOT NULL COMMENT '备注1',
  `comments` varchar(255) NOT NULL COMMENT '备注2',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `135k_sponsor`;
CREATE TABLE `135k_sponsor` (
  `sponsor_id` int(11) NOT NULL AUTO_INCREMENT,
  `sponsor_name` varchar(255) NOT NULL,
  `sponsor_phone` varchar(255) NOT NULL,
  `sponsor_address` varchar(255) NOT NULL,
  `sponsor_content` longtext NOT NULL,
  `sponsor_time` int(11) NOT NULL,
  `sponsor_img` varchar(255) NOT NULL,
  PRIMARY KEY (`sponsor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `135k_stitc`;
CREATE TABLE `135k_stitc` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `times` varchar(20) NOT NULL,
  `leijip` int(11) NOT NULL,
  `open` int(11) NOT NULL,
  `leijiu` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `135k_subscribe`;
CREATE TABLE `135k_subscribe` (
  `sid` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `bid` int(11) NOT NULL DEFAULT '0' COMMENT '预约商家 id',
  `goodsid` int(11) NOT NULL DEFAULT '0' COMMENT '预约商品 id',
  `items` varchar(100) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '服务项目',
  `upid` int(11) NOT NULL DEFAULT '0' COMMENT '预约 东西id',
  `remark` text CHARACTER SET utf8 NOT NULL COMMENT '预约备注',
  `starttime` int(11) NOT NULL DEFAULT '0' COMMENT '开始时间',
  `endtime` int(11) NOT NULL DEFAULT '0' COMMENT '结束时间',
  `uid` int(11) NOT NULL DEFAULT '0' COMMENT '预约用户 id',
  `pay_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0：代付款 1：已付款  2：已完成 3：未退款 4：已退款',
  `money` float(2,0) NOT NULL DEFAULT '0' COMMENT '预支付金额',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0：未确认 1：已确认 2：已完成 3：已拒绝 -1：已取消',
  `createtime` int(11) NOT NULL DEFAULT '0' COMMENT '预约时间',
  PRIMARY KEY (`sid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='预约表';


DROP TABLE IF EXISTS `135k_take_out`;
CREATE TABLE `135k_take_out` (
  `takeid` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0',
  `bid` int(11) NOT NULL DEFAULT '0',
  `money` float(11,2) NOT NULL DEFAULT '0.00',
  `name` varchar(20) NOT NULL DEFAULT '' COMMENT '姓名',
  `alipay` varchar(50) NOT NULL DEFAULT '' COMMENT '支付宝账户',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0：发起提现    1：同意   -1：拒绝',
  `createtime` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `verifytime` int(11) NOT NULL DEFAULT '0' COMMENT '确认时间',
  `is_brank` int(1) NOT NULL DEFAULT '0' COMMENT '0不是退款到银行卡1 是退款到银行卡',
  `brank` varchar(255) NOT NULL COMMENT '卡号',
  `kaihuhang` varchar(255) NOT NULL COMMENT '开户行',
  PRIMARY KEY (`takeid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='提现记录表';


DROP TABLE IF EXISTS `135k_three_class`;
CREATE TABLE `135k_three_class` (
  `tid` int(11) NOT NULL AUTO_INCREMENT,
  `sname` varchar(255) DEFAULT NULL COMMENT '分类名称',
  `bid` int(11) NOT NULL,
  `pid` int(11) NOT NULL,
  `status` int(1) NOT NULL DEFAULT '1',
  `my_status` int(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`tid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `135k_top_up`;
CREATE TABLE `135k_top_up` (
  `topid` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0',
  `bid` int(11) NOT NULL DEFAULT '0',
  `order_no` varchar(100) NOT NULL DEFAULT '' COMMENT '订单号',
  `money` float(11,2) NOT NULL DEFAULT '0.00' COMMENT '金额',
  `pay_user` varchar(100) NOT NULL DEFAULT '' COMMENT '支付用户',
  `pay_serial_number` varchar(100) NOT NULL DEFAULT '' COMMENT '交易流水号',
  `paytime` int(11) NOT NULL DEFAULT '0' COMMENT '支付时间',
  `pay_way` varchar(20) NOT NULL DEFAULT '' COMMENT '支付方式',
  PRIMARY KEY (`topid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='充值表';


DROP TABLE IF EXISTS `135k_tp_subscribe`;
CREATE TABLE `135k_tp_subscribe` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uid` varchar(11) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sex` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `range` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `area` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `others` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `addtime` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `135k_unit`;
CREATE TABLE `135k_unit` (
  `unid` int(11) NOT NULL AUTO_INCREMENT,
  `bid` int(11) NOT NULL DEFAULT '0' COMMENT '商家id',
  `unit` varchar(50) NOT NULL DEFAULT '' COMMENT '单位',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0：删除   1：正常',
  `createtime` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`unid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商品单位表';


DROP TABLE IF EXISTS `135k_user`;
CREATE TABLE `135k_user` (
  `uid` int(11) NOT NULL AUTO_INCREMENT COMMENT '用户uid',
  `bid` int(11) DEFAULT '0' COMMENT '商家id',
  `phone` varchar(20) DEFAULT '' COMMENT '电话',
  `nickname` varchar(200) NOT NULL DEFAULT '' COMMENT '昵称',
  `name` varchar(200) DEFAULT '' COMMENT '真实姓名',
  `head` varchar(255) NOT NULL DEFAULT '' COMMENT '用户头像',
  `sex` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0：无 1：男 2：女',
  `birthday` char(10) DEFAULT '' COMMENT '出生日期',
  `like` varchar(300) DEFAULT '' COMMENT '爱好(''1'',''2'',''3'')',
  `job` varchar(300) DEFAULT '' COMMENT '工作',
  `openid` varchar(100) NOT NULL DEFAULT '' COMMENT 'openid',
  `dev_type` varchar(10) DEFAULT '' COMMENT '手机类型',
  `push_id` varchar(100) DEFAULT '0' COMMENT '推送cid',
  `device` varchar(100) DEFAULT '' COMMENT '手机设备id',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '-1: 删除   0: 正常',
  `address` varchar(300) NOT NULL DEFAULT '' COMMENT '地址',
  `regtime` int(11) NOT NULL DEFAULT '0' COMMENT '注册时间',
  `updatetime` int(11) DEFAULT '0' COMMENT '修改时间',
  `login_status` tinyint(1) DEFAULT '0' COMMENT '0未登录,1登陆',
  `f_id` int(11) NOT NULL DEFAULT '0' COMMENT '父级id',
  `money` float(11,2) NOT NULL DEFAULT '0.00' COMMENT '充值金额',
  `integral` int(11) NOT NULL DEFAULT '0' COMMENT '积分',
  `balance` float(11,2) NOT NULL DEFAULT '0.00' COMMENT '提成',
  `discounts` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否优惠 0：是  1：否',
  `looknum` int(11) DEFAULT NULL COMMENT '访问次数',
  `looktime` int(10) DEFAULT '0' COMMENT '访问时间',
  `level` int(10) NOT NULL DEFAULT '0' COMMENT '0普通用户1跑腿用户',
  `password` varchar(40) DEFAULT NULL,
  `redstatus` int(2) NOT NULL DEFAULT '0' COMMENT '0以领取1未领取',
  `vip` int(11) NOT NULL DEFAULT '0' COMMENT 'vip截止时间',
  `viptime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'vip购买时间',
  `order` varchar(100) NOT NULL DEFAULT '0' COMMENT '购买vip订单号',
  `step` int(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否会员1是',
  `member_grade` int(5) NOT NULL DEFAULT '0' COMMENT '会员等级',
  `hyintegral` int(11) NOT NULL DEFAULT '0' COMMENT '会员积分',
  `updateviptime` int(11) DEFAULT NULL COMMENT '会员到期扣除会员积分的时间',
  `sign` int(3) NOT NULL DEFAULT '0' COMMENT '本月签到次数',
  `lianxuday` int(5) NOT NULL DEFAULT '0' COMMENT '本月连续签到天数',
  `signtime` int(11) NOT NULL DEFAULT '0' COMMENT '签到时间',
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户信息表';


DROP TABLE IF EXISTS `135k_user1`;
CREATE TABLE `135k_user1` (
  `uid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` int(11) unsigned NOT NULL COMMENT 'user表id',
  `name` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `phone` varchar(11) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `sex` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `radio2` varchar(6) COLLATE utf8_unicode_ci NOT NULL,
  `school` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `credit` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `qq` varchar(12) COLLATE utf8_unicode_ci NOT NULL,
  `vx` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `sign` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `avatar` varchar(300) COLLATE utf8_unicode_ci NOT NULL,
  `self_introduction` varchar(300) COLLATE utf8_unicode_ci NOT NULL,
  `consultant` varchar(300) COLLATE utf8_unicode_ci NOT NULL,
  `collection_store` varchar(6) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `addtime` int(11) NOT NULL,
  `credit_degree` varchar(6) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `follow` varchar(6) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `vip` varchar(4) COLLATE utf8_unicode_ci NOT NULL,
  `viptime` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `price` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `order` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `recommend` char(20) COLLATE utf8_unicode_ci NOT NULL COMMENT '推荐人',
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `135k_usertag`;
CREATE TABLE `135k_usertag` (
  `tagid` int(11) NOT NULL AUTO_INCREMENT,
  `bid` int(11) NOT NULL,
  `tagname` varchar(60) NOT NULL,
  `num` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`tagid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `135k_visit_log`;
CREATE TABLE `135k_visit_log` (
  `bid` int(11) NOT NULL DEFAULT '0',
  `uid` int(11) NOT NULL DEFAULT '0',
  `visit_ip` varchar(50) NOT NULL DEFAULT '' COMMENT '访问id',
  `creattime` int(11) NOT NULL DEFAULT '0' COMMENT '访问时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `135k_week`;
CREATE TABLE `135k_week` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `wprice` float(10,2) DEFAULT '0.00' COMMENT '服务金额',
  `week` varchar(255) DEFAULT NULL COMMENT '星期',
  `time` int(11) DEFAULT NULL COMMENT '时间',
  `bid` int(11) DEFAULT NULL COMMENT '小程序id',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `135k_wxnav`;
CREATE TABLE `135k_wxnav` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `navtype` text,
  `value` text,
  `bid` text,
  `createtime` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `135k_wx_service`;
CREATE TABLE `135k_wx_service` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bid` int(11) NOT NULL,
  `appid` varchar(255) DEFAULT NULL,
  `secret` varchar(255) DEFAULT NULL,
  `time` int(11) DEFAULT NULL,
  `token` text,
  `template_id` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `135k_yestoday`;
CREATE TABLE `135k_yestoday` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `yestody` int(11) NOT NULL,
  `opens` int(11) NOT NULL,
  `stay_time_uv` int(11) NOT NULL,
  `visit_uv` int(11) NOT NULL,
  `visit_pv` int(11) NOT NULL,
  `times` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;





sql;
$row = pdo_run($installSql);