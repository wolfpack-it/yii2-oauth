<?php

namespace WolfpackIT\oauth\models\activeRecord;

use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use WolfpackIT\oauth\models\ActiveRecord;
use WolfpackIT\oauth\queries\activeQuery\RefreshTokenQuery;
use WolfpackIT\oauth\traits\ExpirableTrait;
use WolfpackIT\oauth\traits\IdentifiableTrait;

/**
 * Class RefreshToken
 * @package WolfpackIT\oauth\models\activeRecord
 *
 * @property int $id
 * @property int $access_token_id
 * @property-read AccessToken $relatedAccessToken
 * @property string $identifier
 * @property int $expired_at
 * @property AccessToken $accessToken
 * @property int $status
 */
class RefreshToken
    extends ActiveRecord
    implements RefreshTokenEntityInterface
{
    use ExpirableTrait;
    use IdentifiableTrait;

    const STATUS_CREATION = -1;
    const STATUS_ENABLED = 1;
    const STATUS_REVOKED = 0;

    /**
     * @var string
     */
    protected $accessTokenClass = AccessToken::class;

    /**
     * Ignore the invalidConfigException
     *
     * @return RefreshTokenQuery
     */
    public static function find(): RefreshTokenQuery
    {
        return \Yii::createObject(RefreshTokenQuery::class, [get_called_class()]);
    }

    /**
     * @return AccessToken
     */
    public function getAccessToken(): AccessToken
    {
        return $this->relatedAccessToken;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRelatedAccessToken()
    {
        return $this->hasOne($this->accessTokenClass, ['id' => 'access_token_id']);
    }

    /**
     * @param AccessToken $accessToken
     */
    public function setAccessToken(AccessTokenEntityInterface $accessToken)
    {
        $this->link('relatedAccessToken', $accessToken);
    }
}