<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use dominus77\maintenance\models\FileStateForm;
use dominus77\maintenance\BackendMaintenance;

/**
 * @var $this View
 * @var $model FileStateForm
 */
?>
<?php $form = ActiveForm::begin([
    'id' => 'maintenance-update-form',
    'action' => Url::to(['/maintenance/index']),
]); ?>
<?= $form->field($model, 'mode')->dropDownList($model::getModesArray()) ?>

<div style="display:none" id="maintenance-setting-container">
    <?= $form->field($model, 'date')->textInput([
        'placeholder' => date($model->dateFormat),
    ]) ?>

    <?= $form->field($model, 'title')->textInput([
        'placeholder' => true,
    ]) ?>

    <?= $form->field($model, 'text')->textarea([
        'placeholder' => true,
        'rows' => 6,
        'class' => 'form-control'
    ]) ?>

    <?= $form->field($model, 'subscribe')->checkbox() ?>
</div>

<?= Html::submitButton(BackendMaintenance::t('app', 'Save'), [
    'class' => 'btn btn-primary',
    'name' => 'maintenance-subscribe-button'
]) ?>
<?php ActiveForm::end(); ?>
