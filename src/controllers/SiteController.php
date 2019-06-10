<?php

namespace WolfpackIT\oauth\controllers;

use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;

/**
 * Class SiteController
 * @package WolfpackIT\oauth\controllers
 */
class SiteController extends Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * @return array
     */
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