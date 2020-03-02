<?php

namespace dominus77\maintenance\models;

use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use dominus77\maintenance\interfaces\StateInterface;
use dominus77\maintenance\BaseMaintenance;

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
     * Set mail templates for notify subscribers
     * @var array
     */
    public $template;
    /**
     * Set from email for notify subscribers
     * default: Yii::$app->params['supportEmail']
     * @var string
     */
    public $from;
    /**
     * Set subject for notify subscribers
     * @var string
     */
    public $subject;

    /**
     * @inheritDoc
     */
    public function init()
    {
        parent::init();
        $this->state = Yii::$container->get(StateInterface::class);
        $this->template = $this->template ?: [
            'html' => '@dominus77/maintenance/mail/emailNotice-html',
            'text' => '@dominus77/maintenance/mail/emailNotice-text'
        ];
        $this->from = $this->from ?: Yii::$app->params['supportEmail'];
        $this->subject = $this->subject ?: BaseMaintenance::t('app', 'Notification of completion of technical work');
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
            'email' => BaseMaintenance::t('app', 'Your email'),
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
            $messages[] = $mailer->compose($this->template, [])
                ->setFrom([$this->from => Yii::$app->name])
                ->setTo($email)
                ->setSubject($this->subject);

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
}
