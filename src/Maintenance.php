<?php

namespace dominus77\maintenance;

use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\web\Application;
use yii\base\BaseObject;
use yii\base\BootstrapInterface;
use yii\i18n\PhpMessageSource;
use dominus77\maintenance\interfaces\StateInterface;

/**
 * Class Maintenance
 * @package dominus77\maintenance
 */
class Maintenance extends BaseObject implements BootstrapInterface
{
    /**
     * Value of "OK" status code.
     */
    const STATUS_CODE_OK = 200;

    /**
     * Route to maintenance action.
     * @var string
     */
    public $route;
    /**
     * @var array
     */
    public $filters;
    /**
     * Default status code to send on maintenance
     * 503 = Service Unavailable
     * @var integer
     */
    public $statusCode = 503;
    /**
     * Retry-After header
     * @var bool|string
     */
    public $retryAfter = false;
    /**
     * @var StateInterface
     */
    protected $state;

    /**
     * Maintenance constructor.
     * @param StateInterface $state
     * @param array $config
     */
    public function __construct(StateInterface $state, array $config = [])
    {
        $i18n = Yii::$app->i18n;
        $i18n->translations['dominus77/maintenance/*'] = [
            'class' => PhpMessageSource::class,
            'basePath' => '@dominus77/maintenance/messages',
            'fileMap' => [
                'dominus77/maintenance' => 'maintenance.php',
            ]
        ];
        $this->state = $state;
        parent::__construct($config);
    }

    /**
     * @param Application $app
     * @throws InvalidConfigException
     */
    public function bootstrap($app)
    {
        $rules = [
            'maintenance' => 'maintenance/index',
            'maintenance/<_a:\w+>' => 'maintenance/<_a>'
        ];
        if (YII_DEBUG) {
            $rules = ArrayHelper::merge($rules, [
                '<_m:debug>/<_c:\w+>/<_a:\w+>' => '<_m>/<_c>/<_a>',
            ]);
        }
        $urlManager = $app->urlManager;
        $urlManager->addRules($rules);

        $response = $app->response;
        if ($app->request->isAjax) {
            $response->statusCode = self::STATUS_CODE_OK;
        } else {
            $response->statusCode = $this->statusCode;
            if ($this->retryAfter) {
                $response->headers->set('Retry-After', $this->retryAfter);
            }
        }

        if ($this->state->isEnabled() && !$this->filtersExcepted()) {
            $app->catchAll = [$this->route];
        } else {
            $response->statusCode = self::STATUS_CODE_OK;
        }
    }

    /**
     * @return bool
     * @throws InvalidConfigException
     */
    protected function filtersExcepted()
    {
        if (!is_array($this->filters) || empty($this->filters)) {
            return false;
        }
        foreach ($this->filters as $config) {
            $filter = Yii::createObject($config);
            if (!($filter instanceof Filter)) {
                throw new InvalidConfigException(
                    'Class "' . get_class($filter) . '" must instance of "' . Filter::class . '".'
                );
            }
            if ($filter->isAllowed()) {
                return true;
            }
        }
        return false;
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
