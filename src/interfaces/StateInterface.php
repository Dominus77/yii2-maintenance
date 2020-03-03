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
}