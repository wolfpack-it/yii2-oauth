<?php

namespace WolfpackIT\oauth\models\activeRecord;

use League\OAuth2\Server\Entities\ScopeEntityInterface;
use WolfpackIT\oauth\models\ActiveRecord;
use WolfpackIT\oauth\traits\IdentifiableTrait;

/**
 * Class Scope
 * @package WolfpackIT\oauth\models\activeRecord
 *
 * @property int $id
 * @property string $identifier
 * @property string $name
 */
class Scope
    extends ActiveRecord
    implements ScopeEntityInterface
{
    use IdentifiableTrait;

    public function jsonSerialize()
    {
        return $this->getIdentifier();
    }
}