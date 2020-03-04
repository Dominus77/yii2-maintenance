<?php

use Codeception\Test\Unit;
use dominus77\maintenance\interfaces\StateInterface;
use dominus77\maintenance\Maintenance;

/**
 * Class FileStateTest
 */
class FileStateTest extends Unit
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
        $this->tester->assertEquals($this->state->statusCode(), Maintenance::STATUS_CODE_MAINTENANCE);
    }

    /**
     * Disable mode
     */
    public function testDisable()
    {
        $this->state->disable();
        $this->tester->assertFileNotExists($this->state->path);
        $this->tester->assertEquals($this->state->statusCode(), Maintenance::STATUS_CODE_OK);
    }

    /**
     * Status code
     */
    public function testStatusCode()
    {
        $this->tester->assertEquals($this->state->statusCode(), Maintenance::STATUS_CODE_OK);
        $this->state->enable();
        $this->tester->assertEquals($this->state->statusCode(), Maintenance::STATUS_CODE_MAINTENANCE);
        $this->state->disable();
        $this->tester->assertEquals($this->state->statusCode(), Maintenance::STATUS_CODE_OK);
    }

    /**
     * Timestamp
     * @throws Exception
     */
    public function testTimestamp()
    {
        $timestamp = $this->state->timestamp();
        $date = new DateTime(date($this->state->dateFormat));
        $this->tester->assertEquals($timestamp, $date->getTimestamp());
    }
}