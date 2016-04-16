<?php

namespace common\library;
use Yii;

class Redis{

    private static $status;        //保存redis运行、使用状态
    private static $mp4_qiniu = 1;   //redis视频mp4分库
    private static $m3u8_qiniu = 2;   //redis视频m3u8分库
    private static $pic_qiniu = 3;     //redis图片分库

    static function getQiNiuInfo($key,$type) {

        $type = !empty($type)?$type:Yii::$app->params['RES_VIDEO'];
        $index = 0;
        switch ($type) {
            case Yii::$app->params['RES_VIDEO']:
                $index = self::$m3u8_qiniu;
                break;
            case Yii::$app->params['RES_VIDEO_MP4']:
                $index = self::$mp4_qiniu;
                break;
            case Yii::$app->params['RES_PIC']:
                $index = self::$pic_qiniu;
                break;
            default:
                return false;
                break;
        }
        Yii::$app->redis->select($index);
        return Yii::$app->redis->get($key);
    }


    /**
     * 缓存用户常用信息
     * @param int $userId   用户id
     * @param int $isDelete 是否进行删除缓存操作，0--不清理，获取缓存数据，1--清理缓存数据
     * @return array|bool|int|mixed|void    一维数组，enterpriseUid--企业用户id，deptId--部门id
     */
    static function checkStaffInfo($userId = 0,$isDelete = 0,$retData) {

        $checkKey = Yii::$app->params['Redis']['staffInfoKey'].$userId;
        if (!empty($isDelete)) {
            return Yii::$app->redis->del($checkKey);
        }
        $dataInfo = Yii::$app->redis->get($checkKey);
        if (empty($dataInfo)) {
            $dataInfo = array();
            if (!empty($retData)) {
                $dataInfo['enterpriseUid'] = $retData['enterprise_id'];
                $dataInfo['deptId'] = $retData['dept_id'];
                $dataInfo['isAdmin'] = $retData['isAdmin'];
            }
            $resList = serialize($dataInfo);
            Yii::$app->redis->set($checkKey,$resList);
        }else{
            $dataInfo = unserialize($dataInfo);
        }
        return array("uid" => $userId,"enterprise_id" => $dataInfo['enterpriseUid'],"dept_id" => $dataInfo['deptId'],"isAdmin" => $dataInfo['isAdmin']);
    }


    /**
     * 根据部门id 获取旗下全都的部门
     * @param  integer $deptId 部门id
     * @return array           部门id 数组
     */
    static function getDepartmentChild($staffAdminInfo,$retData){

        $deptTree = Redis::checkDeptTree($staffAdminInfo['enterprise_id'],0,$retData);
        if (!empty($deptTree)) {
            $deptInfo = Redis::findWithKeyInArr($staffAdminInfo['dept_id'],$deptTree);
            $deptArr = array($staffAdminInfo['dept_id']);
            $deptArr = array_merge($deptArr,Redis::getTreeAllId($deptInfo['child']));
        }
        return $deptArr;

    }


    /**
     * 缓存企业组织结构信息(唯一id做索引的数组)
     * @param int $userId   企业类型用户id
     * @param int $isDelete 是否进行删除缓存操作，0--不清理，获取缓存数据，1--清理缓存数据
     * @return array|bool|int|mixed|void     组织结构树,child--保存子级节点
     */
    static function checkDeptTree($userId = 0,$isDelete = 0,$retData) {
        $checkKey = Yii::$app->params['Redis']['deptTreeKey'].$userId;
        if (!empty($isDelete)) {
            return Yii::$app->redis->del($checkKey);
        }
        $dataInfo = Yii::$app->redis->get($checkKey);
        if (empty($dataInfo)) {
            $dataInfo = array();
            $keyArr = array();
            foreach ($retData as $retOne) {
                array_push($keyArr,$retOne['deptId']);
            }
            $deptList = array_combine($keyArr,$retData);

            $treeArr = array();
            foreach($deptList as $deptOne) {
                if (isset($deptList[$deptOne['parent_id']])) {
                    $deptList[$deptOne['parent_id']]['child'][$deptOne['deptId']] = &$deptList[$deptOne['deptId']];
                } else {
                    $treeArr[$deptOne['deptId']] = &$deptList[$deptOne['deptId']];
                }
                //去除多余的属性
                $deptList[$deptOne['deptId']] = array_intersect_key($deptList[$deptOne['deptId']],array("deptName" => '',"child" => ''));
            }
            Yii::$app->redis->set($checkKey,json_encode($treeArr));
            $dataInfo = $treeArr;
        } else {
            $dataInfo = json_decode($dataInfo,true);
        }
        return $dataInfo;
    }

    /**
     * 递归在部门结构树里面搜索特定的节点（唯一id索引多维数组）
     * @param string $key   要检索的索引
     * @param array $targetArr  检索的目标结构树数组
     * @return bool 返回false或检索出来的节点内容
     */
    static function findWithKeyInArr($key = "",$targetArr = array()) {
        if (array_key_exists($key,$targetArr)) {
            return $targetArr[$key];
        }
        foreach($targetArr as $value) {
            if(isset($value['child'])){
                $result = self::findWithKeyInArr($key,$value['child']);
                if($result !== false){
                    return $result;
                }
            }

        }
        return false;
    }


    /**
     * 获取一个id唯一索引树的所有索引id
     * @param array $treeArr
     * @return array
     */
    static function getTreeAllId($treeArr = array()) {
        $retArr = array();
        if (!empty($treeArr)) {
            foreach($treeArr as $key => $value) {
                if(isset($value['child'])) {
                    $retArr = array_merge($retArr,self::getTreeAllId($value['child']));
                }
                array_push($retArr,$key);
            }
        }
        return $retArr;
    }



}
