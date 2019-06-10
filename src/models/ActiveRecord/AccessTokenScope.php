<?php

namespace WolfpackIT\oauth\models\activeRecord;

use WolfpackIT\oauth\queries\activeQuery\AccessTokenQuery;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Class AccessTokenScope
 * @package WolfpackIT\oauth\models\activeRecord
 *
 * @property int $access_token_id
 * @property-read AccessToken $accessToken
 * @property int $scope_id
 * @property-read Scope $scope
 */
class AccessTokenScope extends ActiveRecord
{
    /**
     * @var string
     */
    protected $accessTokenClass = AccessToken::class;

    /**
     * @var string
     */
    protected $scopeClass = Scope::class;

    /**
     * @return AccessTokenQuery
     */
    public function getAccessToken(): AccessTokenQuery
    {
        return $this->hasOne($this->accessTokenClass, ['id' => 'access_token_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getScope(): ActiveQuery
    {
        return $this->hasOne($this->scopeClass, ['id' => 'scope_id']);
    }
}