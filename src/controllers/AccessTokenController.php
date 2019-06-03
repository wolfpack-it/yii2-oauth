<?php

namespace oauth\controllers;

use common\controllers\RestController;
use League\OAuth2\Server\Exception\OAuthServerException;
use oauth\components\AuthorizationServer;
use oauth\components\WebRequest;
use oauth\components\WebResponse;
use yii\rest\OptionsAction;

class AccessTokenController extends RestController
{
    /**
     * @return array
     */
    public function actions(): array
    {
        return [
            'options' => [
                'class' => OptionsAction::class
            ]
        ];
    }

    /**
     * Required params to send: see https://oauth2.thephpleague.com/authorization-server/
     */
    public function actionIndex(
        AuthorizationServer $authorizationServer,
        WebRequest $request,
        WebResponse $response
    ) {
        try {
            $authorizationServer->respondToAccessTokenRequest($request, $response);
            //Since the default response format is json, json decode it so content negotiation works
            return json_decode($response->content);
        } catch (OAuthServerException $exception) {
            return $exception->generateHttpResponse($response);
        } catch (\Exception $exception) {
            return $exception;
        }
    }

    public function behaviors(): array
    {
        $result = parent::behaviors();
        $result['authenticator']['optional'] = [
            'index',
        ];
        return $result;
    }
}