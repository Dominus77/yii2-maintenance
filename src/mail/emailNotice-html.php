<?php

use yii\helpers\Html;
use dominus77\maintenance\BackendMaintenance;

/**
 * @var $this yii\web\View
 * @var $backLink string
 */
?>
<div class="email-maintenance-notice">
    <h2><?= BackendMaintenance::t('app', 'Technical work completed.') ?></h2>
    <p><?= BackendMaintenance::t('app', 'Please follow the link below to visit the site.') ?></p>
    <?= Html::a($backLink, $backLink) ?>
</div>
