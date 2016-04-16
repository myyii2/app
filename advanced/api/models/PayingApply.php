<?php

namespace api\models;

use Yii;

/**
 * This is the model class for table "paying_apply".
 *
 * @property integer $id
 * @property integer $payingGoodsId
 * @property integer $uid
 * @property string $username
 * @property integer $createTime
 * @property string $remark
 */
class PayingApply extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'paying_apply';
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
            [['payingGoodsId', 'username', 'createTime'], 'required'],
            [['payingGoodsId', 'uid', 'createTime'], 'integer'],
            [['username'], 'string', 'max' => 10],
            [['remark'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('api', 'ID'),
            'payingGoodsId' => Yii::t('api', 'payingGoods中的ID'),
            'uid' => Yii::t('api', '申请人的uid'),
            'username' => Yii::t('api', '员工姓名'),
            'createTime' => Yii::t('api', '申请时间'),
            'remark' => Yii::t('api', '申请理由'),
        ];
    }
}
