<?php

namespace WolfpackIT\oauth\actions;

use WolfpackIT\oauth\components\AuthorizationServer;
use WolfpackIT\oauth\Module;
use yii\base\Action;

/**
 * Class OAuthAction
 * @package WolfpackIT\oauth\actions
 */
class OAuthAction extends Action
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
}