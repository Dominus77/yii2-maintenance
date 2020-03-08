<?php

namespace dominus77\maintenance;

use Yii;
use yii\base\BootstrapInterface;
use yii\base\InvalidConfigException;
use yii\base\Application;
use dominus77\maintenance\interfaces\StateInterface;

/**
 * Class Maintenance
 * @package dominus77\maintenance
 */
class Maintenance extends BackendMaintenance implements BootstrapInterface
{
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
     * @var int|string
     */
    public $statusCode;
    /**
     * Retry-After header
     * If not set, set automatically from the set time, + 10 minutes
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
        $this->state = $state;
        if ($this->state->isEnabled()) {
            $timestamp = $this->state->timestamp();
            $this->retryAfter = $this->retryAfter ?: gmdate('D, d M Y H:i:s \G\M\T', $timestamp); // (Wed, 21 Oct 2015 07:28:00 GMT)
            $this->statusCode = $this->statusCode ?: $this->state->statusCode();
        }
        parent::__construct($config);
    }

    /**
     * @param Application $app
     * @throws InvalidConfigException
     */
    public function bootstrap($app)
    {
        $response = $app->response;
        if ($app->request->isAjax) {
            $response->statusCode = self::STATUS_CODE_OK;
        } else {
            $response->statusCode = $this->statusCode;
            if ($this->retryAfter && is_string($this->retryAfter)) {
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
        if (empty($this->filters)) {
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
}
