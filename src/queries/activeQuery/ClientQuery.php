<?php

namespace WolfpackIT\oauth\queries\activeQuery;

use WolfpackIT\oauth\models\activeRecord\Client;
use WolfpackIT\oauth\queries\ActiveQuery;

/**
 * Class ClientQuery
 * @package WolfpackIT\oauth\queries\activeQuery
 */
class ClientQuery extends ActiveQuery
{
    /**
     * @return ClientQuery
     */
    public function active(): ClientQuery
    {
        return $this->andWhere(['status' => Client::STATUS_ACTIVE]);
    }

    /**
     * @return ClientQuery
     */
    public function notDeleted(): ClientQuery
    {
        return $this->andWhere(['deleted_at' => null]);
    }
}