<?php

use common\models\activeRecord\User as ActiveRecordUser;
use oauth\components\repository\ScopeRepository;
use oauth\models\activeRecord\User as OAuthActiveRecordUser;
use oauth\models\search\Client;
use yii\data\DataProviderInterface;
use yii\web\View;

/**
 * @var View $this
 * @var ActiveRecordUser $userModel
 * @var Client $filterModel
 * @var DataProviderInterface $dataProvider
 * @var ScopeRepository $scopeRepository
 */

$this->title = \Yii::t('app', 'My apps');

$oauthUser = OAuthActiveRecordUser::findOne(['id' => $userModel->id]);

echo \oauth\widgets\AuthorizedClientsGridView::widget([
    'filterModel' => $filterModel,
    'dataProvider' => $dataProvider,
    'scopeRepository' => $scopeRepository,
    'targetUser' => $oauthUser
]);