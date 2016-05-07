<?php
namespace common\library;
use Yii;

class Common{

    //vip 价格
    static $vip_package = array(
        'a'=>array('id'=>70,'price'=>1000.00,'name'=>'A套餐 生物无忧专科会员'),
        'b'=>array('id'=>68,'price'=>1000.00,'name'=>'B套餐 无忧学院全科会员','pid'=>array(478,479),'class_id'=>471),
        'c'=>array('id'=>407,'price'=>1600.00,'name'=>'C套餐 生命科学会员','pid'=>407),
    );

    static function array_sort(array $arr, $keys, $type = 'asc'){

        $keysvalue = $new_array = array();
        foreach ($arr as $k => $v) {
            $keysvalue[$k] = $v[$keys];
        }
        if ($type == 'asc') {
            array_multisort($keysvalue, SORT_ASC, $arr);
        } else {
            array_multisort($keysvalue, SORT_DESC, $arr);
        }
        return $arr;
    }

    /**
     * 获取会员制资源的资源套餐类型
     * @param int $cate_id  资源分类类型
     * @param int $class_id 资源难度等级
     * @return array        返回该资源所属的套餐等级数组
     */
    static function getVipCate($cate_id = 0,$class_id = 0) {

        $vipConf = common::$vip_package;
        $retArr = array();    //会员制类型返回值数组
        foreach ($vipConf as $confKey => $confOne) {
            $cateList = self::getCateChild($confOne['id']);
            $cidArr = self::getCateChildCids($cateList);
            if (!in_array($cate_id,$cidArr) || (isset($confOne['class_id']) && $confOne['class_id'] != $class_id)) {
                continue;
            }
            array_push($retArr,$confKey);
        }
        return $retArr;
    }

    static function getCateChild($cid){

        $root = self::getFileCache( './../runtime/compile/swwy_classify_tree.php');
        $ret = self::getCateChild_iterator($root,$cid);
        if($ret && isset($ret['children']))
            return $ret['children'];
        return $ret;
    }

    static function getCateChild_iterator($root,$cid){
        foreach($root as $row){
            if($row['id'] == $cid)
                return $row;
            if(isset($row['children']) && is_array($row['children'])){
                $ret = self::getCateChild_iterator($row['children'],$cid);
                if($ret != false)
                    return $ret;
            }
        }
    }

    static function getFileCache($file) {
        if (file_exists($file)) {
            $retval = include($file);
            return $retval['data'];
        } else {
            return array();
        }
    }

    static function getCateChildCids($list){
        $cids = array();
        if($list){
            if(isset($list['children'])){
                foreach ($list['children'] as $v) {
                    $cids[]  = $v['id'];
                    if(isset($v['children'])){
                        foreach($v['children'] as $v1){
                            $cids[]  = $v1['id'];
                        }
                    }
                }
            } elseif(isset($list['id'])) {
                $cids[] = $list['id'];
            } else {
                foreach($list as $value) {
                    $cids[] =  $value['id'];
                }
            }
        }
        return $cids;
    }

    /**
     * 生成购买会员套餐导向的提示信息
     * @param array $vipType    资源所属的套餐
     * @param int $cateId       资源所属的分类
     * @return mixed|string     提示信息
     */
    static function getVipTips($vipType = array(),$cateId = 0) {
        $msgStr = "开通 #object# 会员可免费观看本视频";
        $typeTips = array();    //套餐说明数组
        foreach ($vipType as $typeOne) {
            $tmpStr = "";
            if (strtolower($typeOne) == 'a') {  //a套餐，按科目细分
                $tmpStr .= $typeOne."-".self::getCateName($cateId);
            } else {    //b、c套餐固定整套
                $tmpStr .= $typeOne;
            }
            array_push($typeTips,$tmpStr."套餐");
        }
        $msgStr = str_replace("#object#",implode(",",$typeTips),$msgStr);
        return $msgStr;
    }

