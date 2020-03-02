<?php

namespace dominus77\maintenance\models;

use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use dominus77\maintenance\interfaces\StateInterface;
use dominus77\maintenance\BackendMaintenance;

/**
 * Class SubscribeForm
 * @package dominus77\maintenance\models
 *
 * @property string $fileStatePath
 * @property array $emails
 * @property string $datetime
 * @property int $timestamp
 * @property string $dateFormat
 * @property string $fileSubscribePath
 * @property string $email
 */
class SubscribeForm extends Model
{
    const SUBSCRIBE_SUCCESS = 'subscribeSuccess';
    const SUBSCRIBE_INFO = 'subscribeInfo';
    /**
     * @var string
     */
    public $email;
    /**
     * @var StateInterface
     */
    protected $state;

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
        $this->state = Yii::$container->get(StateInterface::class);
        $urlManager = Yii::$app->urlManager;
        $subscribeOptions = [
            'template' => [
                'html' => '@dominus77/maintenance/mail/emailNotice-html',
                'text' => '@dominus77/maintenance/mail/emailNotice-text'
            ],
            'backLink' => $urlManager->hostInfo, // Link in a letter to the site
            'from' => $this->getFrom('noreply@mail.com'),
            'subject' => BackendMaintenance::t('app', 'Notification of completion of technical work')
        ];
        $this->subscribeOptions = ArrayHelper::merge($subscribeOptions, $this->state->subscribeOptions);
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
     * @param array $emails
     * @return int
     */
    public function send($emails = [])
    {
        $emails = $emails ?: $this->getEmails();
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
        return $mailer->sendMultiple($messages);
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
        return $this->state->emails();
    }

    /**
     * Check email is subscribe
     * @return bool
     */
    public function isEmail()
    {
        return ArrayHelper::isIn($this->email, $this->emails);
    }

    /**
     * Timestamp in file for countDown
     * @return string
     */
    public function getTimestamp()
    {
        return $this->state->timestamp();
    }

    /**
     * Timer show/hide
     * @return bool
     */
    public function isTimer()
    {
        return $this->state->isTimer();
    }

    /**
     * Subscribe form on/off
     * @return bool will return true if on subscribe
     */
    public function isSubscribe()
    {
        return $this->state->isSubscribe();
    }

    /**
     * Save email in file
     * @return bool
     */
    protected function save()
    {
        return $this->state->save($this->email, $this->getFileSubscribePath());
    }

    /**
     * Subscribe file path
     * @return string
     */
    protected function getFileSubscribePath()
    {
        return $this->state->subscribePath;
    }

    /**
     * From
     * @param $from
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
