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

    public function testBootstrapOk()
    {
        $maintenance = new Maintenance($this->state);
        $maintenance->bootstrap(Yii::$app);

        $headers = Yii::$app->response->getHeaders()->toArray();
        $this->tester->assertArrayNotHasKey('retry-after', $headers);
        $this->tester->assertEquals(Yii::$app->response->statusCode, Maintenance::STATUS_CODE_OK);
    }

    public function testBootstrapMaintenance()
    {
        $this->state->enable();
        $maintenance = new Maintenance($this->state);
        $maintenance->retryAfter = 'Tue, 10 Mar 2020 12:42:50 GMT';
        $maintenance->bootstrap(Yii::$app);

        $headers = Yii::$app->response->getHeaders()->toArray();
        $this->tester->seeMyVar($headers['retry-after']);
        $this->tester->assertArrayHasKey('retry-after', $headers);

        $this->tester->assertEquals($headers['retry-after'][0], 'Tue, 10 Mar 2020 12:42:50 GMT');
        $this->state->disable();
    }
}
