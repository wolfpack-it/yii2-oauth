<?php

namespace WolfpackIT\oauth\components;

use oauth\models\activeRecord\Client;
use yii\filters\auth\HttpBearerAuth;
use yii\web\Request;
use yii\web\Response;
use yii\web\User;

/**
 * Class ClientHttpBearerAuth
 * @package WolfpackIT\oauth\components
 */
class ClientHttpBearerAuth extends HttpBearerAuth
{
    /**
     * @var string
     */
    public $clientClass = Client::class;

    /**
     * @param User $user
     * @param Request $request
     * @param Response $response
     * @return \yii\web\IdentityInterface|null
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\UnauthorizedHttpException
     */
    public function authenticate($user, $request, $response)
    {
        $authHeader = $request->getHeaders()->get($this->header);
        if ($authHeader !== null) {
            if ($this->pattern !== null) {
                if (preg_match($this->pattern, $authHeader, $matches)) {
                    $authHeader = $matches[1];
                } else {
                    return null;
                }
            }

            $identity = $this->clientClass::findIdentityByAccessToken($authHeader, get_class($this));

            if ($identity) {
                $user->login($identity);
            }

            if ($identity === null) {
                $this->challenge($response);
                $this->handleFailure($response);
            }

            return $identity;
        }

        return null;
    }
}