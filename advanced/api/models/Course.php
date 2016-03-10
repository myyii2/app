<?php

namespace api\models;

use Yii;

/**
 * This is the model class for table "course".
 *
 * @property integer $courseId
 * @property string $name
 * @property integer $cate_id
 * @property integer $class_id
 * @property integer $cate_type
 * @property string $introduction
 * @property integer $source
 * @property string $from_uid
 * @property integer $addTime
 * @property integer $server_id
 * @property integer $hits
 * @property integer $viewers
 * @property integer $sales
 * @property integer $status
 * @property integer $hasPaper
 * @property integer $up
 * @property integer $ext_permission
 * @property integer $int_permission
 * @property double $person_price
 * @property double $group_price
 * @property string $check_word
 * @property integer $srcCourseId
 * @property integer $commentTotal
 * @property integer $audit
 * @property string $reason
 * @property integer $auditUid
 * @property integer $auditTime
 * @property integer $vip_start
 * @property integer $vip_end
 * @property integer $orderId
 * @property integer $videoDuration
 * @property integer $copyright
 * @property integer $authorUid
 * @property integer $deptId
 * @property string $picList
 */
class Course extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'course';
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
            [['cate_id', 'class_id', 'cate_type', 'source', 'from_uid', 'addTime', 'server_id', 'hits', 'viewers', 'sales', 'status', 'hasPaper', 'up', 'ext_permission', 'int_permission', 'srcCourseId', 'commentTotal', 'audit', 'auditUid', 'auditTime', 'vip_start', 'vip_end', 'orderId', 'videoDuration', 'copyright', 'authorUid', 'deptId'], 'integer'],
            [['introduction'], 'required'],
            [['introduction'], 'string'],
            [['person_price', 'group_price'], 'number'],
            [['name'], 'string', 'max' => 100],
            [['check_word', 'reason', 'picList'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'courseId' => Yii::t('api', '教程id'),
            'name' => Yii::t('api', '名称'),
            'cate_id' => Yii::t('api', '课程分类'),
            'class_id' => Yii::t('api', '难度级别'),
            'cate_type' => Yii::t('api', '中西医'),
            'introduction' => Yii::t('api', '教程简介'),
            'source' => Yii::t('api', '教程来源，0-自制，1-购买'),
            'from_uid' => Yii::t('api', '教程所属用户id'),
            'addTime' => Yii::t('api', '添加时间'),
            'server_id' => Yii::t('api', '教程缩略图服务器id'),
            'hits' => Yii::t('api', '人气（点击查看介绍次数）'),
            'viewers' => Yii::t('api', '学习人数（观看人数）'),
            'sales' => Yii::t('api', '销量'),
            'status' => Yii::t('api', '状态，1-开启 0-关闭,-1-不可用的课程'),
            'hasPaper' => Yii::t('api', '试题数量'),
            'up' => Yii::t('api', '点赞'),
            'ext_permission' => Yii::t('api', '外部权限,0--外部不公开,1--外部公开,2--外部验证可看,3--外部付费可看'),
            'int_permission' => Yii::t('api', '内部权限,0--内部不公开,1--内部公开'),
            'person_price' => Yii::t('api', '个人付费价格'),
            'group_price' => Yii::t('api', '团体付费价格'),
            'check_word' => Yii::t('api', '外部验证文字'),
            'srcCourseId' => Yii::t('api', '来源课程cid，0为原始课程'),
            'commentTotal' => Yii::t('api', '评论总数'),
            'audit' => Yii::t('api', '审核，默认0，未审，-1审核不通过，1审核通过'),
            'reason' => Yii::t('api', '审核不通过的理由'),
            'auditUid' => Yii::t('api', '审核者的ID'),
            'auditTime' => Yii::t('api', '审核时间'),
            'vip_start' => Yii::t('api', 'vip 购买时效'),
            'vip_end' => Yii::t('api', 'vip 购买时效'),
            'orderId' => Yii::t('api', '订单Id'),
            'videoDuration' => Yii::t('api', '视频时长,单位：秒'),
            'copyright' => Yii::t('api', 'Copyright'),
            'authorUid' => Yii::t('api', '资源作者用户id，0--默认等同上传者'),
            'deptId' => Yii::t('api', '来源部门'),
            'picList' => Yii::t('api', '封面轮播图数组，json格式'),
        ];
    }
    
    public static function getRowById($courseId,$field)
    {
        $res = self::find(
                    array(
                        'select'=>array('courseId'),
                        'condition' => 'courseId=:courseid',
                        'params' => array(':courseid'=>$courseId),
                      ));
        $sql = self::find(
                    array(
                        'select'=>array('courseId'),
                        'condition' => 'courseId=:courseid',
                        'params' => array(':courseid'=>$courseId),
                      ))->createCommand()->getRawSql();
        echo $sql;exit;
        return $res;
    }
}
