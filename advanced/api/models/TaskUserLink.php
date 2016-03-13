<?php

namespace api\models;

use Yii;

/**
 * This is the model class for table "task_user_link".
 *
 * @property integer $id
 * @property string $user_id
 * @property integer $task_id
 * @property integer $status
 * @property double $rate
 * @property double $userRate
 * @property integer $passed
 * @property integer $bestVersion
 * @property integer $responseDay
 * @property integer $correctNum
 * @property integer $totalNum
 */
class TaskUserLink extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'task_user_link';
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
            [['user_id', 'task_id', 'status', 'passed', 'bestVersion', 'responseDay', 'correctNum', 'totalNum'], 'integer'],
            [['rate', 'userRate'], 'number'],
            [['user_id', 'task_id'], 'unique', 'targetAttribute' => ['user_id', 'task_id'], 'message' => 'The combination of 用户id and 计划任务id has already been taken.']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('api', 'ID'),
            'user_id' => Yii::t('api', '用户id'),
            'task_id' => Yii::t('api', '计划任务id'),
            'status' => Yii::t('api', '考试状态，0-未参加 1参加中  2-已参加 3-已过期 -1-已删除,4任务终止'),
            'rate' => Yii::t('api', '完成比例'),
            'userRate' => Yii::t('api', '用户任务的整体正确率'),
            'passed' => Yii::t('api', '是否通过，0-未通过 1-已通过'),
            'bestVersion' => Yii::t('api', '多次考核的最优成绩版本号'),
            'responseDay' => Yii::t('api', '任务响应间隔，即收到任务后第几天开始考核，0为未开始考核，1为考核第一天就开始考核，以此类推'),
            'correctNum' => Yii::t('api', '作答正确题目数'),
            'totalNum' => Yii::t('api', '考核总题目数量'),
        ];
    }

    public static function getTulList($cons){

        $connection = \Yii::$app->db1;
        $sqls = 'SELECT task.course_id,task.status FROM task_user_link as tul LEFT JOIN task on tul.task_id = task.taskId where tul.user_id = '.$cons['user_id'].' and tul.task_id = '.$cons['task_id'];
        $command = $connection->createCommand($sqls);
        $tulList = $command->queryAll();
        return $tulList;
    }
}
