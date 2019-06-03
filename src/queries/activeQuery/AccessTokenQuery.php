<?php

namespace WolfpackIT\oauth\queries\activeQuery;

use WolfpackIT\oauth\models\activeRecord\AccessToken;
use WolfpackIT\oauth\queries\ActiveQuery;
use yii\db\Expression;

/**
 * Class AccessTokenQuery
 * @package WolfpackIT\oauth\queries\activeQuery
 */
class AccessTokenQuery extends ActiveQuery
{
    /**
     * @return self
     */
    public function active(): self
    {
        return $this
            ->andWhere(['status' => AccessToken::STATUS_ENABLED])
            ->andWhere(['>', 'expired_at', new Expression('NOW()')]);
    }
}