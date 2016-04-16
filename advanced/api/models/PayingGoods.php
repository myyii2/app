<?php

namespace api\models;

use Yii;

/**
 * This is the model class for table "paying_goods".
 *
 * @property integer $id
 * @property integer $goodsType
 * @property integer $goodsId
 * @property string $goodsName
 * @property string $price
 * @property integer $replyUid
 * @property integer $createTime
 * @property integer $updateTime
 * @property integer $replyTime
 * @property integer $status
 * @property string $refuseReason
 * @property string $latestStaff
 * @property integer $totalApply
 */
class PayingGoods extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'paying_goods';
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
            [['goodsType', 'goodsId', 'replyUid', 'createTime', 'updateTime', 'replyTime', 'status', 'totalApply'], 'integer'],
            [['goodsId', 'price', 'replyUid'], 'required'],
            [['price'], 'number'],
            [['goodsName', 'refuseReason'], 'string', 'max' => 255],
            [['latestStaff'], 'string', 'max' => 10]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('api', 'ID'),
            'goodsType' => Yii::t('api', '商品类型，0--课程订单，1--视频订单，2--sop订单'),
            'goodsId' => Yii::t('api', '商品ID'),
            'goodsName' => Yii::t('api', '商品名称，用于搜索'),
            'price' => Yii::t('api', 'Price'),
            'replyUid' => Yii::t('api', '员工所属的企业、机构ID'),
            'createTime' => Yii::t('api', '首次添加的时间'),
            'updateTime' => Yii::t('api', '最新提交申请的时间'),
            'replyTime' => Yii::t('api', '企业、机构审核时间'),
            'status' => Yii::t('api', '处理状态，0--未处理，1--已付款，2--拒绝付款'),
            'refuseReason' => Yii::t('api', '拒绝的理由'),
            'latestStaff' => Yii::t('api', '最新提交请求的员工姓名'),
            'totalApply' => Yii::t('api', '申请的总人数'),
        ];
    }
}
