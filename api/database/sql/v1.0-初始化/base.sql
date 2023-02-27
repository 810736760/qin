DROP TABLE IF EXISTS `term`;
CREATE TABLE `term`
(
    `id`         int(10) unsigned NOT NULL AUTO_INCREMENT,
    `event_date` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '学期',
    `name`       varchar(255)     NOT NULL DEFAULT '' COMMENT '学期名称',
    `key`        varchar(32)      NOT NULL DEFAULT '' COMMENT '加密分享路径',
    `created_at` timestamp        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`) USING BTREE,
    unique index `uniq_name` (`name`) USING BTREE,
    KEY `idx_event_date` (`event_date`) USING BTREE,
    KEY `idx_key` (`key`) USING BTREE
) ENGINE = InnoDB
    COMMENT ='学期表';
insert into term(`name`, `event_date`)
values ('2023年春季', 20230101);


DROP TABLE IF EXISTS `teacher_class`;
CREATE TABLE `teacher_class`
(
    `id`           int(10) unsigned    NOT NULL AUTO_INCREMENT,
    `tid`          int(10) unsigned    NOT NULL DEFAULT '0' COMMENT '学期号',
    `tel`          bigint(11) unsigned NOT NULL DEFAULT '0' COMMENT '手机号',
    `school_name`  varchar(255)        NOT NULL DEFAULT '' COMMENT '学校名称',
    `class_name`   varchar(255)        NOT NULL DEFAULT '' COMMENT '课程名称',
    `date_index`   tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '周几 0是周日',
    `teacher_name` varchar(255)        NOT NULL DEFAULT '' COMMENT '教师名称',
    `price`        int(10) unsigned    NOT NULL DEFAULT '0' COMMENT '单价 单位分',
    `class_locate` varchar(255)        NOT NULL DEFAULT '' COMMENT '教室地址',
    `is_delete`    tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否删除',
    `start_time`   int(10) unsigned    NOT NULL DEFAULT '0' COMMENT '上课开始时间',
    `end_time`     int(10) unsigned    NOT NULL DEFAULT '0' COMMENT '上课结束时间',
    `created_at`   timestamp           NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`   timestamp           NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`) USING BTREE,
    KEY `idx_tid_tel` (`tid`, `tel`) USING BTREE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
    COMMENT ='教师班级表';


insert into teacher_class(`tid`, `tel`, `school_name`, `class_name`, `date_index`, `teacher_name`, `price`,
                          `class_locate`)
values ('2', 17318018915, '一小', '兴趣课', '1', '张三', 24500, '1号楼203'),
       ('2', 17318018915, '一小', '造物记', '2', '张三', 24500, '1号楼204'),
       ('2', 18925251123, '二小', '兴趣课', '1', '李四', 24500, '10号楼203'),
       ('2', 18925251123, '一小', '兴趣课', '3', '李四', 24500, '11号楼205');


DROP TABLE IF EXISTS `teacher_class_log`;
CREATE TABLE `teacher_class_log`
(
    `id`         int(10) unsigned NOT NULL AUTO_INCREMENT,
    `sid`        int(10) unsigned NOT NULL DEFAULT '0' COMMENT '原ID',
    `tid`        int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属期数',
    `old_record` text             NOT NULL COMMENT '原先参数',
    `new_record` text             NOT NULL COMMENT '新参数',
    `use`        tinyint unsigned NOT NULL DEFAULT '0' COMMENT '使用使用',
    `created_at` timestamp        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`) USING BTREE,
    KEY `idx_created_at_tid` (`created_at`, `tid`) USING BTREE
) ENGINE = InnoDB
    COMMENT ='教师班级修改记录';


CREATE TABLE `user_action_log`
(
    `id`         int unsigned NOT NULL AUTO_INCREMENT,
    `admin_id`   int unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
    `nick_name`  varchar(255) NOT NULL DEFAULT '' COMMENT '用户名',
    `path`       varchar(255) NOT NULL DEFAULT '' COMMENT '路由',
    `ip`         varchar(50)  NOT NULL DEFAULT '' COMMENT 'ip',
    `params`     text         NOT NULL COMMENT '参数',
    `created_at` timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`) USING BTREE,
    KEY `idx_created_at_admin_id` (`created_at`, `admin_id`) USING BTREE
) ENGINE = InnoDB
  ROW_FORMAT = DYNAMIC COMMENT ='后台操作日志';
