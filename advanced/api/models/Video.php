<?php

namespace api\models;

use Yii;

/**
 * This is the model class for table "video".
 *
 * @property integer $videoId
 * @property integer $server_id
 * @property string $name
 * @property integer $uploadSize
 * @property integer $size
 * @property string $format
 * @property integer $duration
 * @property string $upload_uid
 * @property integer $uploadTime
 * @property integer $status
 * @property integer $videocover
 * @property integer $type
 * @property integer $sales
 * @property integer $click
 * @property integer $up
 * @property integer $commentTotal
 * @property integer $audit
 * @property integer $auditTime
 * @property string $reason
 * @property integer $auditUid
 */
class Video extends \yii\db\ActiveRecord
{
    private static $url;
    private static $server_id;
    private static $videoId;
    private static $upload_uid;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'video';
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
            [['server_id', 'uploadSize', 'size', 'duration', 'upload_uid', 'uploadTime', 'status', 'videocover', 'type', 'sales', 'click', 'up', 'commentTotal', 'audit', 'auditTime', 'auditUid'], 'integer'],
            [['click'], 'required'],
            [['name'], 'string', 'max' => 150],
            [['format'], 'string', 'max' => 20],
            [['reason'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'videoId' => Yii::t('api', '视频id'),
            'server_id' => Yii::t('api', '服务器id'),
            'name' => Yii::t('api', '视频名称'),
            'uploadSize' => Yii::t('api', '视频的转换前的大小，可用于计算用户空间占用情况,单位KB'),
            'size' => Yii::t('api', '视频大小，单位KB'),
            'format' => Yii::t('api', '视频格式'),
            'duration' => Yii::t('api', '视频时长，单位秒'),
            'upload_uid' => Yii::t('api', '上传者id'),
            'uploadTime' => Yii::t('api', '上传时间'),
            'status' => Yii::t('api', '状态，预留  1转换中2转换成功3转换中4转换失败'),
            'videocover' => Yii::t('api', '是否上传视频封面 ，--0，未上传，--1，已上传'),
            'type' => Yii::t('api', '0是FFMPEG,1是mencoder'),
            'sales' => Yii::t('api', '销量'),
            'click' => Yii::t('api', '播放次数'),
            'up' => Yii::t('api', '点赞'),
            'commentTotal' => Yii::t('api', '评论总数'),
            'audit' => Yii::t('api', '审核，默认0，未审，-1审核不通过，1审核通过'),
            'auditTime' => Yii::t('api', '审核时间'),
            'reason' => Yii::t('api', '拒绝的原因'),
            'auditUid' => Yii::t('api', '审核的Uid'),
        ];
    }

    public function getUrl($server_id,$videoId,$upload_uid){
        self::$url=($server_id && $videoId && $upload_uid)?\common\library\Common::get_video_url($upload_uid, $videoId,$server_id): null;
        return self::$url;
    }

    public function getMp4URL($server_id,$videoId,$upload_uid){
        return !empty(self::$url) ? \common\library\Common::get_video_url($upload_uid, $videoId,$server_id,\common\library\Common::RES_VIDEO_MP4) : "";
    }
    
    public function getVideoByIds($ids){
           
        $connection = \Yii::$app->db1;
        $sql = "select vul.id,vul.video_id,vul.title,vul.videocover,v.upload_uid from video_user_link as vul LEFT JOIN video as v on vul.video_id = v.videoId where id in (".$ids.")";
        $command = $connection->createCommand($sql);
        $videoList = $command->queryAll();
        return $videoList;    
    }
}
