<?php

namespace WolfpackIT\oauth\components\repository;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use WolfpackIT\oauth\components\Repository;
use WolfpackIT\oauth\models\activeRecord\Client;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;

/**
 * Class ClientRepository
 * @package WolfpackIT\oauth\components\repository
 */
class ClientRepository
    extends Repository
    implements ClientRepositoryInterface
{
    /**
     * @var string
     */
    public $modelClass = Client::class;

    /**
     * @param string $clientIdentifier
     * @param null $grantType
     * @param null $clientSecret
     * @param bool $mustValidateSecret
     * @return Client|null
     */
    public function getClientEntity(
        $clientIdentifier,
        $grantType = null,
        $clientSecret = null,
        $mustValidateSecret = true
    ): ?Client {
        /** @var Client $client */
        $client = $this->modelClass::find()
            ->active()
            ->notDeleted()
            ->innerJoinWith(['clientGrantTypes' => function(ActiveQuery $query) use ($grantType) {
                return $query->andFilterWhere(['grant_type' => $grantType]);
            }])
            ->andWhere(['identifier' => $clientIdentifier])
            ->one()
        ;

        if (
            !is_null($client)
            && (
                $mustValidateSecret === false
                || $client->getIsConfidential() === false
                || $client->secretVerify($clientSecret))
        ) {
            return $client;
        }

        return null;
    }

    public function init()
    {
        if (!is_subclass_of($this->modelClass, ClientEntityInterface::class)) {
            throw new InvalidConfigException('Model class must implement ' . ClientEntityInterface::class);
        }

        parent::init();
    }
}