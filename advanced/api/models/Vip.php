<?php

namespace api\models;

use Yii;

/**
 * This is the model class for table "vip".
 *
 * @property integer $id
 * @property integer $uid
 * @property integer $cat_id
 * @property integer $start_time
 * @property integer $end_time
 * @property integer $update_time
 * @property string $note
 */
class Vip extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vip';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uid', 'cat_id', 'start_time', 'end_time', 'update_time'], 'integer'],
            [['cat_id', 'note'], 'required'],
            [['note'], 'string', 'max' => 1]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('api', 'ID'),
            'uid' => Yii::t('api', '用户ID'),
            'cat_id' => Yii::t('api', '栏目id'),
            'start_time' => Yii::t('api', '开始时间'),
            'end_time' => Yii::t('api', '结束时间'),
            'update_time' => Yii::t('api', 'Update Time'),
            'note' => Yii::t('api', 'Note'),
        ];
    }

    public static function getCountByUid($uid,$type,$catid){

        $time = time();
        $connection = Yii::$app->db;
        if(in_array('a', $type)&&in_array('b', $type)){

            $sql1 = "SELECT count(*) as count FROM `vip` where `end_time`  >= {$time} and  `uid` = {$uid}  and `note` = 'b'   ";
            $sql2 ="SELECT count(*) as count FROM `vip` where `end_time`  >= {$time} and  `uid` = {$uid}  and `note` = 'a' and `cat_id` = '{$catid}' ";

            $command = $connection->createCommand($sql1);
            $mcList1 = $command->queryOne();


            $command = $connection->createCommand($sql2);
            $mcList2 = $command->queryOne();

            if( $mcList1['count'] > 0){
                $mcList = $mcList1;
            }else{
                $mcList = $mcList2;
            }

        }else{
            $type = $type[0];
            if($type!='a'){
                $sql = "SELECT count(*) as count FROM `vip` where `end_time`  >= {$time} and  `uid` = {$uid}  and `note` = '{$type}'  ";

            }else{
                $sql ="SELECT count(*) as count FROM `vip` where `end_time`  >= {$time} and  `uid` = {$uid}  and `note` = 'a' and `cat_id` = '{$catid}'";
            }
            $command = $connection->createCommand($sql);
            $mcList = $command->queryOne();
        }

        return $mcList;
    }
}
