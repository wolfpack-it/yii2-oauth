<?php

namespace WolfpackIT\oauth\queries\activeQuery;

use oauth\models\activeRecord\RefreshToken;
use oauth\queries\ActiveQuery;
use yii\db\Expression;

/**
 * Class RefreshTokenQuery
 * @package oauth\queries\activeQuery
 */
class RefreshTokenQuery extends ActiveQuery
{
    /**
     * @return self
     */
    public function active(): self
    {
        return $this
            ->andWhere(['status' => RefreshToken::STATUS_ENABLED])
            ->andWhere(['>', 'expired_at', new Expression('NOW()')]);
    }
}