<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\FunctionalCluster */

$this->title = 'Редактировать возрастозависимый процесс ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Functional Clusters', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="functional-cluster-update">

    <h2><?= Html::encode($this->title) ?></h2>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
