<?php

namespace WolfpackIT\oauth\models\form\clients;

use WolfpackIT\oauth\models\activeRecord\Client;
use WolfpackIT\oauth\models\activeRecord\ClientScope;
use WolfpackIT\oauth\models\activeRecord\Scope;
use WolfpackIT\oauth\models\Form;
use yii\helpers\ArrayHelper;
use yii\validators\DefaultValueValidator;
use yii\validators\RangeValidator;

/**
 * Class Scopes
 * @package oauth\models\form
 */
class Scopes extends Form
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var int[]
     */
    public $scopes = [];

    /**
     * @var int[]
     */
    protected $_setScopes;

    /**
     * Scopes constructor.
     * @param Client $client
     * @param array $config
     */
    public function __construct(Client $client, array $config = [])
    {
        $this->client = $client;
        $this->scopes = $this->_setScopes = ArrayHelper::getColumn($client->clientScopes, 'id');
        parent::__construct($config);
    }

    /**
     * @return array
     */
    public function attributeLabels(): array
    {
        return [
            'scopes' => \Yii::t('app', 'Scopes'),
        ];
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            [['scopes'], RangeValidator::class, 'range' => array_keys($this->scopeOptions()), 'allowArray' => true],
            [['scopes'], DefaultValueValidator::class, 'value' => []]
        ];
    }

    /**
     * @return bool
     * @throws \yii\db\Exception
     */
    public function runInternal(): bool
    {
        if($result = $this->validate()) {
            $transaction = $this->client::getDb()->beginTransaction();
            $transactionLevel = $transaction->level;

            try {
                //Remove unselected scopes
                $scopesToRemove = array_diff($this->_setScopes, $this->scopes);
                if (!empty($scopesToRemove)) {
                    $result &= 0 < ClientScope::deleteAll(
                        ['client_id' => $this->client->id, 'scope_id' => $scopesToRemove]
                    );
                }

                //Add added scopes
                $scopesToAdd = array_diff($this->scopes, $this->_setScopes);
                foreach ($scopesToAdd as $scopeToAdd) {
                    $clientScope = new ClientScope([
                        'client_id' => $this->client->id,
                        'scope_id' => $scopeToAdd
                    ]);
                    $result &= $clientScope->save();
                }

                if ($result) {
                    $transaction->commit();
                }
            } finally {
                if ($transaction->isActive && $transaction->level === $transactionLevel) {
                    $transaction->rollBack();
                }
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    public function scopeOptions(): array
    {
        return ArrayHelper::map(
            Scope::find()->select(['id', 'name'])->asArray()->all(),
            'id',
            'name'
        );
    }
}