<?php

namespace WolfpackIT\oauth\controllers;

use SamIT\Yii2\Traits\ActionInjectionTrait;
use WolfpackIT\oauth\Module;
use yii\db\ActiveRecord;
use yii\web\Controller as YiiController;
use yii\web\ForbiddenHttpException;

/**
 * Class Controller
 * @package WolfpackIT\oauth\controllers
 */
abstract class Controller extends YiiController
{
    use ActionInjectionTrait;

    /**
     * Find a model and check whether the model exists and the logged in user is allowed to perform the asked permission
     *
     * @param string $class
     * @param int $id
     * @param string|null $permission
     * @return ActiveRecord
     * @throws ForbiddenHttpException
     */
    protected function findModel($class, $id, $permission = null): ActiveRecord
    {
        /** @var Module $module */
        $module = $this->module;

        $permission = $permission ?? $module->defaultPermission;

        $message = \Yii::t('oauth', 'You do not have permission to {permission}', ['permission' => $permission]);

        /** @var ActiveRecord $class */
        $model = $class::findOne(['id' => $id]);
        if ($model !== null) {
            if ($permission !== null && !\Yii::$app->user->can($permission, ['target' => $model])) {
                throw new ForbiddenHttpException($message);
            }
            return $model;
        } else {
            throw new ForbiddenHttpException($message);
        }
    }
}