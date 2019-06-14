<?php

namespace WolfpackIT\oauth\models\search;

use WolfpackIT\oauth\interfaces\UserEntityInterface;
use WolfpackIT\oauth\models\Search;
use WolfpackIT\oauth\queries\activeQuery\ClientQuery;
use yii\data\ActiveDataProvider;
use yii\data\DataProviderInterface;
use yii\validators\StringValidator;

/**
 * Class User
 * @package WolfpackIT\oauth\models\search
 */
class User extends Search
{
    /**
     * @var string|UserEntityInterface
     */
    public $modelClass;

    /**
     * @var string
     */
    public $search;

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

        if (!empty($this->search)) {
            $query->andWhere(['like', (new $this->modelClass())->displayAttribute(), $this->search]);
        }

        return $dataProvider;
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            [['search'], StringValidator::class],
        ];
    }
}