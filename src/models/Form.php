<?php

namespace WolfpackIT\oauth\models;

use yii\base\Model;

/**
 * Class Form
 * @package WolfpackIT\oauth\models
 */
abstract class Form extends Model
{
    /**
     * @var boolean|null
     */
    protected $runResult;

    /**
     * @return bool|null
     */
    public function getRunResult(): ?bool
    {
        return $this->runResult;
    }

    /**
     * @return bool
     */
    public function run(): bool
    {
        $this->runResult = $this->runInternal();
        return $this->runResult;
    }

    /**
     * The form model should implement a run function that can be used in the controller
     *
     * @return bool
     */
    abstract protected function runInternal(): bool;
}