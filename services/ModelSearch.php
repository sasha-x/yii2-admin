<?php

namespace app\modules\admin\services;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;

/**
 * DeviceSearch represents the model behind the search form of `app\models\Device`.
 */
class ModelSearch extends Model
{
    protected $modelClass;
    public $model;
    protected $pk;
    protected $safeAttrs;

    /**
     * DeviceSearch constructor.
     *
     * @param string $modelClass
     */
    public function __construct($modelClass, $scenario = null)    //$config
    {
        parent::__construct();

        $this->pk = current($modelClass::primaryKey());
        /** @var ActiveRecord $model */
        $model = new $modelClass;

        $this->setScenario($model, $scenario);
        $this->safeAttrs = $model->safeAttributes();
        $this->modelClass = $modelClass;
        $this->model = $model;
    }

    public function setScenario($model, $scenario = null)
    {
        if ($scenario == null) {
            return;
        }

        if (!$model instanceof ActiveRecord) {
            throw new \yii\base\InvalidArgumentException();
        }
        $scenarios = $model->scenarios();

        if (isset($scenarios[$scenario])) {
            $model->setScenario($scenario);
        }
    }

    public function getColumns($withPk = false, $withType = false)
    {
        $columns = $this->safeAttrs;
        if ($withPk) {
            array_unshift($columns, $this->pk);
        }

        if ($withType) {
            $columns = $this->fillColumnsWithTypes($columns);
        }

        return $columns;
    }

    protected function fillColumnsWithTypes($columns)
    {
        foreach ($columns as &$column) {
            $type = $this->detectColumnType($column);
            $column = "$column:$type";
        }
        return $columns;
    }

    protected function detectColumnType($column)
    {
        $defaultType = 'text';

        $supportedTypeMap = [
            'boolean' => ['boolean', 'bool'],
            'datetime' => ['datetime', 'date', 'timestamp', 'time'],
            'decimal' => ['decimal', 'money'],
            'integer' => ['smallint', 'integer', 'bigint'],
            //'number' => ['float'],
        ];

        //'email'

        /* May be:
         * char, string, text, boolean, smallint, integer, bigint, float, decimal, datetime,
         * timestamp, time, date, binary, money. */
        $columnSchema = $this->model->getTableSchema()->getColumn($column);
        if (!is_object($columnSchema)) {
            return $defaultType;
        }
        $columnSchemaType = $columnSchema->type;

        if (strpos($column, 'is_') === 0) {
            return 'boolean';
        } elseif (strpos($column, 'email') === 0) {
            return 'email';
        }

        foreach ($supportedTypeMap as $gridType => $sqlTypes) {
            if (in_array($columnSchemaType, $sqlTypes)) {
                return $gridType;
            }
        }

        return $defaultType;
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [[$this->pk], 'integer'],
            [$this->safeAttrs, 'safe'],
        ];
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $modelClass = $this->modelClass;
        $query = $modelClass::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => [$this->pk => SORT_ASC]],
        ]);

        $this->model->load($params);

        /*if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }*/

        // grid filtering conditions
        $query->andFilterWhere([
            $this->pk => $this->model->getPrimaryKey(),
        ]);

        $model = $this->model;
        foreach ($this->safeAttrs as $attr) {
            if (isset($model->$attr)) {
                $query->andFilterWhere(['like', $attr, $model->$attr]);
            }
        }
        return $dataProvider;
    }

    public function getTitle()
    {
        $model = $this->model;
        return $model->name ?? $model->title ?? $model->getPrimaryKey() ?? '';
    }
}
