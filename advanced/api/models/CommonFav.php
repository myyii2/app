<?php

namespace api\models;

use Yii;

/**
 * This is the model class for table "common_fav".
 *
 * @property integer $id
 * @property integer $uid
 * @property integer $data_id
 * @property string $data_type
 * @property integer $inputtime
 */
class CommonFav extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'common_fav';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uid', 'data_id', 'inputtime'], 'integer'],
            [['inputtime'], 'required'],
            [['data_type'], 'string', 'max' => 10]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('api', 'ID'),
            'uid' => Yii::t('api', '收藏者ID'),
            'data_id' => Yii::t('api', '资源的ID'),
            'data_type' => Yii::t('api', 'video-视频   course-课程  sop-sop'),
            'inputtime' => Yii::t('api', '收藏时间'),
        ];
    }
}
