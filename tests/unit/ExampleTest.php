<?php

use Codeception\Test\Unit;
use dominus77\maintenance\interfaces\StateInterface;

/**
 * Class ExampleTest
 */
class ExampleTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected $tester;

    /**
     * @var StateInterface
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
        $this->tester->assertEquals($this->state->fileName, 'YII_TEST_MAINTENANCE_MODE_ENABLED');
        $this->tester->assertEquals($this->state->fileSubscribe, 'YII_TEST_MAINTENANCE_MODE_SUBSCRIBE');
        $this->tester->assertEquals($this->state->dateFormat, 'd-m-Y H:i:s');
    }

    /**
     * Enable mode
     */
    public function testEnable()
    {
        $this->state->enable();
        $this->tester->assertFileExists($this->state->path);
    }

    /**
     * Is enable
     */
    public function testIsEnable()
    {
        $this->tester->assertTrue($this->state->isEnabled());
    }

    /**
     * Disable mode
     */
    public function testDisable()
    {
        $this->state->disable();
        $this->tester->assertFileNotExists($this->state->path);
    }
}