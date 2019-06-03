<?php

use yii\bootstrap4\Nav;
use yii\bootstrap4\NavBar;
use yii\web\View;

/**
 * @var View $this
 */

NavBar::begin([
    'options' => [
        'class' => ['page-footer', 'bg-light', 'navbar-light', 'navbar-expand-xl']
    ],
]);

echo Nav::widget([
    'options' => ['class' => ['ml-auto', 'navbar-nav']],
    'items' => [
        ['label' => '&copy; Wolfpack IT ' . date('Y'), 'encode' => false, 'url' => false],
    ],
]);

NavBar::end();