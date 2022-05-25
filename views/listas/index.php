<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\bootstrap\Modal;
use kartik\grid\GridView;
use johnitvn\ajaxcrud\CrudAsset; 

/* @var $this yii\web\View */
/* @var $searchModel app\models\SearchPagos */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Graderias y sillas Listas';
$this->params['breadcrumbs'][] = $this->title;

CrudAsset::register($this);

/* @var $this yii\web\View */
/* @var $model app\models\Pagos */
?>
<div class="graderiasView">
    <div id="ajaxCrudDatatable">
        <?=GridView::widget ([
        'model' => $model,
        'attributes' => [
           'grad_id',
           'zona_id',
           'gest_id',
           'grad_codigo',
           'grad_direccion',
           'grad_longitud',
           'grad_acera',
           'grad_tipo_armado',
           'grad_tipo_sitio',
           'grad_vendido',
           'grad_estado',
            
        ],
    ]) ?>
    </div>
</div>
