<?php

namespace oauth\models\search;

use common\components\dataProviders\FilteredActiveDataProvider;
use common\components\User as UserComponent;
use oauth\models\activeRecord\Client as ActiveRecordClient;
use oauth\models\activeRecord\Permission;
use oauth\models\Search;
use oauth\queries\activeQuery\ClientQuery;
use yii\data\DataProviderInterface;
use yii\validators\RangeValidator;
use yii\validators\StringValidator;

/**
 * Class Client
 * @package oauth\models\search
 */
class Client extends Search
{
    public $name;
    public $status;

    public $showDeleted = false;

    /** @var UserComponent */
    protected $userComponent;

    public function __construct(UserComponent $userComponent, array $config = [])
    {
        $this->userComponent = $userComponent;
        parent::__construct($config);
    }

    protected function getBaseDataProvider(): DataProviderInterface
    {
        return new FilteredActiveDataProvider([
            'query' => ActiveRecordClient::find(),
            'filter' => function(ActiveRecordClient $client) {
                return $this->userComponent->can(Permission::PERMISSION_LIST, $client);
            }
        ]);
    }

    /**
     * @param FilteredActiveDataProvider $dataProvider
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

    public function rules(): array
    {
        return [
            [['name'], StringValidator::class],
            [['status'], RangeValidator::class, 'range' => array_keys($this->statusOptions())]
        ];
    }

    public function statusOptions(): array
    {
        $client = new ActiveRecordClient();
        return $client->statusOptions();
    }
}