<?php

namespace api\models;
use common\library\Common;

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
    
    public static function getRowById($courseId)
    {

        $connection = \Yii::$app->db1;
        $command = $connection->createCommand('SELECT courseId,deptId,course.name,server_id,hits,introduction,picList,srcCourseId,commentTotal,check_word,hasPaper,source,from_uid,addTime,viewers,sales,status,ext_permission,audit,int_permission,person_price,group_price,cate_id,cate_type,class_id,cateName,course_custom_sort.name as custom_name,parent_id FROM course left join course_category on course.cate_id=course_category.cateId left join course_selfmake on course.courseId=course_selfmake.course_id left join course_custom_sort on course_selfmake.sort_id=course_custom_sort.sortId where course.courseId ='.$courseId);
        $posts = $command->queryOne();
        if(empty($posts)){ return 0; }

        $posts['parentCate']['cateId'] = 0;
        $posts['parentCate']['cateName'] = '';
        if(!empty($posts['parent_id'])){
            $command = $connection->createCommand('SELECT * from course_category where cateId = '.$posts['parent_id']);
            $cateList = $command->queryOne();
            $posts['parentCate']['cateId'] = $cateList['cateId'];
            $posts['parentCate']['cateName'] = $cateList['cateName'];
        }

        $command = $connection->createCommand('SELECT chapter.chapterId,chapter.listOrder,chapter.try,chapter.name,chapter.video_id from course,course_chapter_link as ccl,chapter where course.courseId = ccl.course_id and ccl.chapter_id = chapter.chapterId and course.courseId ='.$courseId);
        $chapterList = $command->queryAll();


        $chapterArr = array_map(function($element){return $element['chapterId'];},$chapterList);
        $videoArr = array_map(function($element){return $element['video_id'];},$chapterList);
        $newarr = implode(",", $videoArr);
        $newsarr = implode(",", $chapterArr);

        $posts['selfmakeSortName']['name'] = $posts['custom_name'];

        unset($posts['parent_id']);
        unset($posts['custom_name']);
        if(!empty($posts['chapters'])) {
            Common::array_sort($posts['chapters'], 'listOrder', 'asc');
        }

        $sql = 'SELECT vul.ext_permission,vul.id,vul.video_id,vul.title as title,v.size,v.format,v.duration,v.upload_uid,vul.type,v.audit,v.server_id from video_user_link as vul left join video as v on v.videoId=vul.video_id where vul.id in ('.$newarr.')';
        $command = $connection->createCommand($sql);
        $videoList = $command->queryAll();
        $durationList = array_map(function($element){return $element['duration'];},$videoList);
        $durationCount = date('H:i:s',array_sum($durationList));
        $videoList = array_reduce($videoList, create_function('$v,$w', '$v[$w["id"]]=$w;return $v;'));

        $sqls = 'SELECT tp.paperId,tp.chapter_id,tp.title,tp.timeSpot,tp.count,tp.finishTime,tp.paperId from testpaper as tp where tp.chapter_id in ('.$newsarr.')';
        $command = $connection->createCommand($sqls);
        $paperList = $command->queryAll();
        $paperIdArr = array_map(function($element){return $element['paperId'];},$paperList);
        $paperIdArr = implode(",", $paperIdArr);
        $paperList = array_reduce($paperList, create_function('$v,$w', '$v[$w["chapter_id"]][]=$w;return $v;'));

        $sqlz = 'SELECT tpi.itemId,tpi.title as papertitle,tpi.paper_id,tpi.type,tpi.option,tpi.result,tpi.listOrder from testpaper_item as tpi where tpi.paper_id in ('.$paperIdArr.')';
        $command = $connection->createCommand($sqlz);
        $paperDetail = $command->queryAll();

        foreach($paperDetail as $key=>$val){
            //$paperDetail[$key]['option'];
            $res = unserialize($val['option']);
            if($val['type'] == 'RadioButton'){
                $paperDetail[$key]['type'] = '单选';
            }else{
                $paperDetail[$key]['type'] = '多选';
            }

            foreach($res as $k=>$v){
                $vals = (array)$v;
                $rez[$k]['index'] = $vals['index'];
                $rez[$k]['content'] = $vals['content'];
                $rez[$k]['isAnswer'] = $vals['isAnswer'];

            }
            $paperDetail[$key]['option'] = $rez;
        }
        $paperDeArr = array_reduce($paperDetail, create_function('$v,$w', '$v[$w["paper_id"]][]=$w;return $v;'));

        foreach($paperList as $key=>$val){
            foreach($val as $k=>$v) {
                $paperList[$key][$k]['item'] = $paperDeArr[$v['paperId']];
            }
        }

        foreach($chapterList as $key=>$val){
            $video_id = $val['video_id'];
            $chapterId = $val['chapterId'];
            $chapterList[$key]['papers'] = $paperList[$chapterId];
            $chapterList[$key]['video'] = $videoList[$video_id];
        }
        $posts['duration'] = $durationCount;
        $posts['chapters'] = $chapterList;
        return $posts;
    }

    public static function addHits($courseId)
    {
        $condition['courseId'] = $courseId;
        $site=self::findOne($courseId);
        $site->hits +=1;
        $res = $site->save();
        return $res;
    }

}
