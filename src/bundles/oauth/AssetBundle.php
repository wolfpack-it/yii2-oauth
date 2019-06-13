<?php

namespace WolfpackIT\oauth\bundles\oauth;

use yii\bootstrap4\BootstrapAsset;
use yii\web\AssetBundle as YiiAssetBundle;
use yii\web\YiiAsset;

/**
 * Class AssetBundle
 * @package WolfpackIT\oauth\bundles\oauth
 */
class AssetBundle extends YiiAssetBundle
{
    /**
     * @var array
     */
    public $css = [
        'css/site.css'
    ];

    /**
     * @var array
     */
    public $depends = [
        YiiAsset::class,
        BootstrapAsset::class
    ];

    /**
     * @var array
     */
    public $publishOptions = [
        'forceCopy' => YII_ENV_DEV
    ];

    /**
     * @var string
     */
    public $sourcePath = __DIR__ . '/assets';
}