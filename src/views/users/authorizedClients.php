<?php

use WolfpackIT\oauth\components\repository\ScopeRepository;
use WolfpackIT\oauth\interfaces\UserEntityInterface;
use WolfpackIT\oauth\models\activeRecord\Client;
use WolfpackIT\oauth\widgets\AuthorizedClientsGridView;
use yii\data\DataProviderInterface;
use yii\web\View;

/**
 * @var View $this
 * @var UserEntityInterface $userModel
 * @var Client $filterModel
 * @var DataProviderInterface $dataProvider
 * @var ScopeRepository $scopeRepository
 */

$this->title = \Yii::t('oauth', 'Clients for {user}', ['user' => $userModel->{$userModel->displayAttribute()}]);

echo AuthorizedClientsGridView::widget([
    'filterModel' => $filterModel,
    'dataProvider' => $dataProvider,
    'scopeRepository' => $scopeRepository,
    'targetUser' => $userModel
]);