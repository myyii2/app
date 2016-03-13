<?php

namespace api\models;

use Yii;

/**
 * This is the model class for table "task_join".
 *
 * @property integer $recid
 * @property integer $task_id
 * @property integer $course_id
 * @property string $user_id
 * @property integer $chapter_id
 * @property string $result
 * @property integer $counts
 * @property integer $corrects
 * @property double $accuracy
 * @property integer $startTime
 * @property integer $joinTime
 * @property integer $passed
 * @property string $timeUsed
 * @property integer $versionCode
 * @property integer $device
 */
class TaskJoin extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'task_join';
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
            [['task_id', 'result'], 'required'],
            [['task_id', 'course_id', 'user_id', 'chapter_id', 'counts', 'corrects', 'startTime', 'joinTime', 'passed', 'versionCode', 'device'], 'integer'],
            [['result'], 'string'],
            [['accuracy'], 'number'],
            [['timeUsed'], 'string', 'max' => 200]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'recid' => Yii::t('api', '自增id'),
            'task_id' => Yii::t('api', '计划任务id'),
            'course_id' => Yii::t('api', '教程ID'),
            'user_id' => Yii::t('api', '参与者用户id'),
            'chapter_id' => Yii::t('api', '章节id'),
            'result' => Yii::t('api', '用户所填答案，格式为章节id:::试卷ID::题目ID:答案#'),
            'counts' => Yii::t('api', '题目数'),
            'corrects' => Yii::t('api', '正确数'),
            'accuracy' => Yii::t('api', '正确率'),
            'startTime' => Yii::t('api', '记录开始的时间'),
            'joinTime' => Yii::t('api', '答题时间'),
            'passed' => Yii::t('api', '是否通过，0-未通过 1-已通过'),
            'timeUsed' => Yii::t('api', '用时'),
            'versionCode' => Yii::t('api', '多次考试区分版本号'),
            'device' => Yii::t('api', '终端类型'),
        ];
    }


    /**
     * 根据用户id和任务id获取当前考核的章节id及可选的章节范围
     * @param int $taskId       考核任务id
     * @param int $userId       考核用户id
     * @return mixed     curChapter 用户的当前系统匹配考核章节id，   optional 用户的任务章节可跳转章节索引
     */
    public static function getTaskNextChapter($taskId = 0,$userId = 0) {

        $retData = 0;
        $optionalIndex = 0;     //可选跳转章节索引

        $condition['user_id'] = $userId;
        $condition['task_id'] = $taskId;
        $res = TaskUserLink::find()->where($condition)->count();

        if (empty($res)) {      //没有收到任务
            $retData = 0;
            $optionalIndex = -1;     //无可选跳转章节
        } else {

            /**
             * 查询未学的章节id或首个章节id
             * 获取所学课程的所有章节
             */

            $connection = \Yii::$app->db1;
            $chapList = $connection->createCommand("SELECT ccl.chapter_id from task as t LEFT JOIN course_chapter_link as ccl on t.course_id = ccl.course_id left JOIN chapter as c on ccl.chapter_id = c.chapterId where t.taskId = ".$taskId." ORDER BY c.listOrder ASC")->queryAll();


            //获取已通过考核的章节
            $hadStudyList = $connection->createCommand("SELECT tjr.chapter_id from task_join_record as tjr LEFT JOIN task_join as tj on tjr.recid = tj.recid where tj.passed = 1 and tj.user_id = ".$userId." and tj.task_id = ".$taskId)->queryAll();

            $hadStudyArr = array();
            foreach ($hadStudyList as $listOne) {
                array_push($hadStudyArr,$listOne['chapter_id']);
            }

            $chapArr = array();
            foreach ($chapList as $key => $chapOne) {
                array_push($chapArr,$chapOne['chapter_id']);
                $optionalIndex = $key;
                $retData = $listOne['chapter_id'];
                if (!in_array($listOne['chapter_id'],$hadStudyArr)) {
                    break;
                }
            }

            define("STATUS_STUDY_NOT",0);       //考核状态，未考
            define("STATUS_STUDY_PASS",2);      //考核状态，未通过
            define("STATUS_STUDY_UNPASS",1);    //考核状态，已通过
            $study_status = STATUS_STUDY_NOT;
            //查看已学到章节的考核情况
            if (!empty($retData)) {
                $dataObj = $connection->createCommand("SELECT passed from task_join where task_id = ".$taskId." and chapter_id = ".$retData." and user_id = ".$userId." ORDER BY accuracy desc limit 1")->queryOne();

                if (empty($dataObj)) {
                    $study_status = STATUS_STUDY_NOT;
                } elseif ($dataObj['passed'] == 0) {
                    $study_status = STATUS_STUDY_UNPASS;
                } elseif ($dataObj['passed'] == 1) {
                    $study_status = STATUS_STUDY_PASS;
                }
            }
        }

        $res = array(
            "curChapter" => $retData,
            "optional" => $optionalIndex,
            "curChapterStatus" => $dataObj['passed']
        );
        return $res;
    }
}
