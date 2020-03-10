<?php

namespace dominus77\maintenance\models;

use Yii;
use yii\data\ArrayDataProvider;
use yii\helpers\ArrayHelper;
use dominus77\maintenance\interfaces\SubscribeFormInterface;
use dominus77\maintenance\BackendMaintenance;

/**
 * Class SubscribeForm
 * @package dominus77\maintenance\models
 *
 * @property array $emails
 * @property string $path
 * @property array $followers
 * @property int $pageSize
 * @property ArrayDataProvider $dataProvider
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
    protected $subscribeOptions;

    /**
     * Path to file
     * @var string
     */
    private $_path;

    /**
     * @inheritDoc
     */
    public function init()
    {
        parent::init();
        $this->_path = $this->state->getSubscribePath();
        $urlManager = Yii::$app->urlManager;
        $subscribeOptions = [
            'template' => $this->state->getSubscribeOptionsTemplate(),
            'backLink' => $urlManager->hostInfo, // Link in a letter to the site
            'from' => $this->getFrom('noreply@mail.com'),
            'subject' => BackendMaintenance::t('app', 'Notification of completion of technical work')
        ];
        $this->subscribeOptions = ArrayHelper::merge($subscribeOptions, $this->state->getSubscribeOptions());
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
            ['email', 'string', 'max' => 255]
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
    }

    /**
     * Save email in file
     *
     * @return bool
     */
    public function save()
    {
        $str = $this->prepareSaveData();
        $file = $this->getPath();
        file_put_contents($file, $str, FILE_APPEND);
        chmod($file, 0765);
        return true;
    }

    /**
     * Delete subscribers file
     * @return bool
     */
    public function deleteFile()
    {
        $file = $this->state->getSubscribePath();
        $result = false;
        if (file_exists($file)) {
            $result = unlink($file);
        }
        return $result;
    }

    /**
     * This prepare data on before save
     * @return string
     */
    public function prepareSaveData()
    {
        return $this->email . ' = ' . date($this->dateFormat) . PHP_EOL;
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
        $subscribeData = $this->getFollowers();
        $emails = [];
        foreach ($subscribeData as $key => $values) {
            $emails[] = $values['email'];
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
     * @return ArrayDataProvider
     */
    public function getDataProvider()
    {
        return new ArrayDataProvider([
            'allModels' => $this->getFollowers(),
            'pagination' => [
                'pageSize' => $this->getPageSize()
            ],
        ]);
    }

    /**
     * @return int
     */
    public function getPageSize()
    {
        return $this->state->getPageSize();
    }

    /**
     * @return array
     */
    public function getFollowers()
    {
        $subscribeData = $this->prepareLoadModel($this->getPath());
        $items = [];
        foreach ($subscribeData as $email => $date) {
            $items[$email]['email'] = $email;
            $items[$email]['date'] = $date;
        }
        return $items;
    }

    /**
     * From
     * @param $from string
     * @return mixed|string
     */
    public function getFrom($from)
    {
        if (isset(Yii::$app->params['senderEmail']) && !empty(Yii::$app->params['senderEmail'])) {
            $from = Yii::$app->params['senderEmail'];
        } else if (isset(Yii::$app->params['supportEmail']) && !empty(Yii::$app->params['supportEmail'])) {
            $from = Yii::$app->params['supportEmail'];
        }
        return $from;
    }

    /**
     * Path to file
     * @return string
     */
    public function getPath()
    {
        return $this->_path;
    }
}
