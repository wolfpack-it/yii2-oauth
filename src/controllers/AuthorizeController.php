<?php

namespace oauth\controllers;

use common\controllers\RestController;
use League\OAuth2\Server\Exception\OAuthServerException;
use oauth\components\AuthorizationServer;
use oauth\components\WebRequest;
use oauth\components\WebResponse;
use yii\rest\OptionsAction;
use yii\web\Session;

class AuthorizeController extends RestController
{
    const SESSION_AUTH_REQUEST = 'auth_request';

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
     * Required params: see https://oauth2.thephpleague.com/authorization-server/auth-code-grant/
     */
    public function actionIndex(
        AuthorizationServer $authorizationServer,
        Session $session,
        WebRequest $request,
        WebResponse $response
    ) {
        try {
            $authRequest = $authorizationServer->validateAuthorizationRequest($request);
            $session->set(self::SESSION_AUTH_REQUEST, $authRequest);
            return $this->redirect(['authorization/login']);
        } catch (OAuthServerException $exception) {
            $response = $exception->generateHttpResponse($response);
            return $response;
        } catch (\Exception $exception) {
            return $exception;
        }
    }

    public function behaviors(): array
    {
        $result = parent::behaviors();
        $result['authenticator']['optional'] = [
            'index',
            'options'
        ];
        return $result;
    }
}