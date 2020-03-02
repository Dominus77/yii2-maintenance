<?php

use yii\helpers\Url;
use dominus77\maintenance\BaseMaintenance;

/**
 * @var $this yii\web\View
 * @var $link string
 */

$link = Url::to(Yii::$app->urlManager->hostInfo);
?>
<?= BaseMaintenance::t('app', 'Technical work completed.') ?>
<?= BaseMaintenance::t('app', 'Please follow the link below to visit the site.') ?>

<?= $link ?>
