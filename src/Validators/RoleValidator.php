<?php

namespace Vtlabs\Core\Validators;

use Spatie\Permission\Models\Role;

class RoleValidator
{
    public function validate($attribute, $value, $parameters, $validator)
    {
        if(is_array($value)) {
            foreach ($value as $k => $v) {
                if(!Role::where('name', $v)->exists()) {
                    return false;
                }
            }
        } else {
            return Role::where('name', $value)->exists();
        }
        return true;
    }
    
    public function replacer($message, $attribute, $rule, $parameters)
    {
        return 'Invalid role';
    }
}
