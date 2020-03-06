<?php

namespace dominus77\maintenance;

use Yii;
use yii\base\BaseObject;
use yii\helpers\ArrayHelper;
use yii\i18n\PhpMessageSource;

/**
 * Class BackendMaintenance
 * @package dominus77\maintenance
 */
class BackendMaintenance extends BaseObject
{
    /**
     * Value of "OK" status code.
     */
    const STATUS_CODE_OK = 200;

    /**
     * Value of "Maintenance" status code.
     */
    const STATUS_CODE_MAINTENANCE = 503;

    /**
     * BackendMaintenance constructor.
     * @param array $config
     */
    public function __construct($config = [])
    {
        $this->addI18n();
        $this->addRules();
        parent::__construct($config);
    }

    /**
     * Add rules this route
     */
    private function addRules()
    {
        $rules = [
            'maintenance' => 'maintenance/index',
            'maintenance/<_a:\w+>' => 'maintenance/<_a>'
        ];
        $rules = (YII_DEBUG) ? ArrayHelper::merge($rules, [
            '<_m:debug>/<_c:\w+>/<_a:\w+>' => '<_m>/<_c>/<_a>',
        ]) : $rules;
        $urlManager = Yii::$app->urlManager;
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
     * @param string $language
     * @return string
     */
    public static function t($category, $message, $params = [], $language = '')
    {
        return Yii::t('dominus77/maintenance/' . $category, $message, $params, $language);
    }
}