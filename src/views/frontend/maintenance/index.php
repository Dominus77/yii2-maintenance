<?php

use yii\helpers\Html;
use dominus77\maintenance\models\SubscribeForm;
use dominus77\maintenance\models\FileStateForm;
use dominus77\maintenance\widgets\timer\CountDownWidget;
use dominus77\maintenance\widgets\subscribe\SubscribeFormWidget;
use dominus77\maintenance\BackendMaintenance;

/* @var $this yii\web\View */
/* @var $name string */
/* @var $message string */
/* @var $subscribeForm SubscribeForm */
/* @var $fileStateForm FileStateForm */

$this->title = $name;
?>
<h1><?= Html::encode($this->title) ?></h1>
<p><?= $message ?></p>
<br>
<?= CountDownWidget::widget([
    'status' => $fileStateForm->isTimer(),
    'timestamp' => $fileStateForm->getTimestamp(),
    'message' => BackendMaintenance::t('app', 'The site will work soon! Please refresh the page.'),
]) ?>
<div class="form-container">
    <?php if (($status = $fileStateForm->isSubscribe()) && $status === true) { ?>
        <p><?= BackendMaintenance::t('app', 'We can notify you when everything is ready.') ?></p>
        <?= SubscribeFormWidget::widget([
            'status' => $status,
            'model' => $subscribeForm
        ]) ?>
    <?php } ?>
</div>
<div class="social-container"></div>
