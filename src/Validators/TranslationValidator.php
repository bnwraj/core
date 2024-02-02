<?php

namespace Vtlabs\Core\Validators;

use Vtlabs\Core\Helpers\CoreHelper;

class TranslationValidator
{
    public function validate($attribute, $value, $parameters, $validator)
    {
        // check for the valid translation input format, if format is correct,
        // inserts the key in current request 
        $isValid = true;
        $isValidName = substr($attribute, strlen($attribute) - strlen('_translations')) === '_translations';

        // if string, decode json first
        $value = is_string($value) ? json_decode($value, true) : $value;
        
        if($isValidName && is_array($value)) {
            foreach ($value as $val) {
                if(empty($val['language']) || empty($val['text']) || !CoreHelper::isValidLocale($val['language'])) {
                    $isValid = false;
                    break;
                }
            }

            // if valid, insert in request
            if($isValid) {
                $keyName = substr($attribute, 0, strlen($attribute) - strlen('_translations'));
                CoreHelper::transformRequestForTranslation($keyName, $value);
            }
        } else {
            $isValid = false;
        }
    
        return $isValid;
    }

    public function replacer($message, $attribute, $rule, $parameters)
    {
        return 'Invalid translation format. Must be a array, name must end with _translations and each object must contains language and text fields. Only available languages are allowed';
    }
}
