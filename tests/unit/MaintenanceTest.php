<?php

use Codeception\Test\Unit;
use dominus77\maintenance\interfaces\StateInterface;
use dominus77\maintenance\Maintenance;

/**
 * Class MaintenanceTest
 */
class MaintenanceTest extends Unit
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
        $this->state->disable();
    }

    public function testStatusCodeOk()
    {
        $this->state->disable();
        $maintenance = new Maintenance($this->state);
        $this->tester->assertEquals($maintenance->statusCode, Maintenance::STATUS_CODE_OK);
    }

    public function testStatusCodeMaintenance()
    {
        $this->state->enable();
        $maintenance = new Maintenance($this->state);
        $this->tester->assertEquals($maintenance->statusCode, Maintenance::STATUS_CODE_MAINTENANCE);
        $this->state->disable();
    }
}