    static function getCateName($cid){
        $root = self::getFileCache( './../runtime/compile/swwy_classify_tree.php');
        $ret = self::getCateChild_iterator($root,$cid);
        return $ret['name'];

    }

    //给默认图片加上域名前缀
    static function prefixImage($image) {
        global $baseDomain;

        if (strpos($image, 'http') === false) {
            $image = $baseDomain.$image;
        }
        return $image;
    }

    /**
     * app使用专属的默认图片
     * @param int $fromType 区分是从移动端请求数据,移动端不使用默认图片,0--web端，1--app端
     * @return string
     */
    static function getImageUrl($fromType = 0,$courseId,$server_id=1){
        $retData = "";
        if ($server_id) {
            $retData = self::get_photo_url($courseId,'course',$server_id);
        } else {
            empty($fromType) && $retData = Yii::$app->params['DEFAULT_VIDEO_IMG'];
        }
        return $retData;
    }

    /**
     * 返回图片http地址
     * @param $pid
     * @param string $type
     * @param int $sid
     * @param bool $preFS   是否优先使用文件服务器路径
     * @return bool|string
     */
    static function get_photo_url($pid,$type='logo',$sid=1,$preFS = false){
        if (empty($sid)) {  //服务器id为0或为null即表示图片尚未上传，返回默认图片
            return Yii::$app->params['DEFAULT_VIDEO_IMG'];
        }

        $pType = Yii::$app->params['TYPE_PHOTO_JPG'];
        if($type == "EnterpriseLogo" || $type == "qrcode"){      //企业logo图片或企业二维码，png可透明背景
            $pType = Yii::$app->params['TYPE_PHOTO_PNG'];
        }elseif($type == "EnterpriseIco"){
            $pType = Yii::$app->params['TYPE_PHOTO_ICO'];
        }

        //存在redis记录走cdn路线
        if (!$preFS) {
            $name = $type."_".self::make_name($pid,$pType);
            $cache = \common\library\Redis::getQiNiuInfo($name,Yii::$app->params['RES_PIC']);
        } else {
            $cache = false;
        }
        $retUrl = !empty($cache) ? $cache : "http://p".$sid.Yii::$app->params['BASE_URL']."/".self::make_store_path($pid,$type).self::make_name($pid,$pType);
        return $retUrl;
    }

    /**
     *
     * @param int $id
     * @param const $type TYPE_VIDEO,TYPE_PHOTO,TYPE_ATTACHMENT 3种产品类型
     * @return string 返回文件名
     */
    static function make_name($id,$type=null){

        if ( $type ==  Yii::$app->params['TYPE_VIDEO'])
            $ext = "mp4";
        else if( $type == Yii::$app->params['TYPE_PHOTO_PNG'])
            $ext = "png";
        else if($type == Yii::$app->params['TYPE_PHOTO_JPG'])
            $ext = "jpg";
        else if($type == Yii::$app->params['TYPE_PHOTO_ICO'])
            $ext = "ico";
        if($type!=null)
            return self::encode_play_querystr($id).".".$ext;
        else
            return self::encode_play_querystr($id);
    }


    static function encode_play_querystr($id, $type=1){
        $items = func_get_args();
        $retval = implode('|',$items);

        //校验码
        $hash = crc32($retval);
        $hash = sprintf("%x", $hash);
        $hash = fmod(hexdec($hash), 99);
        $hash = sprintf("%d", $hash);
        $items[] = $hash;
        //序列化
        $retval = implode('|',$items);
        //加密
        $retval = self::crypto($retval);
        //编码
        $retval = self::base64url_encode($retval);
        //去冗余
        $retval = str_replace("=", "", $retval);
        return $retval;
    }

    private function make_store_path($id,$type){
        $path1 = $id%255;
        $path2 = $id;
        $path = $type."/".$path1."/".$path2."/";
        return $path;
    }


