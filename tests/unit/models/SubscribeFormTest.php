<?php

use Codeception\Test\Unit;
use dominus77\maintenance\interfaces\StateInterface;
use dominus77\maintenance\states\FileState;

/**
 * Class SubscribeFormTest
 */
class SubscribeFormTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected $tester;

    /**
     * @var FileState
     */
    protected $state;

    protected function _before()
    {
        $this->state = Yii::$container->get(StateInterface::class);
    }

    protected function _after()
    {
    }

    // tests
    public function testSomeFeature()
    {

    }
}