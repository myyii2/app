<?php

namespace api\models;

use Yii;

/**
 * This is the model class for table "task_sop_link".
 *
 * @property integer $id
 * @property integer $sopId
 * @property string $title
 * @property string $add_uid
 * @property string $taskId
 * @property integer $type
 * @property integer $requireNum
 * @property integer $actualNum
 * @property integer $status
 * @property integer $addTime
 * @property integer $issueTime
 * @property integer $endTime
 * @property string $extra
 * @property integer $from_deptId
 */
class TaskSopLink extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'task_sop_link';
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
            [['sopId', 'add_uid', 'type', 'requireNum', 'actualNum', 'status', 'addTime', 'issueTime', 'endTime', 'from_deptId'], 'integer'],
            [['extra'], 'string'],
            [['title', 'taskId'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('api', 'ID'),
            'sopId' => Yii::t('api', '发任务的sop模板名称'),
            'title' => Yii::t('api', '任务标题'),
            'add_uid' => Yii::t('api', '任务发布者用户id'),
            'taskId' => Yii::t('api', 'json数组字符串，保存任务id数组'),
            'type' => Yii::t('api', '计划类型，0-面试考核 1-内训  3-会员任务'),
            'requireNum' => Yii::t('api', '计划人数'),
            'actualNum' => Yii::t('api', '参与人数'),
            'status' => Yii::t('api', '状态，3 系统发布中 0-未发布 1-已发布 2-已过期 , -1 已删除,4任务终止'),
            'addTime' => Yii::t('api', '计划添加时间'),
            'issueTime' => Yii::t('api', '发布时间'),
            'endTime' => Yii::t('api', '计划截止日期'),
            'extra' => Yii::t('api', '额外任务信息'),
            'from_deptId' => Yii::t('api', '来源部门'),
        ];
    }
}