    static function crypto($str) {
        $len=strlen($str);
        $retval="";
        for ($pos = 0;$pos<$len;$pos++){
            $keyToUse = (($len+$pos)+1); // (+5 or *3 or ^2)
            $keyToUse = (255+$keyToUse) % 255;
            $byteToBeEncrypted = substr($str, $pos, 1);
            $asciiNumByteToEncrypt = ord($byteToBeEncrypted);
            $xoredByte = $asciiNumByteToEncrypt ^ $keyToUse;  //xor operation
            $encryptedByte = chr($xoredByte);
            $retval .= $encryptedByte;
        }
        return $retval;
    }

    static function base64url_encode($plainText){
        $base64 = base64_encode($plainText);
        $base64 = str_replace("=", "", $base64);
        $base64url = strtr($base64, '+/', '-_');
        return ($base64url);
    }

    //视频播放页地址
    static function dealPlayerUrl($id, $type) {
        $encodeQueryStr = self::encode_play_querystr($id, $type);
        $videoUrl = 'http://'.Yii::$app->params['MAIN_TOP_DOMAIN'].'.mycs.cn/player/'.$encodeQueryStr.'.html';

        $extData = array() ;
        $extData['id'] = $id ;
        switch ($type) {
            case Yii::$app->params['TYPE_VIDEO']:
                $extData['type'] = 2 ;
                break;
            case Yii::$app->params['TYPE_COURSE']:
                $extData['type'] = 3 ;
                break;
            case Yii::$app->params['TYPE_SOP']:
                $extData['type'] = 4 ;
                break;
            default:
                $extData['type'] = 2 ;
                break;
        }
        return $videoUrl . "?extData=" . base64_encode(json_encode( $extData ));
    }

    /*
	 * 返回视频http地址
	 */
    static function get_video_url($uid,$vid,$sid,$resType){
        $resType = !empty($resType)?$resType:Yii::$app->params['RES_VIDEO'];
        $name = self::make_name($vid,Yii::$app->params['TYPE_VIDEO']);
        $cache = \common\library\Redis::getQiNiuInfo($name,$resType);
        $url = $cache?$cache:"http://v".$sid.Yii::$app->params['BASE_URL']."/".self::make_video_store_path($uid,$vid,Yii::$app->params['TYPE_VIDEO']).$name;
        return $url;
    }
    
    /**
	 * 
	 * @param unknown_type $uid
	 * @param unknown_type $vid
	 * @param unknown_type $sid
	 * @param string $key 图片尺寸  big,middle,small 目前只有big
	 * @return string|multitype:string
	 */
        static  function get_video_photo_url($uid,$vid,$sid=1){
              $videoUrl = "http://v".$sid.Yii::$app->params['BASE_URL']."/".self::make_video_store_path($uid,$vid,Yii::$app->params['TYPE_VIDEO']).'big.jpg';
              return $videoUrl;
        }

    private function make_video_store_path($uid,$id,$type){
        $path1 = $uid%255;
        $path2 = $uid;
        $path3 = $id;
        $path = $path1."/".$path2."/".$path3."/";
        return $path;
    }

    //format time length to 00:00:00
    //$timeLength 90s to 01:30
    static function formatTime($timeLenght){
        $hour="00";
        $t = date('i:s',ceil($timeLenght));
        if($timeLenght>=3600){
            $hour= floor($timeLenght / 3600);
            if(strlen($hour)<2) $hour='0'.$hour;
        }
        return $hour.":".$t;
    }

    // 输出json格式给app
    static function outputJson($retvals) {

        $retval['code'] = 1;
        //出错时，直接传出错提示即可
        if (!is_array($retvals)) {
            $retval = array('code' => Yii::$app->params['CODE_ERROR'], 'data' => (object) array(), 'msg' => $retvals);
        }else{
            $retval = array('code' => Yii::$app->params['CODE_SUCCEED']);
        }

        if (!empty($retvals)) {
            $retval['data'] = $retvals;
        }
        
        if (!isset($retval['msg'])) {
            $retval['msg'] = "";
        }
        echo json_encode($retval);
        exit;
    }

}
