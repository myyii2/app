<?php
namespace api\controllers;

use Yii;
use yii\web\Controller;
use app\models\User;
use api\models\Task;
use common\library\Redis;


class SiteController extends Controller
{

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

        //$user = User::find()->where(['uid' => 18])->one();
        //$userList = User::find()->indexBy('uid')->all();
        ini_set("display_errors", "On");
        error_reporting(E_ALL | E_STRICT);


        $customer = User::findOne(500153);
        $customer->username = 'tttter';
        $userList = $customer->save();


        var_dump($userList);exit;
    }

    public function actionDetail(){

        $request = Yii::$app->request;
        $userId = $request->get('userId');
        $courseId = $request->get('id');
        $task_id = $request->get('task_id');

        $condition['taskId'] = $task_id;
        $myFeilds = array('taskName','pass_rate','endTime','taskId','add_uname');

        $returnData = Task::find()->select($myFeilds)->where($condition)->all();

        //echo Task::find()->select($myFeilds)->where($condition)->createCommand()->getRawSql();exit;

        $retval['data']['taskId'] =!empty($returnData['data'][0]['taskId'])?$returnData['data'][0]['taskId']:'';
        $retval['data']['taskName'] =!empty($returnData['data'][0]['taskName'])?$returnData['data'][0]['taskName']:'';
        $retval['data']['passRate'] = !empty($returnData['data'][0]['pass_rate'])?$returnData['data'][0]['pass_rate']:'';
        $retval['data']['endTime'] = !empty($returnData['data'][0]['endTime'])?$returnData['data'][0]['endTime']:'';
        $retval['data']['enterpriseName'] = !empty($returnData['data'][0]['add_uname'])?$returnData['data'][0]['add_uname']:'';


        $res = array('fe'=>'3232','ef'=>'54');

        $myres = serialize($request);

        Yii::$app->redis->set('a',$myres);
        //$course = Redis::getCourse($courseId);
        $res = Yii::$app->redis->get('a');

        $res = unserialize($res);
        print_r($res->get('id'));exit;

        //判断courseId是否存在
        $res = $course->courseId;
        if ($res === null) {
            outputJson('课程不存在');
        }


        // $user = new User();
        // $user->getRowById($userId);
        // $userInfo = $user->getRawDataFromBack();
        // common::$current_user['uid'] = $userId;
        // common::$current_user['userType'] = $userInfo['data']['userType'];

        $courseObj = new Course();
        $courseObj->getRowById($courseId);
        $courseInfo = $courseObj->getRawDataFromBack();

        $picList = json_decode($courseInfo['data']['picList'],1);
        foreach($picList as $key=>$val){
            $retval['data']['picList'][] = Rule::get_photo_url($val, "unifyUpload");
        }

        if(empty($picList)){
            $retval['data']['picList'][] = prefixImage($course->getImageUrl(1));
            $retval['data']['interval_time'] = 0;
        }else{
            $retval['data']['interval_time'] = 2000;
        }

        $course->hasItem = $course->hasPaper;
        if ($course->status && !SessionRegister::getCourseHits($courseId)) { //筛选已经被审核且当前用户未记录点击数
            $courseObj->addHits($courseId);
            SessionRegister::setCourseHits($courseId);
        }



        //购买的，查原作者
        $fromUserName = '';
        if (!empty($course->source)) {
            $courseObj->getRowById($courseId);
            $courseInfo = $courseObj->getRawDataFromBack();
            if (!empty($courseInfo['data']['srcCourseId'])) {
                $sourceCourse = ApplicationRegistry::getCourse($courseInfo['data']['srcCourseId']);
                $fromUid = $sourceCourse->from_uid;
            } else {
                //查不到来源的
                $course->source = 0;
            }
        }

        //游客，加上来源
        // if (empty($userId))
        $fromUid = $course->from_uid;

        if (!empty($fromUid)) {
            $user = new User();
            $user->getRowById($fromUid, array('realname', 'email'));
            $data = $user->getRawDataFromBack();
            $fromUserName = !empty($data['data']['realname']) ? $data['data']['realname'] : $data['data']['email'];
        }

        //判断是否收藏
        $con['uid'] = $userId;
        $con['data_id'] = $courseId;
        $con['data_type'] = 'course';
        $commonFav = new module\User\controller\CommonFav();
        $commonFav->getListCount($con);
        $dataCount = $commonFav->getRawDataFromBack();
        if($dataCount['data'] > 0){
            $retval['data']['is_collect']   = 1;
        }else{
            $retval['data']['is_collect']   = 0;
        }

        $retval['data']['comment_total'] = intval($course->commentTotal);
        $retval['data']['author'] = $fromUserName;
        $retval['data']['click']  = intval($course->viewers);

        $cateList = Toolkit::getCate($course->cate_id);
        $retval['data']['category'] = empty($cateList['name']) ? '未分类' : $cateList['name'];

        //查询资源额外权限
        $src_ext = new module\Course\controller\SrcExt();
        $src_ext->getList(0, 0, array('type'=>SRC_TYPE_COURSE,'srcId'=>$courseId),array(),array());
        $src_ext_data = $src_ext->getRawDataFromBack();
        $src_ret = (int)$src_ext_data['data'][0]['ret'];        //额外限制权限，默认为0--无额外权限

        //默认，不需要申请购买
        $retval['data']['buy'] = 0;
        //额外权限，默认为没有额外权限限制
        $retval['data']['extra_permission'] = 0;
        //是否已获得套餐权限，默认为没有获得
        $retval['data']['isMember'] = 0;
        $retval['data']['memberType'] = array();    //返回套餐类型数组
        $retval['data']['vipTips'] = "";    //会员制套餐导购提示信息

        if (!empty($task_id)) {     //判断任务权限,任务情况
            $TulObj = new \module\Course\controller\TaskUserLink();
            $TulObj->getList(1,1,array("user_id" => $userId,"task_id" => $task_id),
                array("task->course_id","task->status"));
            $retData = $TulObj->getRawDataFromBack();


            $retData['code'] != common::CODE_SUCCEED && outputJson($retData['msg']);
            $retData = $retData['data'];
            $tulData = reset($retData);

            empty($tulData) && outputJson("无相关任务信息");

            if ($tulData['course_id'] != $courseId) {
                outputJson("课程信息与任务信息不匹配");
            }

            //考核状态判断
            if ($tulData['status'] != common::TASK_GOING) {
                outputJson("任务已过期");
            }

            //获取考核章节索引
            $taskJoin = new TaskJoinForMycs();
            $taskJoin->getTaskNextChapter($task_id,$userId);
            $retData = $taskJoin->getRawDataFromBack();
            $nextIndex = $retData['data']['optional'];
            $curChapterStatus = $retData['data']['curChapterStatus'];
//        $taskJoin->getUserNextChapter($task_id, $userId);
//        $ret = $taskJoin->getRawDataFromBack();
//        $lastChapterId = $ret['data']['chapter_id'];
//
//        $nextIndex = $course->getNextChapterIndex($lastChapterId);


            //更新task_user_link状态 及响应时间
            //$TulObj->updateList(array("status"=>1), array('task_id'=>$task_id,"user_id" => $userId,"status"=>0));
            //$TulObj->setResponse($userId,$task_id);

        } else {
            $retval['data']['extra_permission'] = $src_ret;
            if (SRC_RET_MEMBER == $src_ret) {
                //查询是否已经购买会员
                $mret = Rule::getVipCate($course->cate_id,$course->class_id) ;
                $retval['data']['memberType'] = $mret;
                if($mret){
                    $checkUid = !empty($enterpriseId) ? $enterpriseId : $userId;
                    $vip = new module\User\controller\Vip;
                    $vip->getCountByUid( $checkUid,$mret,$course->cate_id);
                    $tempData = $vip->getRawDataFromBack();

                    if($tempData['data'][0]['count'] > 0){
                        //如果已经购买了会员
                        $retval['data']['isMember'] = 1;
                    } else {
                        $retval['data']['vipTips'] = Rule::getVipTips($mret,$course->cate_id);
                    }
                }
            }


            //    判断对内权限
            if ($course->from_uid == $enterpriseId && $course->int_permission == 0) {
                outputJson('此教程对内不公开');
            }

            //要购买的教程
            if ($course->from_uid != $userId && $course->ext_permission == 3) {
                // 判断员工所在公司有没有已经购买
                if (!empty($enterpriseId) && $course->from_uid != $enterpriseId) {

                    $payingGoods = new module\Course\controller\PayingGoods;
                    //要申请购买
                    $retval['data']['buy'] = 1;
                    //是否已申请购买
                    $buyWhere = array('goodsType' => 0, 'goodsId' => $courseId, 'replyUid' => $enterpriseId);
                    $payingGoods->getList(0, 0, $buyWhere, array(), array('id' => 'desc'));
                    $list = $payingGoods->getRawDataFromBack();
                    if (!empty($list['data'])) {
                        if ($list['data'][0]['status'] == 1) {
                            $retval['data']['buy'] = 3;           //已买

                        } elseif ($list['data'][0]['status'] == 0) {
                            //未处理
                            $payingApply = new module\Course\controller\PayingApply;
                            $payingApply->getListCount(array('payingGoodsId' => $list['data'][0]['id'], 'uid' => $userId));
                            $total = $payingApply->getRawDataFromBack();
                            if (!empty($total['data'])) {
                                $retval['data']['buy'] = 2;
                            }
                        }
                    }

                } elseif ($userType != common::USER_STAFF) {

                    $where = array('from_uid' => $userId, 'srcCourseId' => $courseId);
                    $courseObj->getListCount($where);
                    $courseTotal = $courseObj->getRawDataFromBack();

                    //没买，需要购买
                    if (empty($courseTotal['data'])){
                        $retval['data']['buy'] = 1;
                    } else {
                        $retval['data']['extra_permission'] = 0;
                    }

                }
            }
        }

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
