<?php

namespace app\widgets;

use app\models\GeneToProgeria;
use yii\base\Widget;

class GeneToProgeriaWidget extends Widget
{
    /** @var GeneToProgeria */
    public $model;

    public function init()
    {
        parent::init();
    }

    public function run()
    {
        return $this->render('geneToProgeria', ['geneToProgeria' => $this->model]);
    }
}