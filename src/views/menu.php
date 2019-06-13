<?php

use yii\bootstrap4\Nav;
use yii\bootstrap4\NavBar;
use yii\helpers\ArrayHelper;
use yii\web\User as UserComponent;
use yii\web\View;

/**
 * @var View $this
 */

/** @var UserComponent $userComponent */
$userComponent = \Yii::$app->user;

NavBar::begin([
    'brandLabel' => \Yii::$app->name,
    'brandUrl' => \Yii::$app->homeUrl,
    'options' => [
        'class' => ['bg-dark', 'navbar-dark', 'fixed-top', 'navbar-expand-lg'],
    ],
]);

$items = [
    ['label' => \Yii::t( 'app', 'Home'), 'url' => \Yii::$app->homeUrl],
];

if ($userComponent->isGuest) {
    $items = ArrayHelper::merge(
        $items,
        [
            ['label' => \Yii::t('app', 'Login'), 'url' => $userComponent->loginUrl],
        ]
    );
} else {
    $items = ArrayHelper::merge(
        $items,
        [
            [
                'label' => \Yii::t('app','Clients'),
                'url' => ['/clients']
            ],
            [
                'label' => $userComponent->identity->{\common\models\activeRecord\User::getUsernameAttribute()},
                'items' => [
                    [
                        'label' => \Yii::t('app', 'Logout'),
                        'url' => ['/session/delete'],
                        'linkOptions' => [
                            'data-method' => 'delete'
                        ]
                    ]
                ]
            ],
        ]
    );
}

echo Nav::widget([
    'options' => ['class' => 'navbar-nav ml-auto'],
    'items' => $items
]);

NavBar::end();