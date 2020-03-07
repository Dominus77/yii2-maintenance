<?php

use yii\helpers\Html;
use dominus77\maintenance\models\SubscribeForm;
use dominus77\maintenance\models\FileStateForm;
use dominus77\maintenance\widgets\timer\CountDownWidget;
use dominus77\maintenance\widgets\subscribe\SubscribeFormWidget;
use dominus77\maintenance\BackendMaintenance;

/* @var $this yii\web\View */
/* @var $title string */
/* @var $name string */
/* @var $message string */
/* @var $subscribeForm SubscribeForm */
/* @var $fileStateForm FileStateForm */

$this->title = $title;
?>
<?php if ($name) { ?>
    <h1><?= Html::encode($name) ?></h1>
<?php } ?>
<?php if ($message) { ?>
    <p><?= $message ?></p>
    <br>
<?php } ?>

<?= CountDownWidget::widget([
    'status' => $fileStateForm->isTimer(),
    'timestamp' => $fileStateForm->getTimestamp(),
    'message' => BackendMaintenance::t('app', 'The site will work soon! Please refresh the page.'),
]) ?>

<?php if (($status = $fileStateForm->isSubscribe()) && $status === true) { ?>
    <div class="form-container">
        <p><?= BackendMaintenance::t('app', 'We can notify you when everything is ready.') ?></p>
        <?= SubscribeFormWidget::widget([
            'status' => $status,
            'model' => $subscribeForm
        ]) ?>
    </div>
<?php } ?>
<div class="social-container"></div>
