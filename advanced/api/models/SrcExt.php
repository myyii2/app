<?php

namespace api\models;

use Yii;

/**
 * This is the model class for table "src_ext".
 *
 * @property integer $id
 * @property integer $type
 * @property integer $srcId
 * @property integer $ret
 */
class SrcExt extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'src_ext';
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
            [['type', 'srcId', 'ret'], 'integer'],
            [['srcId'], 'required']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('api', 'ID'),
            'type' => Yii::t('api', '1:视频,2:教程,3:sop'),
            'srcId' => Yii::t('api', 'Src ID'),
            'ret' => Yii::t('api', '0: 默认 ; 1:会员可看 ; 2:非登录可看'),
        ];
    }
}
