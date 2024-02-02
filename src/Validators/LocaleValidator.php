<?php

namespace Vtlabs\Core\Validators;

use Vtlabs\Core\Helpers\CoreHelper;

class LocaleValidator
{
    public function validate($attribute, $value, $parameters, $validator)
    {
        return CoreHelper::isValidLocale($value);
    }

    public function replacer($message, $attribute, $rule, $parameters)
    {
        return 'Invalid locale';
    }
}
