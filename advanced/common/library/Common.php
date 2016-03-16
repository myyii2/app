<?php
namespace common\library;

class Common{

    const PAGE_SIZE=10;
    const USER_PERSON = 1;//个人用户
    const USER_ENTERPRISE_UNAUDITED = 2; //未通过企业营业执照审核的企业用户
    const USER_STAFF = 3;//企业员工
    const USER_AGENCY = 4;//机构用户
    const USER_ENTERPRISE = 5; //通过企业营业执照审核的企业用户
    const USER_MANAGER = 6; //企业授权辅助管理员
    const USER_YIQUN = 7; //医群视频管理员账号
    const USER_DOCTOR = 193; //医务工作者
    const COMMOMDIR = __FILE__;
    const USER_VIDEO = 8; //科室审核

    const USER_HOSPITAL = 183; //医院账号
    const USER_KESHI = 185; //科室账号
    const USER_SHIYANSHI = 187; //实验室账号
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


}
