<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\bootstrap\Modal;
use kartik\grid\GridView;
use johnitvn\ajaxcrud\CrudAsset; 
use johnitvn\ajaxcrud\BulkButtonWidget;

/* @var $this yii\web\View */
/* @var $searchModel app\models\SearchPagosEventuales */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Preliquidaciones de actividades economicas eventuales';
$this->params['breadcrumbs'][] = $this->title;

CrudAsset::register($this);

?>
<div class="pagos-eventuales-index">
    <div id="ajaxCrudDatatable">
        <?=GridView::widget([
            'id'=>'crud-datatable',
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'pjax'=>true,
            'columns' => require(__DIR__.'/_columns_eventual.php'),
            'toolbar'=> [
                ['content'=>
                    /*Html::a('<i class="glyphicon glyphicon-zoom-in"></i> Act. eventuales', ['create-eventual'],
                    ['role'=>'modal-remote','title'=> 'Preliquidacion eventuales','class'=>'btn btn-default']).  
                    
                     Html::a('<i class="glyphicon glyphicon-zoom-in"></i> Publicidad', ['create-publicidad'],
                    ['role'=>'modal-remote','title'=> 'Preliquidacion publicidad','class'=>'btn btn-default']).
                    
                     Html::a('<i class="glyphicon glyphicon-zoom-in"></i> Espectaculos', ['create-espectaculo'],
                    ['role'=>'modal-remote','title'=> 'Preliquidacion espectaculos','class'=>'btn btn-default']).
                    
                     Html::a('<i class="glyphicon glyphicon-zoom-in"></i> Alasitas', ['create-alasitas'],
                    ['role'=>'modal-remote','title'=> 'Preliquidacion alasitas','class'=>'btn btn-default']).*/
                    
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
                'after'=>'<div class="clearfix"></div>',
            ]
        ])?>
    </div>
</div>
<?php Modal::begin([
    "id"=>"ajaxCrudModal",
    "footer"=>"",// always need it for jquery plugin
    'size' => 'modal-lg',
])?>
<?php Modal::end(); ?>
