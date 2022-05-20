<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\bootstrap\Modal;
use kartik\grid\GridView;
use johnitvn\ajaxcrud\CrudAsset; 

$modelZona = \app\models\Zonas::findOne($zona);

/* @var $this yii\web\View */
/* @var $searchModel app\models\SearchGraderiasSillas */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Graderias y Sillas - '.$modelZona->zona_nombre."(".$modelZona->zona_color.") - gestion ".$modelZona->gestion->gest_nombre;
$this->params['breadcrumbs'][] = $this->title;

CrudAsset::register($this);

?>
<div class="graderias-sillas-index">
    <div id="ajaxCrudDatatable">
        <?=GridView::widget([
            'id'=>'crud-datatable',
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'pjax'=>true,
            'columns' => require(__DIR__.'/_columns.php'),
            'toolbar'=> [
                ['content'=>
                    Html::a('<i class="glyphicon glyphicon-plus"></i>', ['create','zona'=>$zona ],
                    ['role'=>'modal-remote','title'=> 'Crear graderias Sillas','class'=>'btn btn-default']).
                    Html::a('<i class="glyphicon glyphicon-repeat"></i>', ['', 'id'=>$zona],
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
])?>
<?php Modal::end(); ?>
