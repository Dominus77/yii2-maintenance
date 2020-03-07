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
        $model = new FileStateForm();
        $model->mode = Maintenance::STATUS_CODE_MAINTENANCE;
        // validate
        $this->tester->assertTrue($model->validate());
        // save
        $this->tester->assertTrue($model->save());
        // check file
        $this->tester->assertFileExists($model->getPath());
        // check is enable
        $this->tester->assertTrue($model->isEnabled());
        // check status code
        $this->tester->assertEquals($model->getStatusCode(), Maintenance::STATUS_CODE_MAINTENANCE);
        // disable mode
        $this->state->disable();
    }

    public function testValidateSuccessData()
    {
        $model = $model = new FileStateForm();
        $model->mode = Maintenance::STATUS_CODE_MAINTENANCE;
        $model->date = date($model->getDateFormat());
        // validate
        $this->tester->assertTrue($model->validate());
    }

    public function testValidateWrongData()
    {
        $model = $model = new FileStateForm();
        $model->mode = Maintenance::STATUS_CODE_MAINTENANCE;
        $model->date = 'wrong datetime';
        // validate
        $this->tester->assertFalse($model->validate());
        // error date
        expect_that($model->getErrors('date'));
    }

    public function testMaintenanceUpdate()
    {
        $date = '13-03-2020 10:05:05';
        $title = 'Title';
        $newTitle = 'New Title';

        $model = new FileStateForm();
        $model->mode = Maintenance::STATUS_CODE_MAINTENANCE;
        $model->date = $date;
        $model->title = $title;
        $this->tester->assertTrue($model->validate());
        $this->tester->assertTrue($model->save());
        $this->tester->assertFileExists($model->getPath());

        $model = new FileStateForm();
        $this->tester->assertEquals($model->title, $title);

        $model = new FileStateForm();
        $model->mode = Maintenance::STATUS_CODE_MAINTENANCE;
        $model->title = $newTitle;
        $this->tester->assertTrue($model->validate());
        $this->tester->assertTrue($model->save());
        $this->tester->assertFileExists($model->getPath());

        $model = new FileStateForm();
        $this->tester->assertEquals($model->date, $date);
        $this->tester->assertEquals($model->title, $newTitle);

        $this->state->disable();
    }

    public function testModeName()
    {
        $model = new FileStateForm();

        $model->mode = Maintenance::STATUS_CODE_OK;
        $this->tester->assertEquals($model->getModeName(), BackendMaintenance::t('app', 'Mode normal'));

        $model->mode = Maintenance::STATUS_CODE_MAINTENANCE;
        $this->tester->assertEquals($model->getModeName(), BackendMaintenance::t('app', 'Mode maintenance'));
    }

    public function testValidateDateAttribute()
    {
        $model = new FileStateForm();

        $model->date = 'wrong date';
        $model->validateDateAttribute('date');
        expect_that($model->getErrors('date'));

        $model->date = '12-03-2020 12:05:23';
        $this->tester->assertNull($model->validateDateAttribute('date'));
    }
}