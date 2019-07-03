<?php

namespace WolfpackIT\oauth\models\activeRecord;

use WolfpackIT\oauth\models\ActiveRecord;
use WolfpackIT\oauth\queries\activeQuery\ClientQuery;
use yii\validators\ExistValidator;
use yii\validators\RangeValidator;
use yii\validators\RequiredValidator;

/**
 * Class ClientGrantType
 * @package WolfpackIT\oauth\models\activeRecord
 *
 * @property int $client_id
 * @property-read Client $client
 * @property string $grant_type
 */
class ClientGrantType extends ActiveRecord
{
    const GRANT_TYPE_AUTHORIZATION_CODE = 'authorization_code';
    const GRANT_TYPE_CLIENT_CREDENTIALS = 'client_credentials';
    const GRANT_TYPE_IMPLICIT = 'implicit';
    const GRANT_TYPE_PASSWORD = 'password';
    const GRANT_TYPE_REFRESH_TOKEN = 'refresh_token';

    /**
     * @var string
     */
    protected $clientClass = Client::class;

    /**
     * @return ClientQuery
     */
    public function getClient(): ClientQuery
    {
        return $this->hasOne($this->clientClass, ['id' => 'client_id']);
    }

    /**
     * @return array
     */
    public static function grantTypeOptions(): array
    {
        return [
            self::GRANT_TYPE_AUTHORIZATION_CODE => \Yii::t('app', 'Authorization code'),
            self::GRANT_TYPE_CLIENT_CREDENTIALS => \Yii::t('app', 'Client credentials'),
            self::GRANT_TYPE_IMPLICIT => \Yii::t('app', 'Implicit'),
            self::GRANT_TYPE_PASSWORD => \Yii::t('app', 'Password'),
            self::GRANT_TYPE_REFRESH_TOKEN => \Yii::t('app', 'Refresh token'),
        ];
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            [['client_id', 'grant_type'], RequiredValidator::class],
            [['client_id'], ExistValidator::class, 'targetClass' => Client::class, 'targetAttribute' => 'id'],
            [['grant_type'], RangeValidator::class, 'range' => array_keys(self::grantTypeOptions())]
        ];
    }
}