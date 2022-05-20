<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $model app\models\ActividadesEconomicas */
/* @var $form yii\widgets\ActiveForm */
$modelCategoria = (new app\models\Categorias());
$listaCategorias = ArrayHelper::map($modelCategoria->listaCategoriasModel(), 'categ_id', 'categ_nombre')

?>

<div class="actividades-economicas-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'categ_id')->dropDownList($listaCategorias, ['prompt'=>"*** Seleccione la categoria ***"]) ?>

    <?= $form->field($model, 'activi_descripcion')->textarea(['maxlength' => true, 'rows'=>3]) ?>
    
    <div class="row">
        <div class="col-sm-4 col-md-4">
            <?= $form->field($model, 'activi_largo_mts')->textInput() ?>
        </div>
        <div class="col-sm-4 col-md-4">
            <?= $form->field($model, 'activi_ancho_mts')->textInput() ?>
        </div>
        <div class="col-sm-4 col-md-4">
            <?= $form->field($model, 'activi_superficie')->textInput() ?>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-3 col-md-3">
            <?= $form->field($model, 'activi_costo_patente')->textInput() ?>
        </div>
        <div class="col-sm-3 col-md-3">
            <?= $form->field($model, 'activi_costo_sentaje_dia')->textInput() ?>
        </div>
        <div class="col-sm-3 col-md-3">
            <?= $form->field($model, 'activi_costo_aseo_por_dia')->textInput() ?>
        </div>
        <div class="col-sm-3 col-md-3">
            <?= $form->field($model, 'activi_costo_aseo_por_sitio')->textInput() ?>
        </div>
    </div>    

  
	<?php if (!Yii::$app->request->isAjax){ ?>
	  	<div class="form-group">
	        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
	    </div>
	<?php } ?>

    <?php ActiveForm::end(); ?>
    
</div>
