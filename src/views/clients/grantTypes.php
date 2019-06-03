<?php

use common\helpers\Html;
use common\widgets\ActiveForm;
use common\widgets\Form;
use oauth\models\activeRecord\Client;
use oauth\models\form\clients\GrantTypes;
use yii\web\View;

/**
 * @var View $this
 * @var Client $client
 * @var GrantTypes $model
 */

$this->title = \Yii::t('app','Update grant types of {clientName}', ['clientName' => $client->name]);

echo Html::tag('p', \Yii::t('app', 'Update the grant types the client is allowed to use.'));

$form = ActiveForm::begin([
    'method' => 'put'
]);

echo Form::widget([
    'form' => $form,
    'model' => $model,
    'attributes' => [
        'grantTypes' => [
            'type' => Form::INPUT_CHECKBOX_LIST,
            'items' => $model->grantTypeOptions()
        ]
    ],
    'buttons' => [
        [
            'content' => \Yii::t('app', 'Save'),
            'type' => 'submit',
            'class' => ['btn', 'btn-primary']
        ]
    ]
]);

ActiveForm::end();

