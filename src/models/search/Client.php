<?php

namespace WolfpackIT\oauth\models\search;

use WolfpackIT\oauth\models\activeRecord\Client as ActiveRecordClient;
use WolfpackIT\oauth\models\Search;
use WolfpackIT\oauth\queries\activeQuery\ClientQuery;
use yii\data\ActiveDataProvider;
use yii\data\DataProviderInterface;
use yii\validators\RangeValidator;
use yii\validators\StringValidator;
use yii\web\User;

/**
 * Class Client
 * @package WolfpackIT\oauth\models\search
 */
class Client extends Search
{
    /**
     * @var string
     */
    public $clientClass = ActiveRecordClient::class;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $status;

    /**
     * @var bool
     */
    public $showDeleted = false;

    /**
     * @var User
     */
    protected $user;

    /**
     * Client constructor.
     * @param User $user
     * @param array $config
     */
    public function __construct(User $user, array $config = [])
    {
        $this->user = $user;
        parent::__construct($config);
    }

    /**
     * @return DataProviderInterface
     */
    protected function getBaseDataProvider(): DataProviderInterface
    {
        return new ActiveDataProvider([
            'query' => $this->clientClass::find(),
        ]);
    }

    /**
     * @param ActiveDataProvider $dataProvider
     * @return DataProviderInterface
     */
    protected function internalSearch(DataProviderInterface $dataProvider): DataProviderInterface
    {
        /** @var ClientQuery $query */
        $query = $dataProvider->query;

        //Check whether name is empty since the andFilterWhere won't detect it since we are adding the %
        if (!$this->showDeleted) {
            $query->notDeleted();
        }
        if (!empty($this->name)) {
            $query->andWhere(['like', 'name', $this->name . '%', false]);
        }
        $query->andFilterWhere(['status' => $this->status]);

        return $dataProvider;
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            [['name'], StringValidator::class],
            [['status'], RangeValidator::class, 'range' => array_keys($this->statusOptions())]
        ];
    }

    /**
     * @return array
     */
    public function statusOptions(): array
    {
        $client = new ActiveRecordClient();
        return $client->statusOptions();
    }
}