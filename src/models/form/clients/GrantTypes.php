<?php

namespace WolfpackIT\oauth\models\form\clients;

use WolfpackIT\oauth\models\activeRecord\Client;
use WolfpackIT\oauth\models\activeRecord\ClientGrantType;
use WolfpackIT\oauth\models\Form;
use yii\helpers\ArrayHelper;
use yii\validators\DefaultValueValidator;
use yii\validators\RangeValidator;

/**
 * Class GrantTypes
 * @package WolfpackIT\oauth\models\form
 */
class GrantTypes extends Form
{
    public $grantTypes = [];

    private $client;
    private $_setGrantTypes;

    /**
     * GrantTypes constructor.
     * @param Client $client
     * @param array $config
     */
    public function __construct(Client $client, array $config = [])
    {
        $this->client = $client;
        $this->grantTypes = $this->_setGrantTypes = ArrayHelper::getColumn($client->clientGrantTypes, 'grant_type');
        parent::__construct($config);
    }

    /**
     * @return array
     */
    public function attributeLabels(): array
    {
        return [
            'grantTypes' => \Yii::t('app', 'Grant types'),
        ];
    }

    /**
     * @return array
     */
    public function grantTypeOptions(): array
    {
        return ClientGrantType::grantTypeOptions();
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            [['grantTypes'], RangeValidator::class, 'range' => array_keys($this->grantTypeOptions()), 'allowArray' => true],
            [['grantTypes'], DefaultValueValidator::class, 'value' => []]
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
                //Remove unselected grant types
                $grantTypesToRemove = array_diff($this->_setGrantTypes, $this->grantTypes);
                if (!empty($grantTypesToRemove)) {
                    $result &= 0 < ClientGrantType::deleteAll(
                        ['client_id' => $this->client->id, 'grant_type' => $grantTypesToRemove]
                    );
                }

                //Add added grant types
                $grantTypesToAdd = array_diff($this->grantTypes, $this->_setGrantTypes);
                foreach ($grantTypesToAdd as $grantTypeToAdd) {
                    $clientGrantType = new ClientGrantType([
                        'client_id' => $this->client->id,
                        'grant_type' => $grantTypeToAdd
                    ]);
                    $result &= $clientGrantType->save();
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
}