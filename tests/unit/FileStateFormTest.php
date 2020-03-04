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
        $this->state->disable();
    }

    public function testValidateSuccess()
    {
        $model = new FileStateForm();
        $model->attributes = [
            'mode' => Maintenance::STATUS_CODE_MAINTENANCE,
            'date' => '01-01-2020 20:05:00',
            'title' => 'Test Maintenance',
            'text' => 'The test site is undergoing technical work. We apologize for any inconvenience caused.',
            'subscribe' => true,
            'countDown' => true,
        ];
        // validate
        $this->tester->assertTrue($model->validate());
    }

    public function testValidateWrongData()
    {
        $model = new FileStateForm();
        $model->attributes = [
            'mode' => Maintenance::STATUS_CODE_MAINTENANCE,
            'date' => 'this input error date format',
            'title' => 'Test Maintenance',
            'text' => 'The test site is undergoing technical work. We apologize for any inconvenience caused.',
            'subscribe' => true,
            'countDown' => true,
        ];
        // validate
        $this->tester->assertFalse($model->validate());
        // error date
        expect_that($model->getErrors('date'));
    }

    public function testMaintenanceEnable()
    {
        $model = new FileStateForm();
        $model->attributes = [
            'mode' => (string)Maintenance::STATUS_CODE_MAINTENANCE,
            'date' => '01-01-2020 20:05:00',
            'title' => 'Test Maintenance',
            'text' => 'The test site is undergoing technical work. We apologize for any inconvenience caused.',
            'subscribe' => true,
            'countDown' => true,
        ];
        // validate
        $this->tester->assertTrue($model->validate());
        // save
        $this->tester->assertTrue($model->save());
        // check file
        $this->tester->assertFileExists($this->state->path);
        // check is enable
        $this->tester->assertTrue($model->isEnabled());

        // Check this data
        $model = new FileStateForm();
        $this->tester->assertEquals($model->mode, (string)Maintenance::STATUS_CODE_MAINTENANCE);
        $this->tester->assertEquals($model->date, '01-01-2020 20:05:00');
        $this->tester->assertEquals($model->title, 'Test Maintenance');
        $this->tester->assertEquals($model->text, 'The test site is undergoing technical work. We apologize for any inconvenience caused.');
        $this->tester->assertEquals($model->subscribe, '1');
        $this->tester->assertEquals($model->countDown, '1');
        // check status code
        $this->tester->assertEquals($model->getStatusCode(), Maintenance::STATUS_CODE_MAINTENANCE);
        // check is timer
        $this->tester->assertTrue($model->isTimer());
        // check is subscribe
        $this->tester->assertTrue($model->isSubscribe());
        // disable maintenance mode
        $this->state->disable();
        // check status code
        $this->tester->assertEquals($model->getStatusCode(), Maintenance::STATUS_CODE_OK);

    }

    public function testMaintenanceUpdate()
    {
        // Enable
        $model = new FileStateForm();
        $model->attributes = [
            'mode' => (string)Maintenance::STATUS_CODE_MAINTENANCE,
            'date' => '01-01-2020 20:05:00',
            'title' => 'Test Maintenance',
            'text' => 'The test site is undergoing technical work. We apologize for any inconvenience caused.',
            'subscribe' => true,
            'countDown' => true,
        ];
        // validate
        $this->tester->assertTrue($model->validate());
        // save
        $this->tester->assertTrue($model->save());
        // check file
        $this->tester->assertFileExists($this->state->path);
        // check is enable
        $this->tester->assertTrue($model->isEnabled());

        // Update
        $model = new FileStateForm();
        $model->attributes = [
            'date' => '01-12-2020 18:10:00',
            'title' => 'Test Maintenance update',
            'text' => 'Text update',
        ];
        // validate
        $this->tester->assertTrue($model->validate());
        // save
        $this->tester->assertTrue($model->save());
        // check file
        $this->tester->assertFileExists($this->state->path);
        // check is enable
        $this->tester->assertTrue($model->isEnabled());

        // Check this new data
        $model = new FileStateForm();
        $this->tester->assertEquals($model->mode, (string)Maintenance::STATUS_CODE_MAINTENANCE);
        $this->tester->assertEquals($model->date, '01-12-2020 18:10:00');
        $this->tester->assertEquals($model->title, 'Test Maintenance update');
        $this->tester->assertEquals($model->text, 'Text update');
        $this->tester->assertEquals($model->subscribe, '1');
        $this->tester->assertEquals($model->countDown, '1');
        // check status code
        $this->tester->assertEquals($model->getStatusCode(), Maintenance::STATUS_CODE_MAINTENANCE);
        // disable maintenance mode
        $this->state->disable();
        // check status code
        $this->tester->assertEquals($model->getStatusCode(), Maintenance::STATUS_CODE_OK);
    }

    public function testModeName()
    {
        $model = new FileStateForm();

        $model->mode = Maintenance::STATUS_CODE_OK;
        $this->tester->assertEquals($model->getModeName(), BackendMaintenance::t('app', 'Mode normal'));

        $model->mode = Maintenance::STATUS_CODE_MAINTENANCE;
        $this->tester->assertEquals($model->getModeName(), BackendMaintenance::t('app', 'Mode maintenance'));
    }
}