<?php

namespace Vtlabs\Core\Filters;

use EloquentFilter\ModelFilter;

class UserFilter extends ModelFilter
{
    public function setup()
    {
        if (env('DEMO_ENABLED', 0)) {
            $this->whereHas('roles', function ($query) {
                return $query->where('name', '<>', 'admin');
            });
        }
    }

    public function name($name)
    {
        return $this->whereLike('name', $name);
    }

    public function mobileNumber($number)
    {
        return $this->whereLike('mobile_number', $number);
    }

    public function email($email)
    {
        return $this->whereLike('email', $email);
    }

    public function search($search)
    {
        $this->whereLike('email', $search)->orWhere('name', $search)->orWhere('username', $search)->orWhere('mobile_number', $search);
    }
}
