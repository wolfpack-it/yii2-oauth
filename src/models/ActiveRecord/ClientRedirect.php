<?php

namespace WolfpackIT\oauth\models\activeRecord;

use oauth\models\ActiveRecord;
use oauth\queries\activeQuery\ClientQuery;
use yii\validators\ExistValidator;
use yii\validators\RequiredValidator;
use yii\validators\UrlValidator;

/**
 * Class ClientRedirect
 * @package oauth\models\activeRecord
 *
 * @property int $id
 * @property int $client_id
 * @property string $redirect_uri
 */
class ClientRedirect extends ActiveRecord
{
    /**
     * @return ClientQuery
     */
    public function getClient(): ClientQuery
    {
        return $this->hasOne(Client::class, ['id' => 'client_id'])
            ->inverseOf('clientRedirects')
        ;
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            [['client_id', 'redirect_uri'], RequiredValidator::class],
            [['client_id'], ExistValidator::class, 'targetClass' => Client::class, 'targetAttribute' => 'id'],
            [['redirect_uri'], UrlValidator::class]
        ];
    }
}