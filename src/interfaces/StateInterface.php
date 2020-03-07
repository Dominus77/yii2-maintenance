<?php

namespace dominus77\maintenance\interfaces;

/**
 * Interface StateInterface
 * @package dominus77\maintenance\interfaces
 */
interface StateInterface
{
    /**
     * Enable mode method
     *
     * @return mixed
     */
    public function enable();

    /**
     * Disable mode method
     *
     * @return mixed
     */
    public function disable();

    /**
     * @return bool
     */
    public function isEnabled();

    /**
     * @return integer
     */
    public function timestamp();

    /**
     * @return string
     */
    public function getDateFormat();

    /**
     * @return string
     */
    public function getFileStatePath();

    /**
     * @return string
     */
    public function getSubscribePath();

    /**
     * @return array
     */
    public function getSubscribeOptions();

    /**
     * @return array
     */
    public function getSubscribeOptionsTemplate();

    /**
     * @return string
     */
    public function getDefaultTitle();

    /**
     * @return string
     */
    public function getDefaultContent();

    /**
     * @return int|string
     */
    public function statusCode();
}