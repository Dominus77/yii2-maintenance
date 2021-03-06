<?php

namespace dominus77\maintenance\filters;

use Yii;
use yii\web\Application;
use yii\web\Request;
use dominus77\maintenance\Filter;

/**
 * IP addresses checker with mask supported.
 * @package dominus77\maintenance\filters
 */
class IpFilter extends Filter
{
    /**
     * @var array|string
     */
    public $ips;
    /**
     * @var Request|null
     */
    protected $request;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (Yii::$app instanceof Application) {
            $this->request = Yii::$app->request;
        }
        if (is_string($this->ips)) {
            $this->ips = [$this->ips];
        }
        parent::init();
    }

    /**
     * @return bool
     */
    public function isAllowed()
    {
        if ($this->request && is_array($this->ips) && !empty($this->ips)) {
            $ip = $this->request->userIP;
            foreach ($this->ips as $filter) {
                if ($this->checkIp($filter, $ip)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Check IP (mask supported).
     * @param string $filter
     * @param string $ip
     * @return bool
     */
    protected function checkIp($filter, $ip)
    {
        return $filter === '*' || $filter === $ip
            || (($pos = strpos($filter, '*')) !== false && !strncmp($ip, $filter, $pos));
    }
}
