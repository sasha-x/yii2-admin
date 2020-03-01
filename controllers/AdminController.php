<?php

namespace app\modules\admin\controllers;

use app\modules\admin\services\ModelDescribe;
use Yii;
use yii\base\InvalidRouteException;
use yii\db\ActiveRecord;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * Default controller for the `admin` module
 */
class AdminController extends Controller
{
    public $layout = 'main';

    /**
     * @var array
     * Need in layout
     */
    public $modelMap;

    /** @var string */
    public $modelSlug;

    /** @var string */
    protected $modelClass;
    /** @var ModelDescribe */
    protected $searchModel;

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

    public function beforeAction($action)
    {
        $r = parent::beforeAction($action);
        if (!$r) {
            return $r;
        }

        $isAdmin = Yii::$app->user->identity->isAdmin();

        if (!$isAdmin) {
            throw new \yii\web\ForbiddenHttpException("Admin-only area");
        }

        $modelSlug = Yii::$app->request->get('model');
        //fixit: get it shortly
        $this->modelMap = Yii::$app->controller->module->modelMap;
        $this->modelClass = $this->modelMap[$modelSlug] ?? null;
        if (empty($this->modelClass)) {
            throw new InvalidRouteException("Model $modelSlug is not configured to use here");
        }
        $this->modelSlug = $modelSlug;
        $this->searchModel = new ModelDescribe($this->modelClass, $action->id);

        return true;
    }

    /**
     * Lists all models.
     *
     * @return string
     */
    public function actionIndex($model)
    {
        $searchModel = $this->searchModel;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel->model,
            'dataProvider' => $dataProvider,
            'columns' => $searchModel->getColumns(true, true),
        ]);
    }

    /**
     * Creates a new model.
     *
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new $this->modelClass;
        $this->searchModel->setScenario($model, 'create');

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $id = $model->id;
            Yii::$app->getSession()->setFlash('success', Yii::t('app', $this->modelClass . ": $id created"));
            return $this->redirect('index');        //['view', 'id' => $model->id]
        }

        return $this->render('create', [
            'model' => $model,
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
            'model' => $this->findModel($id),
            'columns' => $this->searchModel->getColumns(true, true),
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
        $model = $this->findModel($id);
        $this->searchModel->setScenario($model, 'update');
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->getSession()->setFlash('success', Yii::t('app', $this->modelClass . ": $id updated"));
            return $this->redirect('index');    //['view', 'id' => $model->id]
        }

        return $this->render('update', [
            'model' => $model,
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
        if ($this->findModel($id)->delete()) {
            Yii::$app->getSession()->setFlash('success', Yii::t('app', $this->modelClass . ": $id deleted"));
        }

        return $this->redirect([$this->modelSlug . "/index"]);
    }

    public function render($view, $params = [])
    {
        $params = array_merge([
            'modelSlug' => $this->modelSlug,
            //'modelMap' => $this->modelMap,
            'modelTitle' => Inflector::humanize($this->modelSlug),
            'elementTitle' => $this->searchModel->getTitle(),
            'columns' => $this->searchModel->getColumns(),
            'title' => $this->searchModel->getTitle(),
        ], $params);
        return parent::render($view, $params);
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
    protected function findModel($id)
    {
        $modelClass = $this->modelClass;
        /** @var ActiveRecord $model */
        if (($model = $modelClass::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}
