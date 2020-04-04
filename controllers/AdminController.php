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
    protected $modelSlug;

    /** @var string */
    protected $modelClass;

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

    public function init()
    {
    }

    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        $isAdmin = Yii::$app->user->identity->isAdmin();

        if (!$isAdmin) {
            throw new \yii\web\ForbiddenHttpException("Admin-only area");
        }

        $this->modelMap = $this->module->modelMap;
        $this->getView()->params['allowTruncate'] = $this->module->allowTruncate;

        $modelSlug = Yii::$app->request->get('model');
        if (empty($modelSlug)) {
            //probably it is module start page, or incorrect route
            $this->redirectFirst();
            return false;
        }

        $this->modelClass = $this->modelMap[$modelSlug] ?? null;
        if (empty($this->modelClass)) {
            throw new InvalidRouteException("Model $modelSlug is not configured to use here");
        }
        $this->modelSlug = $this->getView()->params['modelSlug'] = $modelSlug;
        $this->modelDesc = new ModelDescribe($this->modelClass, $action->id);

        return true;
    }

    //default route
    protected function redirectFirst()
    {
        $moduleId = $this->module->id;
        $modelSlug = key($this->modelMap);

        return $this->redirect("$moduleId/$modelSlug/index");
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
            'searchModel' => $modelDesc->model,
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
        $model = new $this->modelClass;
        $this->modelDesc->setScenario($model, 'create');

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $id = $model->id;

            $msg = $this->modelDesc->getShortModelName() . " #$id created";
            Yii::$app->getSession()->setFlash('success', Yii::t('app', $msg));
            return $this->redirect('index');        //['view', 'id' => $model->id]
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
            'model' => $this->findModel($id),
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
        $model = $this->findModel($id);
        $this->modelDesc->setScenario($model, 'update');
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $msg = $this->modelDesc->getShortModelName() . " #$id updated";
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
        if ($this->findModel($id)->delete()) {
            $msg = $this->modelDesc->getShortModelName() . " #$id deleted";
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

        $table = $this->modelClass::tableName();
        $ok = Yii::$app->db->createCommand("TRUNCATE `$table`")->execute();

        if ($ok) {
            $msg = "Table `$table` truncated";
            Yii::$app->getSession()->setFlash('success', Yii::t('app', $msg));
        }

        return $this->redirect([$this->modelSlug . "/index"]);
    }

    public function render($view, $params = [])
    {
        $modelDesc = $this->modelDesc;
        $globalParams = [
            'modelSlug' => $this->modelSlug,
            'modelTitle' => Inflector::humanize($this->modelSlug),
        ];

        if ($modelDesc instanceof ModelDescribe) {
            $globalParams += [
                'elementTitle' => $modelDesc->getTitle(),
                'columns' => $modelDesc->getColumns(),
                'title' => $modelDesc->getTitle(),
            ];
        }
        $params = array_merge($globalParams, $params);
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
