<?php
namespace api\controllers;

use Yii;
use yii\web\Controller;
use app\models\User;
use api\models\UserStaff;
use api\models\Vip;
use api\models\Task;
use api\models\Course;
use api\models\CommonFav;
use api\models\SrcExt;
use api\models\TaskUserLink;
use api\models\TaskJoin;
use api\models\PayingGoods;
use api\models\PayingApply;
use common\library\Redis;
use common\library\Common;



class SiteController extends Controller
{

    public $enterpriseId = 0;
    public $staffAdminInfo = array();



    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    public function actionIndex()
    {

        $posts = Course::getRowById();
        var_dump($posts);exit;
    }

    public function actionDetail(){

        $request = Yii::$app->getRequest();
        $page  =$request->get('page') ? $request->get('page') : 1;
        $pageSize = $request->get('pageSize') ? $request->get('pageSize') : common::PAGE_SIZE;
        $userId   = (int)$request->get('userId');
        $userType = $request->get('userType');
        $action   = $request->get('action');
        $staffAdmin   = $request->get("staffAdmin");
        $agroup_id   = $request->get("agroup_id");
        $id = $request->get('Id');

        //员工，找企业ID
        if ($userType == common::USER_STAFF) {
            $cone['uid'] = $userId;
            $staffData=UserStaff::find()->where($cone)->select(['enterprise_id', 'dept_id'])->asArray()->one();

            if (!empty($staffData)){
                $enterpriseId = $staffData['enterprise_id'];
                $staffAdminInfo = $staffData;
            }else{
                outputJson('查询不到所属企业信息');
            }
        }


        $userId = $request->get('userId');
        $courseId = $request->get('id');
        $task_id = $request->get('task_id');

        $condition['taskId'] = $task_id;
        $myFeilds = array('taskName','pass_rate','endTime','taskId','add_uname');
        $returnData = Task::find()->select($myFeilds)->where($condition)->all();

        $retval['data']['taskId'] =!empty($returnData['data'][0]['taskId'])?$returnData['data'][0]['taskId']:'';
        $retval['data']['taskName'] =!empty($returnData['data'][0]['taskName'])?$returnData['data'][0]['taskName']:'';
        $retval['data']['passRate'] = !empty($returnData['data'][0]['pass_rate'])?$returnData['data'][0]['pass_rate']:'';
        $retval['data']['endTime'] = !empty($returnData['data'][0]['endTime'])?$returnData['data'][0]['endTime']:'';
        $retval['data']['enterpriseName'] = !empty($returnData['data'][0]['add_uname'])?$returnData['data'][0]['add_uname']:'';

        $course = Yii::$app->redis->get('course_'.$courseId);
        $course = unserialize($course);
        if(empty($course['courseId'])){
            $course = Course::getRowById($courseId);
            $myres = serialize($course);
            Yii::$app->redis->set('course_'.$courseId,$myres);
        }

        //判断courseId是否存在
        $res = $course['courseId'];
        if ($res === null) {
            outputJson('课程不存在');
        }

        $picList = json_decode($course['picList'],1);
        if(!empty($picList)){
            foreach($picList as $key=>$val){
                $retval['data']['picList'][] = Rule::get_photo_url($val, "unifyUpload");
            }
            $retval['data']['interval_time'] = 2000;
        }else{
            $retval['data']['picList'][] = '';
            $retval['data']['interval_time'] = 0;
        }

        $course['hasItem'] = $course['hasPaper'];
        if ($course['status']) { //筛选已经被审核且当前用户未记录点击数
            Course::addHits($courseId);
        }



        //购买的，查原作者
        $fromUserName = '';
        if (!empty($course['source'])) {
            if (!empty($course['srcCourseId'])) {
                $oneCourse=Course::findOne($course['srcCourseId']);
                $fromUid = $oneCourse->from_uid;
            } else {
                //查不到来源的
                $course['source'] = 0;
            }
        }

        //游客，加上来源
        $fromUid = $course['from_uid'];
        if (!empty($fromUid)) {
            $site=User::find(18)->select(['realname', 'email'])->asArray()->one();
            $fromUserName = !empty($site['realname']) ? $site['realname'] : $site['email'];
        }

        //判断是否收藏
        $con['uid'] = $userId;
        $con['data_id'] = $courseId;
        $con['data_type'] = 'course';
        $comFavCount = CommonFav::find()->where($con)->count();

        if($comFavCount > 0){
            $retval['data']['is_collect']   = 1;
        }else{
            $retval['data']['is_collect']   = 0;
        }

        $retval['data']['comment_total'] = intval($course['commentTotal']);
        $retval['data']['author'] = $fromUserName;
        $retval['data']['click']  = intval($course['viewers']);
        $retval['data']['category'] = !empty($course['cateName']) ? $course['cateName']:'未分类';

        //查询资源额外权限
        $conz['type'] = 2; //常量定义
        $conz['srcId'] = $courseId;
        $src_ext_data = SrcExt::find()->where($conz)->asArray()->one();
        $src_ret = (int)$src_ext_data['ret'];//额外限制权限，默认为0--无额外权限

        //默认，不需要申请购买
        $retval['data']['buy'] = 0;
        //额外权限，默认为没有额外权限限制
        $retval['data']['extra_permission'] = 0;
        //是否已获得套餐权限，默认为没有获得
        $retval['data']['isMember'] = 0;
        $retval['data']['memberType'] = array();    //返回套餐类型数组
        $retval['data']['vipTips'] = "";    //会员制套餐导购提示信息

        if (!empty($task_id)) {     //判断任务权限,任务情况
            $cons['user_id'] = $userId;
            $cons['task_id'] = $task_id;
            $retData = TaskUserLink::getTulList($cons);
            $retData = $retData[0];

            //empty($tulData) && outputJson("无相关任务信息");  修复

            if ($retData['course_id'] != $courseId) {
                outputJson("课程信息与任务信息不匹配");
            }

            //考核状态判断
            if ($retData['status'] != 1) {
                outputJson("任务已过期");
            }

            //获取考核章节索引
            $retData = TaskJoin::getTaskNextChapter($task_id,$userId);
            $nextIndex = $retData['optional'];
            $curChapterStatus = $retData['curChapterStatus'];

        } else {

            $src_ret = 1;
            $retval['data']['extra_permission'] = $src_ret;
            define("SRC_RET_MEMBER",1);
            if (SRC_RET_MEMBER == $src_ret) {
                //查询是否已经购买会员
                $mret = Common::getVipCate($course['cate_id'],$course['class_id']);

                $retval['data']['memberType'] = $mret;
                if($mret){
                    $checkUid = !empty($enterpriseId) ? $enterpriseId : $userId;
                    $tempData = Vip::getCountByUid($checkUid,$mret,$course['cate_id']);

                    if($tempData['count'] > 0){
                        //如果已经购买了会员
                        $retval['data']['isMember'] = 1;
                    } else {
                        $retval['data']['vipTips'] = Common::getVipTips($mret,$course['cate_id']);
                    }
                }
            }

            //    判断对内权限
            if ($course['from_uid'] == $enterpriseId && $course['int_permission'] == 0) {
                outputJson('此教程对内不公开');
            }

            $course['ext_permission']=3;
            $userType=5;
            //要购买的教程
            if ($course['from_uid'] != $userId && $course['ext_permission'] == 3) {
                // 判断员工所在公司有没有已经购买
                if (!empty($enterpriseId) && $course['from_uid'] != $enterpriseId) {

                    //要申请购买
                    $retval['data']['buy'] = 1;
                    //是否已申请购买
                    $buyWhere = array('goodsType' => 0, 'goodsId' => 1256, 'replyUid' => $enterpriseId);
                    $list = PayingGoods::find()->where($buyWhere)->orderBy('id desc')->asArray()->all();

                    if (!empty($list)) { $list[0]['status'] = 0;
                        if ($list[0]['status'] == 1) {
                            $retval['buy'] = 3;           //已买

                        } elseif ($list[0]['status'] == 0) {

                            $cond = array('payingGoodsId' => $list[0]['id'], 'uid' => $userId);
                            $total = PayingApply::find()->where($cond)->count();
                            if (!empty($total)) {
                                $retval['data']['buy'] = 2;
                            }
                        }
                    }

                } elseif ($userType != common::USER_STAFF) {

                    $where = array('from_uid' => $userId, 'srcCourseId' => $courseId);
                    $courseTotal = Course::find()->where($where)->createCommand()->getRawSql();
                    //没买，需要购买
                    if (empty($courseTotal)){
                        $retval['data']['buy'] = 1;
                    } else {
                        $retval['data']['extra_permission'] = 0;
                    }
                }
            }
        }
exit;
        $retval['data']['id']           = $courseId;
        $retval['data']['name']         = $course->name;
        $retval['data']['image']        = prefixImage($course->getImageUrl(1));
        $retval['data']['rank']        = 2;
        $retval['data']['mediaType']     = 'course';
        $retval['data']['duration']     = $course->getCourseDuration();
        $retval['data']['introduction'] = $course->introduction;
        $retval['data']['source']       = $course->source;
        $retval['data']['group_price']  = $course->group_price;
        $retval['data']['person_price'] = $course->person_price;
        $retval['data']['source']       = $course->source;
        $retval['data']['uid']          = $course->from_uid;
        $retval['data']['ext_permission'] = $course->ext_permission;
        $retval['data']['check_word']     = $course->check_word;
        $retval['data']['int_permission'] = $course->int_permission;
        $retval['data']['comment_type'] = 3;
        $retval['data']['comment_cid']  = $courseId;
        $retval['data']['buy_cid']      = $courseId;
        $retval['data']['link_url']      = Toolkit::dealPlayerUrl($courseId,common::TYPE_COURSE);
        $retval['data']['nextIndex']      = (int)$nextIndex;


        $courseObj->getRowById($courseId);
        $courseInfo = $courseObj->getRawDataFromBack();
        $retval['data']['up'] = $courseInfo['data']['up'];

        $retval['data']['isPraise'] = 0;    //是否已点赞,0--未点赞，1--已点赞
        //是否点赞的状态输出
        if (!empty($userId)) {
            $PlObj = new module\Log\controller\PraiseLog();
            $PlObj->getList(0,0,array("from_uid" => $userId,"target_id" => $courseId,
                "target_type" => common::PRAISE_TYPE_COURSE));
            $retData = $PlObj->getRawDataFromBack();
            if (!empty($retData['data'])) {
                $retval['data']['isPraise'] = 1;    //已点赞
            }
        }

        //判断章节是否通过
        $conditionz['task_id'] = 2447;//$task_id;
        $conditionz['user_id'] = 91;//$userId;

        //$conditionz['chapter_id'] = $chapterIds?$chapterIds:array('29714','29716','29717');
        $relationStatus = getUserFinishChapter($conditionz);

        $chapters = $course->getChapters();
        $retval['data']['chapters'] = array();
        if (is_array($chapters)) {
            foreach ($chapters as $k => $v) {
                $chapter['name']     = $v->name;
                $chapter['id']       = $v->chapterId;
                $chapter['mp4']      = $v->getVideo()->getURL();
                $chapter['m3u8']   = $v->getVideo()->getURL();
                $chapter['mp4Url']   = $v->getVideo()->getMp4URL();
                $chapter['duration'] = $v->getVideo()->duration_his;
                $chapter['video_id']       = $v->getVideo()->videoId;
                $chapter['paperCount'] = count($v->getPapers());

                if(isset($nextIndex) && isset($curChapterStatus)){
                    if($k<$nextIndex){
                        $chapter['cando'] = 1;
                        $chapter['passStatus'] = 1;
                    }elseif($k==$nextIndex){
                        $chapter['cando'] = 1;
                        if($curChapterStatus == 1){
                            $chapter['passStatus'] = 1;
                        }else{
                            $chapter['passStatus'] = 0;
                        }
                    }else{
                        $chapter['cando'] = 0;
                        $chapter['passStatus'] = 0;
                    }
                }else{
                    $chapter['cando'] = 0;
                    $chapter['passStatus'] = 0;
                }

                $lastStatus = !empty($chapter['passStatus'])?$chapter['passStatus']:0;
                $chapter['papers'] = getPaperArray($v);
                if(!empty($chapter['papers'])){

                    foreach ($chapter['papers'] as $key1 => $papers) {
                        if(empty($papers['items'])){
                            unset( $chapter['papers'][$key1]);
                        }
                    }
                    if(!empty($chapter['papers'])){
                        $chapter['papers'] = array_splice($chapter['papers'],0,count($chapter['papers']));
                    }
                }
                $retval['data']['chapters'][] = $chapter;
            }
        }
    }

}
