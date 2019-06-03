<?php

namespace oauth\widgets;

use common\helpers\Html;
use common\models\activeRecord\Permission;
use common\objects\ActionColumn;
use common\widgets\GridView;
use oauth\components\repository\ScopeRepository;
use oauth\models\activeRecord\Client as OAuthActiveRecordClient;
use oauth\models\activeRecord\User as OAuthActiveRecordUser;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;

/**
 * Class AuthorizedClientsGridView
 * @package oauth\widgets
 */
class AuthorizedClientsGridView extends GridView
{
    /** @var ScopeRepository */
    public $scopeRepository;

    /** @var OAuthActiveRecordUser */
    public $targetUser;

    public function init()
    {
        if (!$this->scopeRepository instanceof ScopeRepository) {
            throw new InvalidConfigException('ScopeRepository must be instance of ' . ScopeRepository::class);
        }

        if (!$this->targetUser instanceof OAuthActiveRecordUser) {
            throw new InvalidConfigException('targetUser must be instance of ' . OAuthActiveRecordUser::class);
        }

        $this->columns = [
            'name',
            [
                'label' => \Yii::t('app', 'Allowed scopes'),
                'value' => function(OAuthActiveRecordClient $client) {
                    return Html::ul(ArrayHelper::getColumn($this->scopeRepository->getAuthorizedScopesForUserAndClient($this->targetUser, $client), 'name'));
                },
                'format' => 'html'
            ],
            [
                'class' => ActionColumn::class,
                'template' => '{revoke}',
                'buttons' => [
                    'revoke' => function ($url, OAuthActiveRecordClient $model, $key) {
                        $title = \Yii::t('app', 'Remove app');
                        $options = [
                            'title' => $title,
                            'aria-label' => $title,
                            'data-pjax' => '0',
                            'data-method' => 'delete',
                            'data-confirm' => \Yii::t('app', 'Are you sure you want to remove {clientName}?', ['clientName' => $model->name])
                        ];
                        return Html::a(Html::icon(Html::ICON_DELETE), ['/users/revoke-client', 'id' => $this->targetUser->id, 'clientId' => $model->id], $options);
                    }
                ],
                'visibleButtons' => [
                    'update' => function($model, $key, $index) {
                        return \Yii::$app->user->can(Permission::PERMISSION_WRITE, $this->targetUser);
                    },
                ]
            ]
        ];
        return parent::init();
    }
}