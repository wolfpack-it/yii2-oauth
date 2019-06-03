<?php

namespace WolfpackIT\\models\activeRecord;

use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use oauth\queries\activeQuery\AuthCodeQuery;

/**
 * Class AuthCode
 * @package oauth\models\activeRecord
 *
 * @inheritdoc
 */
class AuthCode
    extends AccessToken
    implements AuthCodeEntityInterface
{
    /**
     * Ignore the invalidConfigException
     *
     * @return AuthCodeQuery
     */
    public static function find()
    {
        return \Yii::createObject(AuthCodeQuery::class, [get_called_class()]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGrantedScopes()
    {
        return $this->hasMany(Scope::class, ['id' => 'scope_id'])
            ->viaTable('{{%auth_code_scope}}', ['auth_code_id' => 'id'])
        ;
    }

    /**
     * @return string
     */
    public function getRedirectUri(): string
    {
        return $this->redirect_uri;
    }

    /**
     * @param string $uri
     */
    public function setRedirectUri($uri): void
    {
        $this->redirect_uri = $uri;
    }
}