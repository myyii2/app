<?php
namespace common\library;

class Common{

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




}
