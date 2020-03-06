<?php

namespace dominus77\maintenance\models;

use Yii;
use yii\helpers\ArrayHelper;
use dominus77\maintenance\interfaces\SubscribeFormInterface;
use dominus77\maintenance\BackendMaintenance;
use RuntimeException;

/**
 * Class SubscribeForm
 * @package dominus77\maintenance\models
 *
 * @property array $emails
 */
class SubscribeForm extends BaseForm implements SubscribeFormInterface
{
    const SUBSCRIBE_SUCCESS = 'subscribeSuccess';
    const SUBSCRIBE_INFO = 'subscribeInfo';

    /**
     * @var string
     */
    public $email;

    /**
     * @var array
     */
    protected $followers;

    /**
     * @var array
     */
    protected $subscribeOptions;

    /**
     * @inheritDoc
     */
    public function init()
    {
        parent::init();
        $urlManager = Yii::$app->urlManager;
        $subscribeOptions = [
            'template' => $this->state->getSubscribeOptionsTemplate(),
            'backLink' => $urlManager->hostInfo, // Link in a letter to the site
            'from' => $this->getFrom('noreply@mail.com'),
            'subject' => BackendMaintenance::t('app', 'Notification of completion of technical work')
        ];
        $this->subscribeOptions = ArrayHelper::merge($subscribeOptions, $this->state->getSubscribeOptions());
        $this->setFollowers();
    }

    /**
     * @inheritDoc
     * @return array
     */
    public function rules()
    {
        return [
            ['email', 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritDoc
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'email' => BackendMaintenance::t('app', 'Your email'),
        ];
    }

    /**
     * Sending notifications to followers
     *
     * @return int
     */
    public function send()
    {
        try {
            $emails = $this->getEmails();
            $messages = [];
            $mailer = Yii::$app->mailer;
            foreach ($emails as $email) {
                $messages[] = $mailer->compose(
                    $this->subscribeOptions['template'], [
                    'backLink' => $this->subscribeOptions['backLink']
                ])
                    ->setFrom([$this->subscribeOptions['from'] => Yii::$app->name])
                    ->setTo($email)
                    ->setSubject($this->subscribeOptions['subject']);

            }
            $result = $mailer->sendMultiple($messages);
            if ($result >= 0) {
                $this->deleteFile();
            }
            return $result;
        } catch (RuntimeException $e) {
            throw new RuntimeException(
                'Attention: Error sending notifications to subscribers!'
            );
        }
    }

    /**
     * Save email in file
     *
     * @return bool
     */
    public function save()
    {
        $str = $this->prepareData();
        $file = $this->state->getSubscribePath();
        try {
            if (is_string($file) && $str && $fp = fopen($file, 'ab')) {
                fwrite($fp, $str . PHP_EOL);
                fclose($fp);
                return chmod($file, 0765);
            }
            return false;
        } catch (RuntimeException $e) {
            throw new RuntimeException(
                "Attention: Subscriber cannot be added because {$file} could not be save."
            );
        }
    }

    /**
     * Delete subscribers file
     * @return bool
     */
    public function deleteFile()
    {
        $file = $this->state->getSubscribePath();
        try {
            $result = false;
            if (file_exists($file)) {
                $result = unlink($file);
            }
            return $result;
        } catch (RuntimeException $e) {
            throw new RuntimeException(
                "Attention: Error deleting {$file} file."
            );
        }
    }

    /**
     * This prepare data on before save
     * @return string
     */
    protected function prepareData()
    {
        return date($this->dateFormat) . ' = ' . $this->email;
    }

    /**
     * Save follower
     * @return bool
     */
    public function subscribe()
    {
        if ($this->isEmail()) {
            return false;
        }
        return $this->save();
    }

    /**
     * Emails subscribe
     * @return array
     */
    public function getEmails()
    {
        $subscribeData = $this->prepareLoadModel($this->state->getSubscribePath());
        $emails = [];
        foreach ($subscribeData as $email) {
            $emails[] = $email;
        }
        return $emails;
    }

    /**
     * Check email is subscribe
     * @return bool
     */
    public function isEmail()
    {
        return ArrayHelper::isIn($this->email, $this->getEmails());
    }

    /**
     * @return array
     */
    public function getFollowers()
    {
        $items = [];
        foreach ($this->followers as $follower) {
            $items[]['email'] = $follower;
        }
        return $items;
    }

    /**
     * @param array $followers
     */
    public function setFollowers($followers = [])
    {
        $this->followers = $followers ?: $this->getEmails();
    }

    /**
     * From
     * @param $from string
     * @return mixed|string
     */
    protected function getFrom($from)
    {
        if (isset(Yii::$app->params['senderEmail']) && !empty(Yii::$app->params['senderEmail'])) {
            $from = Yii::$app->params['senderEmail'];
        } else if (isset(Yii::$app->params['supportEmail']) && !empty(Yii::$app->params['supportEmail'])) {
            $from = Yii::$app->params['supportEmail'];
        }
        return $from;
    }
}
