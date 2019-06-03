<?php

namespace oauth\models\form\clients;

use oauth\models\activeRecord\Client;
use oauth\models\activeRecord\ClientRedirect;
use oauth\models\Form;
use yii\helpers\ArrayHelper;
use yii\validators\DefaultValueValidator;
use yii\validators\RequiredValidator;
use yii\validators\UrlValidator;

/**
 * Class Redirects
 * @package oauth\models\form\clients
 */
class Redirects extends Form
{
    public $redirects = [];

    private $client;
    private $_setRedirects;

    /**
     * Redirects constructor.
     * @param Client $client
     * @param array $config
     */
    public function __construct(Client $client, array $config = [])
    {
        $this->client = $client;
        $this->redirects = $this->_setRedirects = ArrayHelper::getColumn($client->clientRedirects, 'redirect_uri');
        parent::__construct($config);
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'redirects' => \Yii::t('app', 'Redirects')
        ];
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            [['redirects'], RequiredValidator::class],
            [['redirects'], DefaultValueValidator::class, 'value' => []],
            [['redirects'], function ($attribute, $params, $validator) {
                $validator = new UrlValidator();
                foreach ($this->redirects as $index => $redirect) {
                    if (!$validator->validate($redirect)) {
                        $this->addError($attribute . "[{$index}]", \Yii::t('app', 'Please enter a valid url.'));
                    }
                }
            }]
        ];
    }

    /**
     * @return bool
     * @throws \yii\db\Exception
     */
    public function runInternal(): bool
    {
        if ($result = $this->validate()) {
            $transaction = $this->client::getDb()->beginTransaction();
            $transactionLevel = $transaction->level;

            try {
                //Remove old redirects
                $redirectsToRemove = array_diff($this->_setRedirects, $this->redirects);
                if (!empty($redirectsToRemove)) {
                    $result &= 0 < ClientRedirect::deleteAll(
                            ['client_id' => $this->client->id, 'redirect_uri' => $redirectsToRemove]
                        );
                }

                //Add new redirects
                $redirectsToAdd = array_diff($this->redirects, $this->_setRedirects);
                foreach ($redirectsToAdd as $redirectsToAdd) {
                    $clientRedirect = new ClientRedirect([
                        'client_id' => $this->client->id,
                        'redirect_uri' => $redirectsToAdd
                    ]);
                    $result &= $clientRedirect->save();
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