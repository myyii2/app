<?php

namespace api\models;

use Yii;

/**
 * This is the model class for table "task".
 *
 * @property integer $taskId
 * @property string $taskName
 * @property integer $course_id
 * @property integer $type
 * @property integer $pass_rate
 * @property integer $asstype
 * @property string $scope
 * @property string $note
 * @property integer $requireNum
 * @property integer $actualNum
 * @property integer $passNum
 * @property string $add_uid
 * @property string $add_uname
 * @property integer $addTime
 * @property integer $issueTime
 * @property integer $endTime
 * @property integer $status
 * @property string $password
 * @property integer $hasPaper
 * @property integer $soplink_id
 * @property integer $from_deptId
 */
class Task extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'task';
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
            [['course_id', 'type', 'pass_rate', 'asstype', 'requireNum', 'actualNum', 'passNum', 'add_uid', 'addTime', 'issueTime', 'endTime', 'status', 'hasPaper', 'soplink_id', 'from_deptId'], 'integer'],
            [['scope', 'note'], 'required'],
            [['scope', 'note'], 'string'],
            [['taskName'], 'string', 'max' => 150],
            [['add_uname'], 'string', 'max' => 50],
            [['password'], 'string', 'max' => 20]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'taskId' => Yii::t('api', '计划任务id'),
            'taskName' => Yii::t('api', '任务名称'),
            'course_id' => Yii::t('api', '课程id'),
            'type' => Yii::t('api', '计划类型，0-面试考核 1-企业内训  2-考证 3-会员任务 4-sop类型任务'),
            'pass_rate' => Yii::t('api', '达标正确率'),
            'asstype' => Yii::t('api', '指派类型，0-按人 1-按部门'),
            'scope' => Yii::t('api', '计划范围'),
            'note' => Yii::t('api', '计划说明'),
            'requireNum' => Yii::t('api', '要求参与人数'),
            'actualNum' => Yii::t('api', '实际参与人数'),
            'passNum' => Yii::t('api', '通过考核人数'),
            'add_uid' => Yii::t('api', '发布者id'),
            'add_uname' => Yii::t('api', 'Add Uname'),
            'addTime' => Yii::t('api', '计划添加时间'),
            'issueTime' => Yii::t('api', '发布时间'),
            'endTime' => Yii::t('api', '计划截止日期'),
            'status' => Yii::t('api', '状态，3 系统发布中 0-未发布 1-已发布 2-已过期, -1已删除,4任务终止'),
            'password' => Yii::t('api', '发布任务账号初始密码'),
            'hasPaper' => Yii::t('api', '课程试题数量，0为没有课程试题'),
            'soplink_id' => Yii::t('api', 'sop任务关联表id，非sop任务为0'),
            'from_deptId' => Yii::t('api', '来源部门'),
        ];
    }
}
