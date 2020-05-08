<?php

namespace sasha_x\admin\controllers;

use sasha_x\admin\services\ModelDescribe;
use Yii;
use yii\base\InvalidRouteException;
use yii\db\ActiveRecord;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\helpers\StringHelper;
use yii\web\ForbiddenHttpException;

/**
 * Default controller for the `admin` module
 */
class AdminController extends Controller
{
    public $layout = 'main';

    /** @var string */
    protected $modelSlug;

    protected $modelTitle;

    /** @var ModelDescribe */
    protected $modelDesc;

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                    'truncate' => ['POST'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ]);
    }

    protected function checkAccess($action)
    {
        $isAdmin = Yii::$app->user->identity->isAdmin();

        if (!$isAdmin) {
            throw new ForbiddenHttpException("Admin-only area");
        }
        return true;
    }

    protected function getModelClassList()
    {
        return $this->module->models;
    }

    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }
        $this->checkAccess($action);

        $modelSlug = Yii::$app->request->get('model');
        if (empty($modelSlug)) {
            //probably it is module start page, or incorrect route
            $this->redirectFirst();
            return false;
        }

        $modelClass = $this->findModelClass($modelSlug);
        if (empty($modelClass)) {
            throw new InvalidRouteException("Model $modelSlug is not configured to use here");
        }
        $this->modelTitle = Inflector::camel2words(Inflector::id2camel($modelSlug));
        $this->modelDesc = new ModelDescribe($modelClass, $action->id);

        $this->modelSlug = $modelSlug;

        return true;
    }

    protected function getModelMap($shortNames = true)
    {
        $map = [];
        foreach ($this->getModelClassList() as $model) {
            $basename = StringHelper::basename($model);
            $slug = Inflector::camel2id($basename);
            $map[$slug] = ($shortNames) ? $basename : $model;
        }
        return $map;
    }

    protected function findModelClass($modelSlug)
    {
        foreach ($this->getModelClassList() as $model) {
            $slug = Inflector::camel2id(StringHelper::basename($model));
            if ($slug == $modelSlug) {
                return $model;
            }
        }
        return null;
    }

    //default route
    protected function redirectFirst()
    {
        $moduleId = $this->module->id;
        $firstModelSlug = key($this->getModelMap(false));

        return $this->redirect("$moduleId/$firstModelSlug/index");
    }

    /**
     * Lists all models.
     *
     * @return string
     */
    public function actionIndex($model)
    {
        $modelDesc = $this->modelDesc;
        $dataProvider = $modelDesc->search(Yii::$app->request->queryParams);
        
        return $this->render('index', [
            'searchModel' => $modelDesc->searchModel,
            'dataProvider' => $dataProvider,
            'columns' => $modelDesc->getColumns(true, true),
        ]);
    }

    /**
     * Creates a new model.
     *
     * @return mixed
     */
    public function actionCreate()
    {
        $model = $this->modelDesc->newModel();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $id = $model->id;

            $msg = $this->modelTitle . " #$id created";
            Yii::$app->getSession()->setFlash('success', Yii::t('app', $msg));
            return $this->redirect('index');
        }

        return $this->render('create', [
            'model' => $model,
            'columns' => $this->modelDesc->getColumns(false, true),
        ]);
    }

    /**
     * Displays a single model.
     *
     * @param integer $id
     *
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($model, $id)
    {
        return $this->render('view', [
            'model' => $this->modelDesc->findModel($id, 'view'),
            'columns' => $this->modelDesc->getColumns(true, true),
        ]);
    }


    /**
     * Updates an existing model.
     *
     * @param integer $id
     *
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->modelDesc->findModel($id, 'update');

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $msg = $this->modelTitle . " #$id updated";
            Yii::$app->getSession()->setFlash('success', Yii::t('app', $msg));
            return $this->redirect('index');    //['view', 'id' => $model->id]
        }

        return $this->render('update', [
            'model' => $model,
            'columns' => $this->modelDesc->getColumns(false, true),
        ]);
    }


    /**
     * Deletes an existing model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @param integer $id
     *
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    //TODO: check results, show errors
    public function actionDelete($id)
    {
        if ($this->modelDesc->findModel($id)->delete()) {
            $msg = $this->modelTitle . " #$id deleted";
            Yii::$app->getSession()->setFlash('success', Yii::t('app', $msg));
        }

        return $this->redirect([$this->modelSlug . "/index"]);
    }

    //Danger zone
    public function actionTruncate()
    {
        if (!$this->module->allowTruncate) {
            return false;
        }

        $table = $this->modelDesc->tableName();
        $ok = Yii::$app->db->createCommand("TRUNCATE `$table`")->execute();

        if ($ok) {
            $msg = "Table `$table` truncated";
            Yii::$app->getSession()->setFlash('success', Yii::t('app', $msg));
        }

        return $this->redirect([$this->modelSlug . "/index"]);
    }

    public function render($view, $params = [])
    {
        $this->view->params['modelSlug'] = $this->modelSlug;
        $this->view->params['leftMenu'] = $this->getModelMap(true);
        $this->view->params['allowTruncate'] = $this->module->allowTruncate;

        $globalParams = [
            'modelSlug' => $this->modelSlug,
            'modelTitle' => $this->modelTitle,
        ];

        $modelDesc = $this->modelDesc;
        if ($modelDesc instanceof ModelDescribe) {
            $globalParams += [
                'elementTitle' => $modelDesc->getTitle(),
                'columns' => $modelDesc->getColumns(),
            ];
        }
        $params = array_merge($globalParams, $params);
        return parent::render($view, $params);
    }
}
