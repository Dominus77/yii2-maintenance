<?php

namespace dominus77\maintenance\interfaces;

use Exception;

/**
 * Interface StateFormInterface
 * @package dominus77\maintenance\interfaces
 */
interface StateFormInterface
{
    /**
     * Save
     *
     * @return bool
     */
    public function save();

    /**
     * Timestamp
     *
     * @return int
     * @throws Exception
     */
    public function getTimestamp();
}
