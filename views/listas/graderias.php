<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\bootstrap\Modal;
use kartik\grid\GridView;
use johnitvn\ajaxcrud\CrudAsset; 
use johnitvn\ajaxcrud\BulkButtonWidget;


$this->title = 'Listado de sillas y graderias';
$this->params['breadcrumbs'][] = $this->title;

?>
<?= $msj ?>