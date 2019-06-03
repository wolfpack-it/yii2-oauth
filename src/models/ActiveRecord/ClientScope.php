<?php

namespace WolfpackIT\oauth\models\activeRecord;

use oauth\models\ActiveRecord;
use oauth\queries\activeQuery\ClientQuery;

/**
 * Class ClientScope
 * @package oauth\models\activeRecord
 *
 * @property int $id
 * @property int $client_id
 * @property-read Client $client
 * @property int $scope_id
 * @property-read Scope $scope
 * @property boolean $is_default
 */
class ClientScope extends ActiveRecord
{
    /**
     * @return ClientQuery
     */
    public function getClient(): ClientQuery
    {
        return $this->hasOne(Client::class, ['id' => 'client_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getScope()
    {
        return $this->hasOne(Scope::class, ['id' => 'scope_id']);
    }
}