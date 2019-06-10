<?php

namespace WolfpackIT\oauth\traits;

use yii\db\ActiveRecordInterface;

/**
 * Trait IdentifiableTrait
 * @package WolfpackIT\oauth\traits
 */
trait IdentifiableTrait
{
    /**
     * @return mixed
     */
    public function getIdentifier()
    {
        /** @var ActiveRecordInterface $this */
        return $this->getAttribute('identifier');
    }
    /**
     * @param mixed $identifier
     */
    public function setIdentifier($identifier)
    {
        /** @var ActiveRecordInterface $this */
        $this->setAttribute('identifier', $identifier);
    }
}