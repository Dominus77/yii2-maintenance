<?php

namespace dominus77\maintenance;

use Yii;
use yii\base\BaseObject;
use yii\base\BootstrapInterface;
use yii\helpers\ArrayHelper;
use yii\i18n\PhpMessageSource;
use yii\web\Application;

/**
 * Class BaseMaintenance
 * @package dominus77\maintenance
 */
class BaseMaintenance extends BaseObject implements BootstrapInterface
{

    /**
     * BaseMaintenance constructor.
     * @param array $config
     */
    public function __construct($config = [])
    {
        $this->addI18n();
        parent::__construct($config);
    }

    /**
     * @param Application $app
     */
    public function bootstrap($app)
    {
        $this->addRules($app);
    }

    /**
     * Add rules this route
     * @param Application $app
     */
    private function addRules(Application $app)
    {
        $rules = [
            'maintenance' => 'maintenance/index',
            'maintenance/<_a:\w+>' => 'maintenance/<_a>'
        ];
        $rules = (YII_DEBUG) ? ArrayHelper::merge($rules, [
            '<_m:debug>/<_c:\w+>/<_a:\w+>' => '<_m>/<_c>/<_a>',
        ]) : $rules;
        $urlManager = $app->urlManager;
        $urlManager->addRules($rules);
    }

    /**
     * Add i18n
     */
    private function addI18n()
    {
        $i18n = Yii::$app->i18n;
        $i18n->translations['dominus77/maintenance/*'] = [
            'class' => PhpMessageSource::class,
            'basePath' => '@dominus77/maintenance/messages',
            'fileMap' => [
                'dominus77/maintenance/app' => 'app.php',
            ]
        ];
    }

    /**
     * @param $category
     * @param $message
     * @param array $params
     * @param null $language
     * @return string
     */
    public static function t($category, $message, $params = [], $language = null)
    {
        return Yii::t('dominus77/maintenance/' . $category, $message, $params, $language);
    }
}