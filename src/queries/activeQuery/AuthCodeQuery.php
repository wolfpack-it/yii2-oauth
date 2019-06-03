<?php

namespace WolfpackIT\oauth\queries\activeQuery;

use WolfpackIT\oauth\models\activeRecord\AuthCode;
use WolfpackIT\oauth\queries\ActiveQuery;
use yii\db\Expression;

/**
 * Class AuthCodeQuery
 * @package WolfpackIT\oauth\queries\activeQuery
 */
class AuthCodeQuery extends ActiveQuery
{
    /**
     * @return self
     */
    public function active(): self
    {
        return $this
            ->andWhere(['status' => AuthCode::STATUS_ENABLED])
            ->andWhere(['>', 'expired_at', new Expression('NOW()')]);
    }
}