<?php

namespace WolfpackIT\\models\activeRecord;

use oauth\models\ActiveRecord;
use oauth\queries\activeQuery\AccessTokenQuery;
use yii\db\ActiveQuery;

/**
 * Class AccessTokenScope
 * @package oauth\models\activeRecord
 *
 * @property int $access_token_id
 * @property-read AccessToken $accessToken
 * @property int $scope_id
 * @property-read Scope $scope
 */
class AccessTokenScope extends ActiveRecord
{
    /**
     * @return AccessTokenQuery
     */
    public function getAccessToken()
    {
        return $this->hasOne(AccessToken::class, ['id' => 'access_token_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getScope()
    {
        return $this->hasOne(Scope::class, ['id' => 'scope_id']);
    }
}