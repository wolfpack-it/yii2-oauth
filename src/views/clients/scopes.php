<?php

use common\helpers\Html;
use common\widgets\ActiveForm;
use common\widgets\Form;
use oauth\models\activeRecord\Client;
use oauth\models\form\clients\Scopes;
use yii\web\View;

/**
 * @var View $this
 * @var Client $client
 * @var Scopes $model
 */

$this->title = \Yii::t('app','Update scopes of {clientName}', ['clientName' => $client->name]);

echo Html::tag('p', \Yii::t('app', 'Update the set of scopes the client is allowed to ask.'));

$form = ActiveForm::begin([
    'method' => 'put'
]);

echo Form::widget([
    'form' => $form,
    'model' => $model,
    'attributes' => [
        'scopes' => [
            'type' => Form::INPUT_CHECKBOX_LIST,
            'items' => $model->scopeOptions()
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

