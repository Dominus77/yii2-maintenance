<?php

namespace dominus77\maintenance\interfaces;

/**
 * Interface SubscribeFormInterface
 * @package dominus77\maintenance\interfaces
 */
interface SubscribeFormInterface
{
    /**
     * Save
     *
     * @return bool
     */
    public function save();

    /**
     * @return int
     */
    public function send();
}
