<?php

namespace sasha_x\admin\services;

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

    /**
     * @var \string[][] As $gridType => $sqlTypes[]
     */
    public $supportedTypeMap = [
        'boolean' => ['boolean', 'bool', 'tinyint'],
        'datetime' => ['datetime', 'date', 'timestamp', 'time'],
        'decimal' => ['decimal', 'money'],
        'integer' => ['smallint', 'integer', 'bigint'],
        //'number' => ['float'],
    ];

    public $defaultType = 'text';
    public $searchModel;

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
        $searchModel = clone $model;
        
        $this->setScenario($model, $scenario);
        $this->safeAttrs = $model->safeAttributes();
        $this->modelClass = $modelClass;
        $this->model = $model;
        $this->searchModel = $searchModel;
    }


    /**
     * Finds the model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param integer $id
     *
     * @return ActiveRecord the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function findModel($id, $scenario = null)
    {
        $modelClass = $this->modelClass;
        /** @var ActiveRecord $model */
        $model = $modelClass::findOne($id);
        if (empty($model)) {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }

        if ($scenario) {
            $this->setScenario($model, $scenario);
        }

        return $model;
    }

    public function newModel()
    {
        $model = new $this->modelClass;
        $this->setScenario($model, 'create');
        return $model;
    }

    public function tableName()
    {
        return $this->modelClass::tableName();
    }

    /**
     * @param string      $model
     * @param string|null $scenario
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
    public function getColumns($withPk = false, $withTypes = false)
    {
        $columns = $this->safeAttrs;
        if ($withPk) {
            array_unshift($columns, $this->pk);
        }

        if ($withTypes) {
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

        $this->searchModel->load($params);

        // grid filtering conditions
        $query->andFilterWhere([
            $this->pk => $this->searchModel->getPrimaryKey(),
        ]);

        $model = $this->searchModel;
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
        if (strpos($column, 'email') === 0) {
            return 'email';
        } elseif (stripos($column, 'password') !== false) {
            return 'password';
        }

        /* May be:
         * char, string, text, boolean, smallint, integer, bigint, float, decimal, datetime,
         * timestamp, time, date, binary, money. */
        $columnSchema = $this->model->getTableSchema()->getColumn($column);
        if (!is_object($columnSchema)) {
            return $this->defaultType;
        }
        $columnSchemaType = $columnSchema->type;

        foreach ($this->supportedTypeMap as $gridType => $sqlTypes) {
            if (in_array($columnSchemaType, $sqlTypes)) {
                return $gridType;
            }
        }

        return $this->defaultType;
    }
}
