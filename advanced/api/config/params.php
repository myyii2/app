<?php
return [
    'adminEmail' => 'admin@example.com',
    'SRC_TYPE_COURSE' => 2,
        //缓存信息数组
    'Redis' => [
        'mainBannerKey' => 'main_banner_status_',   //主banner设置状态
        'homeMailKey' => 'home_mail_',  //首页邮件信息缓存
        'homeTaskManagerKey' => 'home_taskmanager_',     //首页任务管理信息缓存
        'deptTaskManagerKey' => 'home_dept_taskmanager_',   //部门管理员首页任务管理信息缓存
        'homeMyTaskKey' => 'home_mytask_',  //首页待办任务信息缓存
        'homeMsgKey' => 'home_msg_',        //首页消息缓存
        'homeMemberKey' => 'home_member_',  //首页会员申请记录缓存
        'userCommonKey' => 'user_common_',  //用户常用信息缓存
        'deptTreeKey' => 'dept_tree_',      //组织架构树信息缓存
        'staffInfoKey' => 'staff_info_',    //员工信息缓存
    ],
    'Task' => [
        'TASK_STATUS_DOING' => 1,   //考核中任务状态码
        'TASK_STATUS_DONE' => 2,   //已过期
        'TASK_TYPE_TRAIN' => 1,   //内训
        'TASK_TYPE_INTERVIEW' => 0,   //面试
        'TASK_TYPE_MEMBER' => 3,   //会员
    ],
    'system' => [
        'PAGE_SIZE' => 10,
    ],
    'BASE_URL' => '.mycs.cn',
    'TYPE_VIDEO' => 1,
    'RES_VIDEO_MP4' => 3,
    'RES_PIC' => 2,
    'RES_VIDEO' => 1,
    'CODE_SUCCEED' => 1,
    'CODE_ERROR' => 2,
    'CODE_LACK' => 3,
    
    
];
