<?php

namespace WolfpackIT\oauth\models\activeRecord;

use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use WolfpackIT\oauth\queries\activeQuery\AuthCodeQuery;
use yii\db\ActiveQuery;

/**
 * Class AuthCode
 * @package WolfpackIT\oauth\models\activeRecord
 *
 * @property string $redirectUri
 */
class AuthCode
    extends AccessToken
    implements AuthCodeEntityInterface
{
    /**
     * @var string
     */
    protected $scopeClass = Scope::class;

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
     * @return ActiveQuery
     */
    public function getGrantedScopes()
    {
        return $this->hasMany($this->scopeClass, ['id' => 'scope_id'])
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