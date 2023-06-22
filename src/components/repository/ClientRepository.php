<?php

namespace WolfpackIT\oauth\components\repository;

use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use WolfpackIT\oauth\components\Repository;
use WolfpackIT\oauth\interfaces\ClientEntityInterface;
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
     * Get a client.
     *
     * @param string $clientIdentifier The client's identifier
     *
     * @return \League\OAuth2\Server\Entities\ClientEntityInterface|null
     */
    public function getClientEntity($clientIdentifier) : ?Client
    {
        /** @var Client $client */
        return $this->modelClass::find()
            ->active()
            ->notDeleted()
            ->andWhere(['identifier' => $clientIdentifier])
            ->one();
    }

    /**
     * Validate a client's secret.
     *
     * @param string      $clientIdentifier The client's identifier
     * @param null|string $clientSecret     The client's secret (if sent)
     * @param null|string $grantType        The type of grant the client is using (if sent)
     *
     * @return bool
     */
    public function validateClient($clientIdentifier, $clientSecret, $grantType)
    {
        $client = $this->modelClass::find()
           ->active()
           ->notDeleted()
           ->innerJoinWith(['clientGrantTypes' => function(ActiveQuery $query) use ($grantType) {
               return $query->andFilterWhere(['grant_type' => $grantType]);
           }])
           ->andWhere(['identifier' => $clientIdentifier])
           ->one();

        $this->getClientEntity($clientIdentifier);
        if ($client === null)
            return false;
        return (
            !is_null($client)
            && (
                $client->getIsConfidential() === false
                || $client->secretVerify($clientSecret))
        );
    }

    public function init()
    {
        if (!is_subclass_of($this->modelClass, ClientEntityInterface::class)) {
            throw new InvalidConfigException('Model class must implement ' . ClientEntityInterface::class);
        }

        parent::init();
    }
}
