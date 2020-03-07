<?php

use yii\web\View;
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
    'id' => 'maintenance-update-form'
]); ?>
<?= $form->field($model, 'mode')->dropDownList($model::getModesArray()) ?>

<div style="display:none" id="maintenance-setting-container">
    <?= $form->field($model, 'date')->textInput([
        'placeholder' => $model->getDateTime(),
    ]) ?>

    <?= $form->field($model, 'title')->textInput([
        'placeholder' => $model->getTitle(),
    ]) ?>

    <?= $form->field($model, 'text')->textarea([
        'placeholder' => $model->getText(),
        'rows' => 6,
        'class' => 'form-control'
    ]) ?>

    <?= $form->field($model, 'subscribe')->checkbox() ?>
    <?= $form->field($model, 'countDown')->checkbox() ?>
</div>

<?= Html::submitButton(BackendMaintenance::t('app', 'Save'), [
    'class' => 'btn btn-primary',
    'name' => 'maintenance-subscribe-button'
]) ?>
<?php ActiveForm::end(); ?>
