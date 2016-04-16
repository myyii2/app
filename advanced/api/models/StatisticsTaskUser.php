<?php

namespace api\models;

use Yii;

/**
 * This is the model class for table "statistics_task_user".
 *
 * @property string $id
 * @property integer $taskId
 * @property integer $taskType
 * @property integer $uid
 * @property string $userName
 * @property integer $userId
 * @property integer $passed
 * @property double $userRate
 * @property integer $rank
 * @property integer $viewTime
 * @property integer $viewCount
 */
class StatisticsTaskUser extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'statistics_task_user';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db2');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['taskId', 'taskType', 'uid', 'userId', 'passed', 'rank', 'viewTime', 'viewCount'], 'integer'],
            [['userRate'], 'number'],
            [['userName'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('api', 'ID'),
            'taskId' => Yii::t('api', '考核id'),
            'taskType' => Yii::t('api', '1:教程考核 2:SOP考核'),
            'uid' => Yii::t('api', 'Uid'),
            'userName' => Yii::t('api', '参与者姓名'),
            'userId' => Yii::t('api', '参与者UID'),
            'passed' => Yii::t('api', '考核结果,是否通过'),
            'userRate' => Yii::t('api', '正确率'),
            'rank' => Yii::t('api', '排名'),
            'viewTime' => Yii::t('api', '考核总时长/观看视频中时长'),
            'viewCount' => Yii::t('api', '考核观看视频次数'),
        ];
    }
}
