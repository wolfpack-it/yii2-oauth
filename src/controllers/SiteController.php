<?php

namespace oauth\controllers;

use oauth\components\AuthorizationServer;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;

/**
 * Class SiteController
 * @package oauth\controllers
 */
class SiteController extends Controller
{
    public function actionIndex(
    )
    {
        return $this->render('index');
    }

    public function behaviors(): array
    {
        return ArrayHelper::merge(
            [
                'access' => [
                    'class' => AccessControl::class,
                    'rules' => [
                        [
                            'allow' => true,
                            'actions' => ['index']
                        ]
                    ]
                ]
            ],
            parent::behaviors()
        );
    }

}