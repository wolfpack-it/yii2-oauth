<?php

namespace WolfpackIT\oauth\models\activeRecord;

use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Entities\Traits\AccessTokenTrait;
use WolfpackIT\oauth\models\ActiveRecord;
use WolfpackIT\oauth\queries\activeQuery\AccessTokenQuery;
use WolfpackIT\oauth\traits\ExpirableTrait;
use WolfpackIT\oauth\traits\IdentifiableTrait;

/**
 * Class AccessToken
 * @package WolfpackIT\oauth\models\activeRecord
 *
 * @property int $id
 * @property int $client_id
 * @property-read Client $relatedClient
 * @property int $user_id
 * @property string $identifier
 * @property int $expired_at
 * @property int $status
 * @property-read Scope[] $grantedScopes
 */
class AccessToken
    extends ActiveRecord
    implements AccessTokenEntityInterface
{
    use AccessTokenTrait;
    use ExpirableTrait;
    use IdentifiableTrait;

    const STATUS_CREATION = -1;
    const STATUS_ENABLED = 1;
    const STATUS_REVOKED = 0;

    /**
     * @var string
     */
    protected $clientClass = Client::class;

    /**
     * @var string
     */
    protected $scopeClass = Scope::class;

    /**
     * @param Scope $scope
     */
    public function addScope(ScopeEntityInterface $scope): void
    {
        try {
            $this->link('grantedScopes', $scope);
        } catch (IntegrityException $e) {
            //In case the link already exists
        }
    }

    /**
     * Ignore the invalidConfigException
     *
     * @return AccessTokenQuery
     */
    public static function find()
    {
        return \Yii::createObject(AccessTokenQuery::class, [get_called_class()]);
    }

    /**
     * @return ClientEntityInterface
     */
    public function getClient(): ClientEntityInterface
    {
        return $this->relatedClient;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGrantedScopes()
    {
        return $this->hasMany($this->scopeClass, ['id' => 'scope_id'])
            ->viaTable('{{%access_token_scope}}', ['access_token_id' => 'id'])
        ;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRelatedClient()
    {
        return $this->hasOne($this->clientClass, ['id' => 'client_id']);
    }

    /**
     * @return Scope[]
     */
    public function getScopes(): array
    {
        return $this->grantedScopes;
    }

    /**
     * @return int
     */
    public function getUserIdentifier(): ?int
    {
        return $this->user_id;
    }

    /**
     * @param Client $client
     */
    public function setClient(ClientEntityInterface $client): void
    {
        $this->link('relatedClient', $client);
    }

    /**
     * @param int|null|string $identifier
     */
    public function setUserIdentifier($identifier): void
    {
        $this->user_id = $identifier;
    }
}
