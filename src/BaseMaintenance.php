<?php

namespace dominus77\maintenance;

use Yii;
use yii\base\BaseObject;
use yii\helpers\ArrayHelper;
use yii\i18n\PhpMessageSource;

/**
 * Class BaseMaintenance
 * @package dominus77\maintenance
 */
class BaseMaintenance extends BaseObject
{
    /**
     * BaseMaintenance constructor.
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
    protected function addRules()
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
     * @param null $language
     * @return string
     */
    public static function t($category, $message, $params = [], $language = null)
    {
        return Yii::t('dominus77/maintenance/' . $category, $message, $params, $language);
    }
}