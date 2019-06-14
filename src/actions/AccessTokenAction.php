<?php

namespace WolfpackIT\oauth\actions;

use League\OAuth2\Server\Exception\OAuthServerException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use WolfpackIT\oauth\components\AuthorizationServer;
use WolfpackIT\oauth\components\Request;
use WolfpackIT\oauth\components\Response;
use WolfpackIT\oauth\Module;
use yii\base\Action;
use yii\base\InvalidConfigException;
use yii\web\Request as YiiRequest;
use yii\web\Response as YiiResponse;

/**
 * Class AccessTokenAction
 * @package WolfpackIT\oauth\actions
 */
class AccessTokenAction extends Action
{
    /**
     * @var AuthorizationServer
     */
    public $authorizationServer;

    public function init()
    {
        if (is_null($this->authorizationServer) && $this->controller->module instanceof Module) {
            /** @var Module $module */
            $module = $this->controller->module;

            $this->authorizationServer = $module->get($module->authorizationServerComponent);
        }

        parent::init();
    }

    /**
     * Required params to send: see https://oauth2.thephpleague.com/authorization-server/
     *
     * @param YiiRequest $request
     * @param YiiResponse $response
     * @return \Exception|OAuthServerException|mixed|ResponseInterface
     * @throws InvalidConfigException
     */
    public function run(
        YiiRequest $request,
        YiiResponse $response
    ) {
        $request = \Yii::createObject(Request::class, [$request]);
        $response = \Yii::createObject(Response::class, [$response]);

        if (!$request instanceof ServerRequestInterface) {
            throw new InvalidConfigException('The request class for the module must implement ' . ServerRequestInterface::class . ', use can use ' . Request::class . ' via DI.');
        }

        if (!$response instanceof ResponseInterface) {
            throw new InvalidConfigException('The response class for the module must implement ' . ResponseInterface::class . ', use can use ' . Response::class . ' via DI.');
        }

        try {
            $this->authorizationServer->respondToAccessTokenRequest($request, $response);
            //Since the default response format is json, json decode it so content negotiation works
            return json_decode($response->getResponse()->content);
        } catch (OAuthServerException $exception) {
            return $exception->generateHttpResponse($response);
        } catch (\Exception $exception) {
            return $exception;
        }
    }
}