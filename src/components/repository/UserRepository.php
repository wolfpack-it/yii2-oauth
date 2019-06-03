<?php

namespace oauth\components\repository;

use common\models\form\session\Create;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\UserEntityInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use WolfpackIT\oauth\components\Repository;
use WolfpackIT\oauth\models\activeRecord\User;
use SamIT\Yii2\UrlSigner\UrlSigner;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * Class UserRepository
 * @package oauth\components\repository
 */
class UserRepository
    extends Repository
    implements UserRepositoryInterface
{
    /**
     * TODO implement check for $grantType
     *
     * @param string $username
     * @param string $password
     * @param string $grantType
     * @param ClientEntityInterface $clientEntity
     * @return UserEntityInterface|null
     * @throws InvalidConfigException
     */
    public function getUserEntityByUserCredentials(
        $username,
        $password,
        $grantType,
        ClientEntityInterface $clientEntity
    ): ?UserEntityInterface {
        $sessionCreateModel = new Create(\Yii::$app->user);
        $sessionCreateModel->username = $username;
        $sessionCreateModel->password = $password;

        //The case it was an internal request
        if ($result = $this->getUserFromSignedRequest(
            \Yii::$app->request->bodyParams,
            \Yii::createObject(UrlSigner::class)->secret,
            User::getPasswordAttribute()
        )) {
            return $result;
        }

        return
            $sessionCreateModel->validate()
                ? User::findOne(['id' => $sessionCreateModel->getUser()->id])
                : null
        ;
    }

    /**
     * @param array $data
     * @param string $secret
     * @param string $signatureAttribute
     * @return User|null
     * @throws InvalidConfigException
     */
    protected function getUserFromSignedRequest(array $data, string $secret, string $signatureAttribute): ?User
    {
        $signature = ArrayHelper::remove($data, $signatureAttribute);

        $sortedData = $data;
        ksort($sortedData);

        $newSignature = \Yii::$app->security->hashData(Json::encode($sortedData), $secret);

        if ($newSignature === $signature) {
            return User::findOne([User::getUsernameAttribute() => $data['username']]);
        }

        return null;
    }

    /**
     * @param array $data
     * @param string $secret
     * @param string $signatureAttribute
     * @return array
     * @throws InvalidConfigException
     */
    public function signRequestData(array $data, string $secret, string $signatureAttribute): array
    {
        $sortedData = $data;
        ksort($sortedData);

        $signature = \Yii::$app->security->hashData(Json::encode($sortedData), $secret);
        $data[$signatureAttribute] = $signature;

        return $data;
    }
}