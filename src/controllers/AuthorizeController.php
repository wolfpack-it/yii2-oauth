<?php

namespace WolfpackIT\oauth\controllers;

use WolfpackIT\oauth\actions\oauth\AuthorizeAction;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\rest\OptionsAction;
use yii\web\Request;
use yii\web\User as UserComponent;

/**
 * Class AuthorizeController
 * @package WolfpackIT\oauth\controllers
 */
class AuthorizeController extends Controller
{
    public function actionLogout(
        Request $request,
        UserComponent $user
    ) {
        $returnUrl = $user->getReturnUrl();
        if ($request->isDelete) {
            //In this case we do not want to destroy the session so a user can logout during the authorization and continue with another user
            $user->logout(false);
        }
        return $returnUrl ? $this->redirect($returnUrl) : $this->goHome();
    }

    /**
     * @return array
     */
    public function actions(): array
    {
        return [
            'index' => [
                'class' => AuthorizeAction::class
            ],
            'options' => [
                'class' => OptionsAction::class
            ],
        ];
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
                            'actions' => ['index', 'options'],
                            'allow' => true,
                        ],
                        [
                            'actions' => ['logout'],
                            'allow' => true,
                            'roles' => ['@']
                        ]
                    ]
                ],
                'verbs' => [
                    'class' => VerbFilter::class,
                    'actions' => [
                        'logout' => ['DELETE']
                    ]
                ]
            ],
            parent::behaviors()
        );
    }
}