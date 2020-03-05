<?php

use Codeception\Test\Unit;
use dominus77\maintenance\BackendMaintenance;
use dominus77\maintenance\interfaces\StateInterface;
use dominus77\maintenance\Maintenance;
use dominus77\maintenance\models\FileStateForm;
use dominus77\maintenance\states\FileState;

/**
 * Class FileStateFormTest
 */
class FileStateFormTest extends Unit
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

    public function testFileStateFormSuccess()
    {
        $model = $this->getLoadedModel();
        // validate
        $this->tester->assertTrue($model->validate());
        // save
        $this->tester->assertTrue($model->save());
        // check file
        $this->tester->assertFileExists($this->state->path);
        // check is enable
        $this->tester->assertTrue($model->isEnabled());
        // check status code
        $this->tester->assertEquals($model->getStatusCode(), Maintenance::STATUS_CODE_MAINTENANCE);
        // disable mode
        $this->state->disable();
    }

    public function testValidateWrongData()
    {
        $model = $this->getLoadedModel();
        $model->date = 'wrong datetime';
        // validate
        $this->tester->assertFalse($model->validate());
        // error date
        expect_that($model->getErrors('date'));
    }

    public function testMaintenanceUpdate()
    {
        $model = $this->getLoadedModel();
        $this->tester->assertTrue($model->validate());
        $this->tester->assertTrue($model->save());
        // check file
        $this->tester->assertFileExists($this->state->path);

        // Update
        $model = new FileStateForm();
        $model->title = 'Title update';
        $model->text = 'Text update';
        // validate
        $this->tester->assertTrue($model->validate());
        // save
        $this->tester->assertTrue($model->save());

        // check file
        $this->tester->assertFileExists($this->state->path);
        // check new data
        $this->checkModelUpdate();
        // disable maintenance mode
        $this->state->disable();
        // check status code
        $this->tester->assertEquals($model->getStatusCode(), Maintenance::STATUS_CODE_OK);
    }

    protected function checkModelUpdate()
    {
        $model = new FileStateForm();
        $this->tester->assertEquals($model->mode, (string)Maintenance::STATUS_CODE_MAINTENANCE);
        $this->tester->assertEquals($model->date, '01-01-2020 20:05:00');
        $this->tester->assertEquals($model->title, 'Title update');
        $this->tester->assertEquals($model->text, 'Text update');
        $this->tester->assertEquals($model->subscribe, '1');
        $this->tester->assertEquals($model->countDown, '1');
    }

    public function testModeName()
    {
        $model = new FileStateForm();

        $model->mode = Maintenance::STATUS_CODE_OK;
        $this->tester->assertEquals($model->getModeName(), BackendMaintenance::t('app', 'Mode normal'));

        $model->mode = Maintenance::STATUS_CODE_MAINTENANCE;
        $this->tester->assertEquals($model->getModeName(), BackendMaintenance::t('app', 'Mode maintenance'));
    }

    protected function getLoadedModel()
    {
        return new FileStateForm([
            'mode' => (string)Maintenance::STATUS_CODE_MAINTENANCE,
            'date' => '01-01-2020 20:05:00',
            'title' => 'Test Maintenance',
            'text' => 'The test site is undergoing technical work. We apologize for any inconvenience caused.',
            'subscribe' => '1',
            'countDown' => '1',
        ]);
    }
}