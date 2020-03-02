<?php

use yii\helpers\Html;
use dominus77\maintenance\BaseMaintenance;

/**
 * @var $this yii\web\View
 * @var $backlink string
 */
?>
<div class="email-maintenance-notice">
    <h2><?= BaseMaintenance::t('app', 'Technical work completed.') ?></h2>
    <p><?= BaseMaintenance::t('app', 'Please follow the link below to visit the site.') ?></p>
    <?= Html::a($backlink, $backlink) ?>
</div>
