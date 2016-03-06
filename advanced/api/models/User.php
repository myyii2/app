<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "user".
 *
 * @property string $uid
 * @property string $username
 * @property string $password
 * @property string $salt
 * @property integer $userType
 * @property integer $agroup_id
 * @property string $realname
 * @property string $email
 * @property string $mobile
 * @property string $applyCode
 * @property string $create_uid
 * @property string $regIp
 * @property integer $regTime
 * @property integer $sid
 * @property integer $status
 * @property integer $upTime
 * @property integer $platformAuth
 * @property integer $personTag
 * @property string $homeInfo
 * @property integer $sorts
 */
class User extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['userType', 'agroup_id', 'create_uid', 'regTime', 'sid', 'status', 'upTime', 'platformAuth', 'personTag', 'sorts'], 'integer'],
            [['homeInfo'], 'required'],
            [['homeInfo'], 'string'],
            [['username', 'realname'], 'string', 'max' => 100],
            [['password'], 'string', 'max' => 32],
            [['salt'], 'string', 'max' => 6],
            [['email', 'applyCode'], 'string', 'max' => 50],
            [['mobile'], 'string', 'max' => 20],
            [['regIp'], 'string', 'max' => 30],
            [['username', 'email', 'mobile'], 'unique', 'targetAttribute' => ['username', 'email', 'mobile'], 'message' => 'The combination of 登录名, 用户email and 绑定电话 has already been taken.']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'uid' => Yii::t('app', '用户id'),
            'username' => Yii::t('app', '登录名'),
            'password' => Yii::t('app', '密码'),
            'salt' => Yii::t('app', 'Salt'),
            'userType' => Yii::t('app', '用户角色类型，1-个人用户 3-企业员工 4-培训机构 5企业用户 6企业子管理员'),
            'agroup_id' => Yii::t('app', '用户组类型id'),
            'realname' => Yii::t('app', '用户姓名'),
            'email' => Yii::t('app', '用户email'),
            'mobile' => Yii::t('app', '绑定电话'),
            'applyCode' => Yii::t('app', '泛解析域名短码'),
            'create_uid' => Yii::t('app', '创建人id'),
            'regIp' => Yii::t('app', '注册IP'),
            'regTime' => Yii::t('app', '注册时间'),
            'sid' => Yii::t('app', '个人和企业用户的头像，机构和公司表示营业执照 是否上传'),
            'status' => Yii::t('app', '账号状态，0为关闭，1为开启 , 2为待删除'),
            'upTime' => Yii::t('app', '置顶时间，默认值0为不置顶'),
            'platformAuth' => Yii::t('app', '平台认证状态，0--未通过，1--已通过'),
            'personTag' => Yii::t('app', '0-无 1-人物 2-医生 3-专家 4-名医 '),
            'homeInfo' => Yii::t('app', '个性主页信息'),
            'sorts' => Yii::t('app', 'Sorts'),
        ];
    }
}
