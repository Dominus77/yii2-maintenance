<?php

namespace dominus77\maintenance\filters;

use Yii;
use dominus77\maintenance\Filter;
use yii\web\Request;

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
     * @var Request
     */
    protected $request;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->request = Yii::$app->request;
        if (is_string($this->ips)) {
            $this->ips = [$this->ips];
        }
        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function isAllowed()
    {
        if (is_array($this->ips) && !empty($this->ips)) {
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
     * @since 1.0.0
     */
    protected function checkIp($filter, $ip)
    {
        return $filter === '*' || $filter === $ip
            || (($pos = strpos($filter, '*')) !== false && !strncmp($ip, $filter, $pos));
    }
}