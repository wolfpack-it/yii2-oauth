<?php

use WolfpackIT\oauth\interfaces\UserEntityInterface;
use yii\bootstrap4\Nav;
use yii\bootstrap4\NavBar;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\User as UserComponent;
use yii\web\View;

/**
 * @var View $this
 */

/** @var UserComponent $userComponent */
$userComponent = \Yii::$app->user;
/** @var UserEntityInterface $identity */
$identity = $userComponent->identity;

NavBar::begin([
    'brandLabel' => \Yii::$app->name . Html::tag('small', \Yii::t('oauth', ' - OAuth module')),
    'brandUrl' => \Yii::$app->homeUrl,
    'options' => [
        'class' => ['bg-dark', 'navbar-dark', 'fixed-top', 'navbar-expand-lg'],
    ],
]);

$items = [];

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
                'label' => \Yii::t('app','Users'),
                'url' => ['users/list']
            ],
            [
                'label' => \Yii::t('app','Clients'),
                'url' => ['clients/list']
            ],
        ]
    );
}

echo Nav::widget([
    'options' => ['class' => 'navbar-nav ml-auto'],
    'items' => $items
]);

NavBar::end();