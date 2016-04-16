<?php

namespace api\models;

use Yii;

/**
 * This is the model class for table "department".
 *
 * @property integer $deptId
 * @property string $deptName
 * @property integer $parent_id
 * @property integer $enterprise_uid
 * @property integer $listOrder
 * @property integer $isTag
 */
class Department extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'department';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['parent_id', 'enterprise_uid', 'listOrder', 'isTag'], 'integer'],
            [['enterprise_uid'], 'required'],
            [['deptName'], 'string', 'max' => 100]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'deptId' => Yii::t('api', '部分id'),
            'deptName' => Yii::t('api', '部门/岗位名称'),
            'parent_id' => Yii::t('api', '上级部门id'),
            'enterprise_uid' => Yii::t('api', '所属企业用户id'),
            'listOrder' => Yii::t('api', '部门排序索引'),
            'isTag' => Yii::t('api', '是否为岗位标签专用'),
        ];
    }
    
     public static function getDepInfo($pid=0,$resultArrs=array(),$enterprise_uid){
          
          $key = 'deptTrees_'.md5($pid.$enterprise_uid);
          $dataInfo = Yii::$app->redis->get($key);
          if (empty($dataInfo)) {
                $dataInfo = static::getDepTrees($pid,$resultArrs,$enterprise_uid);
                $resList = serialize($dataInfo);
                Yii::$app->redis->set($key,$resList);
          }else{
                $dataInfo = unserialize($dataInfo);
          }
          
          $deptArr = array_map(function($element){return $element['deptId'];},$dataInfo);
          array_push($deptArr,$pid);
          return $deptArr;
          
     }
    
        public static function getDepTrees($pid,$resultArrs,$enterprise_uid){
           
             $conz['parent_id'] = $pid;
             $conz['enterprise_uid'] =$enterprise_uid;
             $conz['isTag'] = 0;
             $resultArr = self::find()->where($conz)->asArray()->all();
       
             foreach($resultArr as $key=>$val){
                    $resultArr = self::getDepTrees($val['deptId'],$resultArr,$enterprise_uid);
             }
             $resultArr = array_merge($resultArr,$resultArrs);
             return $resultArr;
            
        }
}
