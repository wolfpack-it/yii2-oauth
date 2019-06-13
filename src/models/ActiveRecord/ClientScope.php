<?php

namespace WolfpackIT\oauth\models\activeRecord;

use WolfpackIT\oauth\models\ActiveRecord;
use WolfpackIT\oauth\queries\activeQuery\ClientQuery;

/**
 * Class ClientScope
 * @package WolfpackIT\oauth\models\activeRecord
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
     * @var string
     */
    protected $clientClass = Client::class;

    /**
     * @var string
     */
    protected $scopeClass = Scope::class;

    /**
     * @return ClientQuery
     */
    public function getClient(): ClientQuery
    {
        return $this->hasOne($this->clientClass, ['id' => 'client_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getScope()
    {
        return $this->hasOne($this->scopeClass, ['id' => 'scope_id']);
    }
}