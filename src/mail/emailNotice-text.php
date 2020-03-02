<?php

use dominus77\maintenance\BaseMaintenance;

/**
 * @var $this yii\web\View
 * @var $backLink string
 */

?>
<?= BaseMaintenance::t('app', 'Technical work completed.') ?>
<?= BaseMaintenance::t('app', 'Please follow the link below to visit the site.') ?>

<?= $backLink ?>
