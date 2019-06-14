<?php

use kartik\form\ActiveForm;
use kartik\builder\Form;
use WolfpackIT\oauth\models\activeRecord\Client;
use yii\helpers\Html;
use yii\web\View;

/**
 * @var View $this
 * @var Client $model
 */

$this->title =
    $model->isNewRecord
        ? \Yii::t('app', 'Create new client')
        : \Yii::t('app', 'Update client')
;

$form = ActiveForm::begin([
    'method' => $model->isNewRecord ? 'post' : 'put',
]);

echo Form::widget([
    'form' => $form,
    'model' => $model,
    'attributes' => [
        'name' => [
            'type' => Form::INPUT_TEXT
        ],
        'identifier' => [
            'type' => $model->isNewRecord ? Form::INPUT_TEXT : Form::INPUT_STATIC
        ],
        'secret' => [
            'type' => Form::INPUT_STATIC
        ],
        'status' => [
            'type' => Form::INPUT_DROPDOWN_LIST,
            'items' => $model->statusOptions()
        ],
        'actions' => [
            'type' => Form::INPUT_RAW,
            'value' =>
                Html::beginTag('div', ['class' => ['text-right']]) .
                Html::submitButton(\Yii::t('oauth', 'Save'), ['class' => ['btn', 'btn-primary']]) .
                Html::endTag('div')
        ]
    ],
]);

$form::end();

