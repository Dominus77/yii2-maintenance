<?php

use Codeception\Test\Unit;
use dominus77\maintenance\interfaces\StateInterface;
use dominus77\maintenance\models\SubscribeForm;
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
        $subscribeForm = new SubscribeForm();
        $subscribeForm->deleteFile();
    }

    // tests
    public function testSubscribeSuccess()
    {
        $subscribeForm = new SubscribeForm([
            'email' => 'test@email.com',
        ]);
        $subscribeForm->deleteFile();
        $this->tester->assertTrue($subscribeForm->validate());
        $this->tester->assertTrue($subscribeForm->subscribe());
        $this->tester->assertFileExists($subscribeForm->getPath());
        $this->tester->assertTrue($subscribeForm->deleteFile());
        $this->tester->assertFileNotExists($subscribeForm->getPath());
    }

    public function testSubscribeWrongEmail()
    {
        $subscribeForm = new SubscribeForm([
            'email' => 'test.email.com',
        ]);
        $this->tester->assertFalse($subscribeForm->validate());
        expect_that($subscribeForm->getErrors('email'));
    }

    public function testAlreadySubscribed()
    {
        $emailArray = [
            'test@email.com',
            'test2@email.com',
            'test3@email.com'
        ];

        $subscribeForm = new SubscribeForm();
        $subscribeForm->email = $emailArray[0];
        $this->tester->assertFalse($subscribeForm->isEmail());
        $this->tester->assertTrue($subscribeForm->subscribe());

        $subscribeForm->email = $emailArray[0];
        $this->tester->assertTrue($subscribeForm->isEmail());

        $this->tester->assertTrue($subscribeForm->deleteFile());
        $this->tester->assertFileNotExists($subscribeForm->getPath());
    }

    public function testSendNotifySubscribers()
    {
        $emailArray = [
            'test@email.com',
            'test2@email.com',
            'test3@email.com'
        ];

        $subscribeForm = new SubscribeForm();
        foreach ($emailArray as $value) {
            $subscribeForm->email = $value;
            $subscribeForm->subscribe();
        }
        $this->tester->assertFileExists($subscribeForm->getPath());
        $emails = $subscribeForm->getEmails();
        $this->tester->seeMyVar($emails);
        $this->tester->assertCount(3, $emails);

        foreach ($emails as $email) {
            $this->tester->assertArrayHasKey($email, array_flip($emailArray));
        }
        $this->tester->assertCount($subscribeForm->send(), $emails);
        $this->tester->assertFileNotExists($subscribeForm->getPath());
    }

    public function testGetFollowers()
    {
        $emailArray = [
            'test@email.com',
            'test2@email.com',
            'test3@email.com'
        ];
        $subscribeForm = new SubscribeForm();
        foreach ($emailArray as $value) {
            $subscribeForm->email = $value;
            $subscribeForm->subscribe();
        }
        $this->tester->assertFileExists($subscribeForm->getPath());
        $followers = $subscribeForm->getFollowers();
        $this->tester->seeMyVar($followers);
        foreach ($followers as $key => $data) {
            $this->tester->assertArrayHasKey($key, array_flip($emailArray));
            $this->tester->assertArrayHasKey('email', $data);
            $this->tester->assertArrayHasKey('date', $data);
            foreach ($data as $k => $value) {
                $this->tester->assertArrayHasKey($data['email'], array_flip($emailArray));
            }
        }
        $this->tester->assertTrue($subscribeForm->deleteFile());
    }

    public function testIsSubscribe()
    {
        $emailArray = [
            'test@email.com',
            'test2@email.com',
            'test3@email.com'
        ];
        $subscribeForm = new SubscribeForm();
        $subscribeForm->email = $emailArray[0];
        $subscribeForm->subscribe();

        $subscribeForm->email = $emailArray[0];
        $this->tester->assertFalse($subscribeForm->subscribe());
    }

    public function testGetFrom()
    {
        $emailArray = [
            'test@email.com',
            'test2@email.com',
            'test3@email.com'
        ];
        Yii::$app->params['senderEmail'] = $emailArray[0];
        $subscribeForm = new SubscribeForm();
        $this->tester->assertEquals($subscribeForm->getFrom($emailArray[1]), $emailArray[0]);

        Yii::$app->params['senderEmail'] = '';
        Yii::$app->params['supportEmail'] = $emailArray[1];
        $this->tester->assertEquals($subscribeForm->getFrom($emailArray[0]), $emailArray[1]);
    }
}
