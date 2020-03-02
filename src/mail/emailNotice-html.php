<?php

use yii\helpers\Html;
use yii\helpers\Url;
use dominus77\maintenance\BaseMaintenance;

/**
 * @var $this yii\web\View
 * @var $link string
 */

$link = Url::to(Yii::$app->urlManager->hostInfo);
?>
<div class="email-maintenance-notice">
    <h2><?= BaseMaintenance::t('app', 'Technical work completed.') ?></h2>
    <p><?= BaseMaintenance::t('app', 'Please follow the link below to visit the site.') ?></p>
    <?= Html::a($link, $link) ?>
</div>
