<?php

namespace WolfpackIT\oauth\models\activeRecord;

use WolfpackIT\oauth\queries\activeQuery\AuthCodeQuery;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Class AuthCodeScope
 * @package WolfpackIT\oauth\models\activeRecord
 *
 * @property int $auth_code_id
 * @property-read AuthCode $authCode
 * @property int $scope_id
 * @property-read Scope $scope
 */
class AuthCodeScope extends ActiveRecord
{
    /**
     * @var string
     */
    protected $authCodeClass = AuthCode::class;

    /**
     * @var string
     */
    protected $scopeClass = Scope::class;

    /**
     * @return AuthCodeQuery
     */
    public function getAuthCode()
    {
        return $this->hasOne($this->authCodeClass, ['id' => 'auth_code_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getScope()
    {
        return $this->hasOne($this->scopeClass, ['id' => 'scope_id']);
    }
}