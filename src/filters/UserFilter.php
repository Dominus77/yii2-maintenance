<?php

namespace dominus77\maintenance\filters;

use Yii;
use yii\web\IdentityInterface;
use dominus77\maintenance\Filter;

/**
 * Class UserFilter
 * @package dominus77\maintenance\filters
 */
class UserFilter extends Filter
{
    /**
     * @var string
     */
    public $checkedAttribute;
    /**
     * @var array|string
     */
    public $users;
    /**
     * @var IdentityInterface|null
     */
    protected $identity;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (Yii::$app->user->identity instanceof IdentityInterface) {
            $this->identity = Yii::$app->user->identity;
        }
        if (is_string($this->users)) {
            $this->users = [$this->users];
        }
        parent::init();
    }

    /**
     * @return bool
     */
    public function isAllowed()
    {
        if ($this->identity && is_array($this->users) && !empty($this->users)) {
            return (bool)in_array($this->identity->{$this->checkedAttribute}, $this->users, true);
        }
        return false;
    }
}