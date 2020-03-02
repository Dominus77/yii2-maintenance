<?php

use yii\helpers\Html;
use dominus77\maintenance\widgets\timer\CountDown;
use dominus77\maintenance\models\SubscribeForm as ModelSubscribeForm;
use dominus77\maintenance\widgets\subscribe\SubscribeForm;
use dominus77\maintenance\BackendMaintenance;

/* @var $this yii\web\View */
/* @var $name string */
/* @var $message string */
/* @var $model ModelSubscribeForm */

$this->title = $name;
?>
<h1><?= Html::encode($this->title) ?></h1>
<p><?= $message ?></p>
<br>
<?= CountDown::widget([
    'status' => $model->isTimer(),
    'timestamp' => $model->getTimestamp(),
    'message' => BackendMaintenance::t('app', 'The site will work soon! Please refresh the page.'),
]) ?>
<div class="form-container">
    <?php if (($status = $model->isSubscribe()) && $status === true) { ?>
        <p><?= BackendMaintenance::t('app', 'We can notify you when everything is ready.') ?></p>
        <?= SubscribeForm::widget([
            'status' => $status,
            'model' => $model
        ]) ?>
    <?php } ?>
</div>
<div class="social-container"></div>
