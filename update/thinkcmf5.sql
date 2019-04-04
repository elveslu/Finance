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


-- 支付单表
DROP TABLE IF EXISTS `cmf_shop_payments`;
CREATE TABLE `cmf_shop_payments` (
  `payment_id` varchar(20) NOT NULL DEFAULT '' COMMENT '支付单id',
  `money` decimal(20,3) NOT NULL DEFAULT '0.000' COMMENT '支付金额',
  `user_id` varchar(100) DEFAULT NULL COMMENT '会员id',
  `status` enum('succ','failed','cancel','error','invalid','progress','timeout','ready') NOT NULL DEFAULT 'ready' COMMENT '状态',
  `pay_name` varchar(100) DEFAULT NULL COMMENT '支付方式',
  `pay_type` enum('online','offline','deposit') NOT NULL DEFAULT 'online' COMMENT '支付类型',
  `t_payed` int(10) unsigned DEFAULT NULL COMMENT '支付时间',
  `account` varchar(50) DEFAULT NULL COMMENT '收款账号',
  `bank` varchar(50) DEFAULT NULL COMMENT '收款银行',
  `pay_account` varchar(50) DEFAULT NULL COMMENT '支付账号',
  `pay_app_id` varchar(100) NOT NULL DEFAULT '0' COMMENT '支付方式id',
  `t_begin` int(10) unsigned DEFAULT NULL COMMENT '创建时间',
  `memo` longtext COMMENT '备注',
  `return_url` varchar(100) DEFAULT NULL COMMENT '返回url',
  `trade_no` varchar(30) DEFAULT NULL COMMENT '外部订单号',
  PRIMARY KEY (`payment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='支付单表';
