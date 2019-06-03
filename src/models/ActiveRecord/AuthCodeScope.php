<?php

namespace WolfpackIT\oauth\models\activeRecord;

use oauth\models\ActiveRecord;
use oauth\queries\activeQuery\AuthCodeQuery;
use yii\db\ActiveQuery;

/**
 * Class AuthCodeScope
 * @package oauth\models\activeRecord
 *
 * @property int $auth_code_id
 * @property-read AuthCode $authCode
 * @property int $scope_id
 * @property-read Scope
 */
class AuthCodeScope extends ActiveRecord
{
    /**
     * @return AuthCodeQuery
     */
    public function getAuthCode()
    {
        return $this->hasOne(AuthCode::class, ['id' => 'auth_code_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getScope()
    {
        return $this->hasOne(Scope::class, ['id' => 'scope_id']);
    }
}