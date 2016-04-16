<?php

namespace api\models;

use Yii;

/**
 * This is the model class for table "praise_log".
 *
 * @property integer $id
 * @property integer $from_uid
 * @property integer $addTime
 * @property integer $target_id
 * @property integer $target_type
 */
class PraiseLog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'praise_log';
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
            [['from_uid', 'addTime', 'target_id', 'target_type'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('api', 'ID'),
            'from_uid' => Yii::t('api', '点赞用户id'),
            'addTime' => Yii::t('api', '点赞时间,时间戳'),
            'target_id' => Yii::t('api', '点赞对象id，类型按target_type'),
            'target_type' => Yii::t('api', '点赞对象类型，0--名医业界评价,1--交流评论类型点赞,2--视频资源点赞，3--课程资源点赞，4--sop资源点赞'),
        ];
    }
}
