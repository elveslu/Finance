/*
 Navicat Premium Data Transfer

 Source Server         : localhost
 Source Server Type    : MySQL
 Source Server Version : 50717
 Source Host           : localhost
 Source Database       : thinkcmf5

 Target Server Type    : MySQL
 Target Server Version : 50717
 File Encoding         : utf-8

 Date: 05/11/2017 08:06:45 AM
*/

SET NAMES utf8;
SET FOREIGN_KEY_CHECKS = 0;

-- 产品表
DROP TABLE IF EXISTS `cmf_goods`;
CREATE TABLE `cmf_goods` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属用户',
  `name` varchar(200) NOT NULL DEFAULT '' COMMENT '商品名称',
  `buying_price` float NOT NULL DEFAULT '0' COMMENT '进货单价',
  `vip_price` float NOT NULL DEFAULT '0' COMMENT 'vip价',
  `provincial_price` float NOT NULL DEFAULT '0' COMMENT '省代价',
  `GM_price` float NOT NULL DEFAULT '0' COMMENT '总监价',
  `retail_price` float NOT NULL DEFAULT '0' COMMENT '零售价',
  `size` int(11) NOT NULL DEFAULT '18' COMMENT '每箱数量',
  `num` int(11) NOT NULL DEFAULT '0' COMMENT '库存',
  `description` varchar(255) NOT NULL DEFAULT '' COMMENT '商品描述',
  `status` enum('0','1') DEFAULT '1' COMMENT '状态：1-上架 0-下架',
  `list_order` float NOT NULL DEFAULT '10000' COMMENT '排序',
  `create_time` int(10) NOT NULL DEFAULT '0' COMMENT '添加时间',
  `delete_time` int(10) NOT NULL DEFAULT '0' COMMENT '删除时间',
  `update_time` int(10) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `published_time` int(10) NOT NULL DEFAULT '0' COMMENT '发布时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='商品表';


-- 单据表
DROP TABLE IF EXISTS `cmf_finance_bound`;
CREATE TABLE `cmf_finance_bound` (
  `bound_id` varchar(20) NOT NULL DEFAULT '' COMMENT '单据id',
  `money` decimal(20,3) NOT NULL DEFAULT '0.000' COMMENT '单据金额',
  `float_money` decimal(20,3) NOT NULL DEFAULT '0.000' COMMENT '浮动金额',
  `amount` decimal(20,3) NOT NULL DEFAULT '0.000' COMMENT '最终金额',
  `user_id` varchar(100) DEFAULT NULL COMMENT '会员id',
  `type` varchar(100) NOT NULL DEFAULT 'inBound' COMMENT '单据类型-inBound,outBound',
  `memo` longtext COMMENT '单据备注',
  `goods` longtext NOT NULL COMMENT '产品',
  `createtime` int(10) unsigned DEFAULT NULL COMMENT '生成时间',
  PRIMARY KEY (`bound_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='单据表';

-- 产品组合表
DROP TABLE IF EXISTS `cmf_goods_group`;
CREATE TABLE `cmf_goods_group` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属用户',
  `name` varchar(200) NOT NULL DEFAULT '' COMMENT '组合名称',
  `group_price` float NOT NULL DEFAULT '0' COMMENT '组合零售价',
  `count` int(11) NOT NULL DEFAULT '2' COMMENT '组合数量',
  `status` enum('0','1') DEFAULT '1' COMMENT '状态：1-上架 0-下架',
  `published_time` int(10) NOT NULL DEFAULT '0' COMMENT '发布时间',
  `delete_time` int(10) NOT NULL DEFAULT '0' COMMENT '删除时间',
  `update_time` int(10) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='产品组合表';

-- 添加零售组合价
ALTER TABLE `cmf_goods` ADD `group_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '组合价id';

-- 添加出货价格
ALTER TABLE `cmf_finance_bound` ADD `price_grade` varchar(100) NOT NULL DEFAULT 'inBound' COMMENT '出货等级-inBound,vip_price,provincial_price,GM_price,retail_price';

-- 添加是否正式库如
ALTER TABLE `cmf_finance_bound` ADD `status` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '是否正式入库：0-未入库，1-正式入库';
-- 添加套装优惠金额
ALTER TABLE `cmf_finance_bound` ADD `grade_pmt` decimal(20,3) NOT NULL DEFAULT '0.000' COMMENT '套装优惠金额';

-- 添加进货单位
ALTER TABLE `cmf_finance_bound` ADD `unit` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '进货单位：1-箱，2-个';
