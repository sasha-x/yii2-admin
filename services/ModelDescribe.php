<?php

namespace app\modules\admin\services;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;

/**
 * Class ModelDescribe
 * Service to present some description of current model object or class
 */
class ModelDescribe extends Model
{
    public $model;

    protected $modelClass;
    protected $pk;
    protected $safeAttrs;

    /**
     * constructor.
     *
     * @param string $modelClass
     */
    public function __construct($modelClass, $scenario = null)
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

    /**
     * @param string $model
     * @param string|null   $scenario
     */
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

    /**
     * @param bool $withPk
     * @param bool $withType
     *
     * @return string[]
     */
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
}
