<?php

namespace api\models;

use Yii;

/**
 * This is the model class for table "user_staff".
 *
 * @property string $uid
 * @property integer $dept_id
 * @property string $enterprise_id
 * @property integer $gender
 * @property string $birthday
 * @property string $idcardNum
 * @property string $phone
 * @property string $question
 * @property integer $home_id
 * @property string $address
 * @property string $graduateSchool
 * @property string $education
 * @property string $position
 * @property string $interest
 * @property integer $avatar
 * @property string $extraP
 * @property string $dream
 * @property string $introduction
 * @property integer $isAdmin
 */
class UserStaff extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_staff';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uid', 'dept_id', 'enterprise_id', 'question'], 'required'],
            [['uid', 'dept_id', 'enterprise_id', 'gender', 'home_id', 'avatar', 'isAdmin'], 'integer'],
            [['birthday'], 'safe'],
            [['question', 'introduction'], 'string'],
            [['idcardNum'], 'string', 'max' => 18],
            [['phone'], 'string', 'max' => 15],
            [['address'], 'string', 'max' => 200],
            [['graduateSchool', 'interest', 'extraP', 'dream'], 'string', 'max' => 255],
            [['education', 'position'], 'string', 'max' => 100]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'uid' => Yii::t('api', '员工id'),
            'dept_id' => Yii::t('api', '所属部门id'),
            'enterprise_id' => Yii::t('api', '所属公司id'),
            'gender' => Yii::t('api', '性别,0-默认 1-男 2-女'),
            'birthday' => Yii::t('api', '出生日期'),
            'idcardNum' => Yii::t('api', '身份证号'),
            'phone' => Yii::t('api', '员工联系电话'),
            'question' => Yii::t('api', '密保问题和答案'),
            'home_id' => Yii::t('api', '员工故乡地区'),
            'address' => Yii::t('api', '员工联系地址'),
            'graduateSchool' => Yii::t('api', '毕业院校'),
            'education' => Yii::t('api', '学历'),
            'position' => Yii::t('api', '公司职务'),
            'interest' => Yii::t('api', '兴趣爱好'),
            'avatar' => Yii::t('api', '头像'),
            'extraP' => Yii::t('api', 'Extra P'),
            'dream' => Yii::t('api', '人生理想'),
            'introduction' => Yii::t('api', '员工简介'),
            'isAdmin' => Yii::t('api', '是否为管理员'),
        ];
    }
}
