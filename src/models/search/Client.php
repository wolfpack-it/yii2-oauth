<?php

namespace WolfpackIT\oauth\models\search;

use WolfpackIT\oauth\models\activeRecord\Client as ActiveRecordClient;
use WolfpackIT\oauth\models\Search;
use WolfpackIT\oauth\queries\activeQuery\ClientQuery;
use yii\data\ActiveDataProvider;
use yii\data\DataProviderInterface;
use yii\validators\RangeValidator;
use yii\validators\StringValidator;

/**
 * Class Client
 * @package WolfpackIT\oauth\models\search
 */
class Client extends Search
{
    /**
     * @var int[]
     */
    public $ids;

    /**
     * @var string
     */
    public $modelClass = ActiveRecordClient::class;

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
     * @return DataProviderInterface
     */
    protected function getBaseDataProvider(): DataProviderInterface
    {
        return new ActiveDataProvider([
            'query' => $this->modelClass::find(),
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

        // No filter where since we want to limit the possible results to the given ids
        if (!is_null($this->ids)) {
            $query->andWhere(['id' => $this->ids]);
        }

        //Check whether name is empty since the andFilterWhere won't detect it since we are adding the %
        if (!$this->showDeleted) {
            $query->notDeleted();
        }
        if (!empty($this->name)) {
            $query->andWhere(['like', 'name', $this->name]);
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