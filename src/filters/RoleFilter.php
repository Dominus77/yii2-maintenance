<?php

namespace dominus77\maintenance\filters;

use Yii;
use yii\web\User;
use dominus77\maintenance\Filter;

/**
 * Class RoleFilter
 * @package dominus77\maintenance\filters
 */
class RoleFilter extends Filter
{
    /**
     * @var array|string
     */
    public $roles;
    /**
     * @var User
     */
    protected $user;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->user = Yii::$app->user;
        if (is_string($this->roles)) {
            $this->roles = [$this->roles];
        }
        parent::init();
    }

    /**
     * @return bool
     */
    public function isAllowed()
    {
        if (is_array($this->roles) && !empty($this->roles)) {
            foreach ($this->roles as $role) {
                if ($this->user->can($role)) {
                    return true;
                }
            }
        }
        return false;
    }
}