<?php
namespace common\library;
use Yii;


class Redis{

    static function getCourse($courseId){

        $courseId = (int)$courseId;
        if(!$courseId){
            throw new AppException('wrong paramter');
        }
        $course=self::instance()->get('course_'.$courseId);

        if(!$course){//如果memcache里没有这个key就执行接口写cache
            //echo "http://www".\common::BASE_URL."/app/apps/writeCache.php?model=course&course_id=".$courseId;
            \Toolkit::sendHTTPRequest("http://".\common::MAIN_TOP_DOMAIN.\common::BASE_URL."/app/apps/writeCache.php?model=course&course_id=".$courseId);
            self::clearInstance();
            $course=self::instance()->get('course_'.$courseId);
        }

        return $course;
    }



}
