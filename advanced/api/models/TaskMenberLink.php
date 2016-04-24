<?php

namespace api\models;

use Yii;

/**
 * This is the model class for table "task_menber_link".
 *
 * @property integer $id
 * @property integer $task_id
 * @property string $member_uid
 * @property string $staffUid
 * @property integer $staffMaxCount
 * @property integer $gradeId
 * @property integer $isSop
 * @property integer $addTime
 * @property integer $status
 * @property integer $actualNum
 * @property integer $passNum
 */
class TaskMenberLink extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'task_menber_link';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db1');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['task_id', 'member_uid', 'staffMaxCount', 'gradeId', 'isSop', 'addTime', 'status', 'actualNum', 'passNum'], 'integer'],
            [['staffUid'], 'string'],
            [['gradeId'], 'required']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('api', 'ID'),
            'task_id' => Yii::t('api', '机构发布任务id'),
            'member_uid' => Yii::t('api', '机构会员或考证购买者uid'),
            'staffUid' => Yii::t('api', '接收任务的员工id，格式为json转义后的uid数组，为空数组则为个人用户任务'),
            'staffMaxCount' => Yii::t('api', '任务可发布的最大员工数'),
            'gradeId' => Yii::t('api', '用户等级ID'),
            'isSop' => Yii::t('api', '是否为sop任务，0--否，1--是'),
            'addTime' => Yii::t('api', '添加时间'),
            'status' => Yii::t('api', '状态，3 系统发布中 0-未发布 1-已发布 2-已过期 , -1 已删除,4任务终止'),
            'actualNum' => Yii::t('api', '参与考核人数'),
            'passNum' => Yii::t('api', '通过考核人数'),
        ];
    }

    /*
     * 获得会员考核
     */
    static function getListByWhere($page=1,$pageSize=10,$whereStr="",$order_by = '')
    {
        $sql = "SELECT count(1) as count FROM `task_menber_link` as tml LEFT JOIN `task` as t on (t.taskId = tml.task_id and t.status != -1 ) where 1 {$whereStr} " ;

        $connection = \Yii::$app->db1;
        $command = $connection->createCommand($sql);
        $total = $command->queryOne();

        $sql1 = "SELECT tml.id , tml.staffUid , tml.staffMaxCount ,t.taskName,t.course_id,t.status,t.issueTime,t.endTime,tml.task_id FROM `task_menber_link` as tml LEFT JOIN `task` as t on (t.taskId = tml.task_id and t.status != -1 ) where 1 {$whereStr} {$order_by} limit " .($page -1 ) * $pageSize ." , {$pageSize} ";

        $command = $connection->createCommand($sql1);
        $data = $command->queryAll();

        $listData = array() ;
        foreach ($data as $key => $v) {
            $_tmp = array();
            $_tmp['id'] = $v['id'];
            $_tmp['staffUid'] = $v['staffUid'];
            $_tmp['staffMaxCount'] = $v['staffMaxCount'];
            $_tmp['taskName'] = $v['taskName'];
            $_tmp['course_id'] = $v['course_id'];
            $_tmp['status'] = $v['status'];
            $_tmp['issueTime'] = $v['issueTime'];
            $_tmp['endTime'] = $v['endTime'];
            $_tmp['task_id'] = $v['task_id'];
            $listData[] = $_tmp ;
        }
        return ['total'=>$total , 'data'=>$listData];
    }

    /*
    * 获得会员sop考核
    */
    public function getSopListByWhere($page=1,$pageSize=10,$whereStr="",$order_by = '')
    {
        $sql = "SELECT count(1) as count FROM `task_menber_link` as tml LEFT JOIN `task_sop_link` as t on (t.taskId = tml.task_id and t.status != -1 ) where 1 {$whereStr}";

        $connection = \Yii::$app->db1;
        $command = $connection->createCommand($sql);
        $total = $command->queryOne();

        $sql1 = "SELECT tml.id , tml.staffUid , tml.staffMaxCount ,t.title,t.sopId,t.status,t.issueTime,t.endTime,tml.task_id FROM `task_menber_link` as tml LEFT JOIN `task_sop_link` as t on (t.id = tml.task_id and t.status != -1 ) where 1 {$whereStr} {$order_by} limit " .($page -1 ) * $pageSize ." , {$pageSize}";

        $command = $connection->createCommand($sql1);
        $data = $command->queryAll();

        $listData = array() ;
        foreach ($data as $key => $v) {
            $_tmp = array();

            $_tmp['id'] = $v['id'];
            $_tmp['staffUid'] = $v['staffUid'];
            $_tmp['staffMaxCount'] = $v['staffMaxCount'];
            $_tmp['title'] = $v['title'];
            $_tmp['status'] = $v['status'];
            $_tmp['issueTime'] = $v['issueTime'];
            $_tmp['endTime'] = $v['endTime'];
            $_tmp['task_id'] = $v['task_id'];
            $_tmp['sopId'] = $v['sopId'];

            $listData[] = $_tmp ;
        }
        return ['total'=>$total , 'data'=>$listData];
    }


}
