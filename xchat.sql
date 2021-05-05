/*
 Navicat Premium Data Transfer

 Source Server         : 127.0.0.1
 Source Server Type    : MariaDB
 Source Server Version : 100122
 Source Host           : 127.0.0.1:3306
 Source Schema         : xchat

 Target Server Type    : MariaDB
 Target Server Version : 100122
 File Encoding         : 65001

 Date: 05/05/2021 10:42:59
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for im_chatlog
-- ----------------------------
DROP TABLE IF EXISTS `im_chatlog`;
CREATE TABLE `im_chatlog`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `sender` char(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '消息发送者UID',
  `sender_nickname` char(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `sender_avatar` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `message` varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '消息内容',
  `receiver` char(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '接收者UID',
  `receiver_nickname` char(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `receiver_avatar` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `refer` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '来源',
  `city` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '城市',
  `num` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '访问次数',
  `utime` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '最近访问时间',
  `ctime` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '消息发送时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `sender`(`sender`) USING BTREE,
  INDEX `receiver`(`receiver`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 18 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of im_chatlog
-- ----------------------------
INSERT INTO `im_chatlog` VALUES (1, '1', '客服2', '/static/chat/img/logo.png', '<img src=\"/static/chat/img/face/47.gif\" class=\"emoj\">', 'afb0e3a73153aaf990e4dee21d1a22ab', '', '', '', '', 0, 1619451386, 1619451386);
INSERT INTO `im_chatlog` VALUES (2, '208f0ec278cfbf9bfb2f1c8a624442bb', '本机地址网友', '/static/chat/img/noavatar.jpg', '<img src=\"/static/chat/img/face/21.gif\" class=\"emoj\">', '1', '', '', 'http://chat.me/xchat/visitor', '本机地址', 0, 1620051878, 1620045490);
INSERT INTO `im_chatlog` VALUES (3, '1', '客服2', '/static/chat/img/logo.png', '<img src=\"/static/chat/img/face/33.gif\" class=\"emoj\">', '208f0ec278cfbf9bfb2f1c8a624442bb', '', '', 'http://chat.me/xchat/visitor', '本机地址', 0, 1620050743, 1620045495);
INSERT INTO `im_chatlog` VALUES (4, '1', '客服2', '/static/chat/img/logo.png', '<img src=\"/static/chat/img/face/40.gif\" class=\"emoj\">', '2', '', '', '', '', 0, 1620050772, 1620050772);
INSERT INTO `im_chatlog` VALUES (5, '1', '客服2', '/static/chat/img/logo.png', '<img src=\"/static/chat/img/face/32.gif\" class=\"emoj\">', '2', '', '', '', '', 0, 1620050783, 1620050783);
INSERT INTO `im_chatlog` VALUES (6, '208f0ec278cfbf9bfb2f1c8a624442bb', '本机地址网友', '/static/chat/img/noavatar.jpg', '<img src=\"/static/chat/img/face/32.gif\" class=\"emoj\">', '1', '', '', '', '', 0, 1620051892, 1620051892);
INSERT INTO `im_chatlog` VALUES (7, '1', '客服2', '/static/chat/img/logo.png', '<img src=\"/static/chat/img/face/42.gif\" class=\"emoj\">', '208f0ec278cfbf9bfb2f1c8a624442bb', '', '', '', '', 0, 1620051983, 1620051983);
INSERT INTO `im_chatlog` VALUES (8, '208f0ec278cfbf9bfb2f1c8a624442bb', '本机地址网友', '/static/chat/img/noavatar.jpg', '<img src=\"/static/chat/img/face/40.gif\" class=\"emoj\">', '1', '', '', 'http://chat.me/xchat/visitor', '本机地址', 0, 1620052175, 1620051988);
INSERT INTO `im_chatlog` VALUES (9, '208f0ec278cfbf9bfb2f1c8a624442bb', '本机地址网友', '/static/chat/img/noavatar.jpg', '<img src=\"/static/chat/img/face/49.gif\" class=\"emoj\">', '1', '', '', 'http://chat.me/xchat/visitor', '本机地址', 0, 1620052229, 1620052186);
INSERT INTO `im_chatlog` VALUES (10, '1', '客服2', '/static/chat/img/logo.png', '<img src=\"/static/chat/img/face/42.gif\" class=\"emoj\">', '208f0ec278cfbf9bfb2f1c8a624442bb', '', '', '', '', 0, 1620052229, 1620052229);
INSERT INTO `im_chatlog` VALUES (11, '208f0ec278cfbf9bfb2f1c8a624442bb', '本机地址网友', '/static/chat/img/noavatar.jpg', '<img src=\"/static/chat/img/face/49.gif\" class=\"emoj\">', '1', '', '', 'http://chat.me/xchat/visitor', '本机地址', 0, 1620052723, 1620052237);
INSERT INTO `im_chatlog` VALUES (12, '1', '客服2', '/static/chat/img/logo.png', '<img src=\"/static/chat/img/face/66.gif\" class=\"emoj\">', '208f0ec278cfbf9bfb2f1c8a624442bb', '', '', '', '', 0, 1620052241, 1620052241);
INSERT INTO `im_chatlog` VALUES (13, '1', '客服2', '/static/chat/img/logo.png', '<img src=\"/static/chat/img/face/41.gif\" class=\"emoj\">', '208f0ec278cfbf9bfb2f1c8a624442bb', '', '', '', '', 0, 1620052734, 1620052734);
INSERT INTO `im_chatlog` VALUES (14, '208f0ec278cfbf9bfb2f1c8a624442bb', '本机地址网友', '/static/chat/img/noavatar.jpg', '<img src=\"/static/chat/img/face/41.gif\" class=\"emoj\">', '1', '', '', 'http://chat.me/xchat/visitor', '本机地址', 0, 1620053518, 1620052738);
INSERT INTO `im_chatlog` VALUES (15, '1', '客服2', '/static/chat/img/logo.png', '<img src=\"/upload/xchat/20210503713a36cf3e5393006bc01bb771931323.jpg\" />', '208f0ec278cfbf9bfb2f1c8a624442bb', '', '', '', '', 0, 1620052742, 1620052742);
INSERT INTO `im_chatlog` VALUES (16, '208f0ec278cfbf9bfb2f1c8a624442bb', '访客', '/static/chat/img/noavatar.jpg', '<img src=\"/static/chat/img/face/41.gif\" class=\"emoj\">', '1', '', '', '', '', 0, 1620100098, 1620100098);
INSERT INTO `im_chatlog` VALUES (17, '1', '客服2', '/static/chat/img/logo.png', '<img src=\"/static/chat/img/face/50.gif\" class=\"emoj\">', '208f0ec278cfbf9bfb2f1c8a624442bb', '', '', '', '', 0, 1620100103, 1620100103);

-- ----------------------------
-- Table structure for im_match
-- ----------------------------
DROP TABLE IF EXISTS `im_match`;
CREATE TABLE `im_match`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `uid` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `type` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '0公共,1个人',
  `name` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '关键词',
  `content` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '匹配内容',
  `sort` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `ctime` int(10) UNSIGNED NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 5 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of im_match
-- ----------------------------
INSERT INTO `im_match` VALUES (1, 1, 0, '无欲无求', '遨游于天地', 0, 1618052240);
INSERT INTO `im_match` VALUES (2, 1, 1, '青酒红人面', '财帛动人心', 0, 1618052428);
INSERT INTO `im_match` VALUES (3, 2, 0, '江湖', '三女侠', 0, 1619270209);
INSERT INTO `im_match` VALUES (4, 2, 1, '客服系统取名', '暂取名携心客服系统，求荐名', 0, NULL);

-- ----------------------------
-- Table structure for im_reply
-- ----------------------------
DROP TABLE IF EXISTS `im_reply`;
CREATE TABLE `im_reply`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `pid` int(10) UNSIGNED NULL DEFAULT 0 COMMENT '分组id',
  `name` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '名称或关键词',
  `content` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `type` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '0默认展示,1自动欢迎',
  `sort` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `ctime` int(10) UNSIGNED NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 11 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of im_reply
-- ----------------------------
INSERT INTO `im_reply` VALUES (1, 0, '常见问题一', '', 2, 0, NULL);
INSERT INTO `im_reply` VALUES (2, 1, '你是谁？', '大龄单身程序青年小帅', 2, 0, NULL);
INSERT INTO `im_reply` VALUES (3, 1, '客服系统叫什么', 'XChat', 2, 0, NULL);
INSERT INTO `im_reply` VALUES (4, 0, '常见问题二', '', 2, 0, NULL);
INSERT INTO `im_reply` VALUES (5, 4, '你好', '你好', 2, 0, NULL);
INSERT INTO `im_reply` VALUES (6, 4, '侠客行', '赵客缦胡缨，吴钩霜雪明。 银鞍照白马，飒沓如流星。 十步杀一人，千里不留行。 事了拂衣去，深藏身与名。 闲过信陵饮，脱剑膝前横。 将炙啖朱亥，持觞劝侯嬴。 三杯吐然诺，五岳倒为轻。 眼花耳热后，意气素霓生。 救赵挥金槌，邯郸先震惊。 千秋二壮士，烜赫大梁城。 纵死侠骨香，不惭世上英。 谁能书阁下，白首太玄经。', 2, 0, NULL);
INSERT INTO `im_reply` VALUES (7, 0, '欢迎光临1', '【自动欢迎】欢迎光临d', 1, 0, NULL);
INSERT INTO `im_reply` VALUES (8, 0, '欢迎光临2', '【自动欢迎】欢迎来到携信客服', 1, 0, NULL);
INSERT INTO `im_reply` VALUES (9, 0, '简介', '', 0, 0, 1620045654);
INSERT INTO `im_reply` VALUES (10, 9, '下载', '<img src=\"/static/chat/img/face/3.gif\" class=\"emoj\">ddd', 0, 1, 1620045686);

-- ----------------------------
-- Table structure for im_user
-- ----------------------------
DROP TABLE IF EXISTS `im_user`;
CREATE TABLE `im_user`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '客服用户uid',
  `uid` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '用户ID（你的系统用户uid)',
  `nickname` char(15) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `avatar` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `sort` tinyint(2) UNSIGNED NOT NULL DEFAULT 0 COMMENT '排序',
  `login_status` char(8) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `login_ip` char(15) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `login_num` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `login_time` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `logout` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '退出时间',
  `ctime` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `status` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uid`(`uid`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Records of im_user
-- ----------------------------
INSERT INTO `im_user` VALUES (1, 2, '客服1', '/static/chat/img/logo.png', 2, 'offline', '127.0.0.1', 1, 1619447830, 1619447858, 1510122006, 0);
INSERT INTO `im_user` VALUES (2, 1, '客服2', '/static/chat/img/logo.png', 1, 'offline', '127.0.0.1', 1, 1620094874, 1620100105, 1512469698, 0);

SET FOREIGN_KEY_CHECKS = 1;
