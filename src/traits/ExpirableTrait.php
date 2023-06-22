<?php

namespace WolfpackIT\oauth\traits;

use yii\db\ActiveRecordInterface;

/**
 * Trait ExpirableTrait
 * @package WolfpackIT\oauth\traits
 */
trait ExpirableTrait
{
    /**
     * @return \DateTime
     */
    public function getExpiryDateTime()
    {
        /** @var ActiveRecordInterface $this */
        return \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $this->getAttribute('expired_at'));
    }

    /**
     * @param \DateTime $dateTime
     */
    public function setExpiryDateTime(\DateTimeImmutable $dateTime): void
    {
        $this->setAttribute('expired_at', $dateTime->format('Y-m-d H:i:s'));
    }
}