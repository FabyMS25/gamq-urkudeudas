<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\bootstrap\Modal;
use kartik\grid\GridView;
use johnitvn\ajaxcrud\CrudAsset; 

/* @var $this yii\web\View */
/* @var $searchModel app\models\SearchPagos */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Pagos y  anulaciones de graderias y sillas';
$this->params['breadcrumbs'][] = $this->title;

CrudAsset::register($this);

?>
<div class="pagos-index">
    <div id="ajaxCrudDatatable">
        <?=GridView::widget([
            'id'=>'crud-datatable',
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'showPageSummary' => true,
            'rowOptions'=>function($model){
                    if($model->pago_preliquidacion == 1){ 
                        return ['class' => GridView::TYPE_WARNING];
                    }
                    if($model->pago_anulado == 1){ 
                        return ['class' => GridView::TYPE_DANGER];
                    }
            },
            'pjax'=>true,
            'columns' => require(__DIR__.'/_columns_general.php'),
            'toolbar'=> [
                ['content'=>                   
                    Html::a('<i class="glyphicon glyphicon-repeat"></i>', [''],
                    ['data-pjax'=>1, 'class'=>'btn btn-default', 'title'=>'Recargar datos']).
                    '{toggleData}'.
                    '{export}'
                ],
            ],          
            'striped' => true,
            'condensed' => true,
            'responsive' => true,          
            'panel' => [
                'type' => 'primary', 
                'heading' => '<i class="glyphicon glyphicon-list"></i> '.$this->title,
                'before'=>'',
                'after'=> '<div class="clearfix"></div>',
            ]
        ])?>
    </div>
</div>
<?php Modal::begin([
    "id"=>"ajaxCrudModal",
    "footer"=>"",// always need it for jquery plugin
])?>
<?php Modal::end(); ?>
