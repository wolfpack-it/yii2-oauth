<?php

namespace WolfpackIT\oauth\models\activeRecord;

use League\OAuth2\Server\Entities\ScopeEntityInterface;
use WolfpackIT\oauth\traits\IdentifiableTrait;
use yii\db\ActiveRecord;

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

    /**
     * @return string
     */
    public function jsonSerialize()
    {
        return $this->getIdentifier();
    }
}