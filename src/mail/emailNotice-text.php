<?php

use dominus77\maintenance\BackendMaintenance;

/**
 * @var $this yii\web\View
 * @var $backLink string
 */

?>
<?= BackendMaintenance::t('app', 'Technical work completed.') ?>
<?= BackendMaintenance::t('app', 'Please follow the link below to visit the site.') ?>

<?= $backLink ?>
