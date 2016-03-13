<?php

namespace api\models;

use Yii;

/**
 * This is the model class for table "course_category".
 *
 * @property integer $cateId
 * @property string $cateName
 * @property integer $parent_id
 * @property integer $hasChild
 * @property integer $listorder
 */
class CourseCategory extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'course_category';
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
            [['parent_id', 'hasChild', 'listorder'], 'integer'],
            [['cateName'], 'string', 'max' => 100]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'cateId' => Yii::t('api', '分类id'),
            'cateName' => Yii::t('api', '分类名称'),
            'parent_id' => Yii::t('api', '上级分类id'),
            'hasChild' => Yii::t('api', '是否有子分类'),
            'listorder' => Yii::t('api', '排序'),
        ];
    }
}
