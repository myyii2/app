<?php
namespace api\controllers;

use Yii;
use yii\web\Controller;


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
        $sql= "SELECT * FROM course";
        $firsttime = time();

        $rows=Yii::$app->db1->createCommand($sql)->query();
        $secondtime = time();

        foreach($rows as $k => $v){
            print_r($v);exit;
        }

        $sql= "SELECT * FROM user";
        $rows=Yii::$app->db->createCommand($sql)->query();
        foreach($rows as $k => $v){
            print_r($v);exit;
        }

    }

}
