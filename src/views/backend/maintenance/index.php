<?php

use yii\web\View;
use yii\helpers\Html;
use dominus77\maintenance\models\FileStateForm;
use dominus77\maintenance\widgets\maintenance\MaintenanceFormWidget;
use dominus77\maintenance\widgets\timer\CountDownWidget;
use dominus77\maintenance\widgets\followers\FollowersWidget;
use dominus77\maintenance\BackendMaintenance;

/**
 * @var $this View
 * @var $name string
 * @var $model FileStateForm
 * @var $isEnable bool
 */

$isEnable = $model->isEnabled();

$this->title = $name;
$this->params['breadcrumbs'][] = $this->title;
?>
<section class="maintenance-index">
    <div class="row">
        <div class="col-md-6">
            <div class="box <?= $isEnable ? 'box-danger' : 'box-success' ?>">
                <div class="box-header with-border">
                    <h3 class="box-title"><?= Html::encode($model->modeName) ?> <?= $isEnable ? BackendMaintenance::t('app', 'up {:date}', [':date' => $model->dateTime]) : '' ?></h3>
                    <div class="box-tools pull-right"></div>
                </div>
                <div class="box-body">
                    <?= MaintenanceFormWidget::widget([
                        'model' => $model
                    ]) ?>
                </div>
                <div class="box-footer">
                    <div class="pull-left">
                        <?= CountDownWidget::widget([
                            'status' => $isEnable,
                            'timestamp' => $model->getTimestamp(),
                            'message' => Yii::t('app', 'Time is over'),
                            'countContainerOptions' => [
                                'style' => 'display:none;'
                            ],
                            'noteContainerOptions' => [
                                'style' => 'text-align: left;',
                            ]
                        ]) ?>
                    </div>
                    <div class="pull-right">
                        <?php if (($message = Yii::$app->session->getFlash($model::MAINTENANCE_UPDATE_KEY)) && $message !== null) { ?>
                            <p class="notify" style="color: green"><?= $message ?></p>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title"><?= BackendMaintenance::t('app', 'Followers') ?></h3>
                    <div class="box-tools pull-right"></div>
                </div>
                <div class="box-body">
                    <?= FollowersWidget::widget() ?>
                </div>
                <div class="box-footer">
                    <div class="pull-left">
                        <?php if (($message = Yii::$app->session->getFlash($model::MAINTENANCE_NOTIFY_SENDER_KEY)) && $message !== null) { ?>
                            <p class="notify" style="color: green"><?= $message ?>.</p>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
