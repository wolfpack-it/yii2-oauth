<?php

namespace oauth\components\repository;

use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use oauth\components\Repository;
use oauth\models\activeRecord\Client;
use yii\db\ActiveQuery;

/**
 * Class ClientRepository
 * @package oauth\components\repository
 */
class ClientRepository
    extends Repository
    implements ClientRepositoryInterface
{
    public function getClientEntity(
        $clientIdentifier,
        $grantType = null,
        $clientSecret = null,
        $mustValidateSecret = true
    ): ?Client {
        $client = Client::find()
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
}