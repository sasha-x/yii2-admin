<?php

namespace sasha_x\admin\components;

class View extends \yii\web\View
{
    public $customViewsPath;

    public $modelSlug;

    public function render($view, $params = [], $context = null)
    {
        $view = $this->findView($view);
        return parent::render($view, $params, $context);
    }

    /**
     * @param string $view
     */
    protected function findView($view)
    {
        if ($this->customViewsPath) {
            $path = (strpos($view, '/') === false) ? "{$this->modelSlug}/$view" : $view;
            $path = $this->customViewsPath . "/$path";
            $file = $this->findViewFile($path);
            if ($file && file_exists($file)) {
                //custom view file found
                return $path;
            }
        }
        //return default
        return $view;
    }
}