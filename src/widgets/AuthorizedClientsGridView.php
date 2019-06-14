<?php

namespace WolfpackIT\oauth\widgets;

use kartik\icons\Icon;
use WolfpackIT\oauth\components\repository\ScopeRepository;
use WolfpackIT\oauth\interfaces\UserEntityInterface;
use WolfpackIT\oauth\models\activeRecord\Client;
use WolfpackIT\oauth\Module;
use yii\base\InvalidConfigException;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * Class AuthorizedClientsGridView
 * @package oauth\widgets
 */
class AuthorizedClientsGridView extends GridView
{
    /**
     * @var Module
     */
    protected $module;

    /**
     * @var ScopeRepository
     */
    public $scopeRepository;

    /**
     * @var UserEntityInterface
     */
    public $targetUser;

    public function init()
    {
        if (!$this->scopeRepository instanceof ScopeRepository) {
            throw new InvalidConfigException('ScopeRepository must be instance of ' . ScopeRepository::class);
        }

        if (!$this->targetUser instanceof UserEntityInterface) {
            throw new InvalidConfigException('targetUser must be instance of ' . UserEntityInterface::class);
        }

        $this->module = Module::getInstance();

        $this->columns = [
            'name',
            [
                'label' => \Yii::t('oauth', 'Allowed scopes'),
                'value' => function(Client $client) {
                    return Html::ul(ArrayHelper::getColumn($this->scopeRepository->getAuthorizedScopesForUserAndClient($this->targetUser, $client), 'name'));
                },
                'format' => 'html'
            ],
            [
                'class' => ActionColumn::class,
                'template' => '{revoke}',
                'contentOptions' => [
                    'class' => ['text-center']
                ],
                'buttons' => [
                    'revoke' => function ($url, Client $model, $key) {
                        $title = \Yii::t('oauth', 'Remove app');
                        $options = [
                            'title' => $title,
                            'aria-label' => $title,
                            'data-pjax' => '0',
                            'data-method' => 'delete',
                            'data-confirm' => \Yii::t('oauth', 'Are you sure you want to remove {clientName}?', ['clientName' => $model->name])
                        ];
                        return Html::a(Icon::show('trash'), ['users/revoke-client', 'id' => $this->targetUser->getId(), 'clientId' => $model->id], $options);
                    }
                ],
                'visibleButtons' => [
                    'update' => function($model, $key, $index) {
                        return $this->module->user->can($this->module->userWritePermission, $this->targetUser);
                    },
                ]
            ]
        ];
        return parent::init();
    }
}