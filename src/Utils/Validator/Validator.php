<?php

namespace App\Utils\Validator;

use Symfony\Contracts\Translation\TranslatorInterface;

class Validator {

    protected $errors = [];

    public function validate(): array {
        return $this->errors;
    }

    public function string(TranslatorInterface $trans, ...$args): Validator {
        foreach($args as $k => $arg){
            if(!is_string($arg[0]) || ctype_digit($arg[0])) $this->errors[] = $trans->trans("validator.not.string", ["%field%" => $trans->trans($arg[1], [], 'base')], 'validator');
        }
        return $this;
    }

    public function number(TranslatorInterface $trans, ...$args): Validator{
        foreach($args as $k => $arg){
            if(!is_int($arg[0])) $this->errors[] = $trans->trans("validator.not.number", ["%field%" => $trans->trans($arg[1], [], 'base')], 'validator');
        }
        return $this;
    }

    public function checkSize(TranslatorInterface $trans, $arg, string $fieldname, int $size): Validator{
        if(!count($arg) === $size) $this->errors[] = $trans->trans("validator.not.size", ["%field%" => $trans->trans($fieldname, [], "base"), "%size%" => $size], 'validator');
        return $this;
    }

    public function checkSizeMin(TranslatorInterface $trans, $arg, string $fieldname, int $sizemin): Validator{
        if(!count($arg) >= $sizemin) $this->errors[] = $trans->trans("validator.not.size.min", ["%field%" => $trans->trans($fieldname, [], 'base'), "%sizemin%" => $sizemin], 'validator');
        return $this; 
    }

    public function checkSizeMax(TranslatorInterface $trans, $arg, string $fieldname, int $sizemax): Validator{
        if(!count($arg) <= $sizemax) $this->errors[] = $trans->trans("validator.not.size.max", ["%field%" => $trans->trans($fieldname, [], 'base'), "%sizemax%" => $sizemax], 'validator');
        return $this;
    }

    public function phoneNumber(TranslatorInterface $trans, $arg, string $fieldname): Validator {
        if(!preg_match("#[0][1-9][- \.?]?([0-9][0-9][- \.?]?){4}$#", $arg)) $this->errors[] = $trans->trans("validator.not.phone_number", ["%field%" => $trans->trans($fieldname, [], 'base')], 'validator');
        return $this;
    }

    public function password(TranslatorInterface $trans, $arg, string $fieldname): Validator {
        if(!preg_match('/^(?=.*\d)(?=.*[A-Za-z])[0-9A-Za-z!@#$%]{8,12}$/', $arg)) $this->errors[] = $trans->trans("validator.not.password", ["%field%" => $trans->trans($fieldname, [], 'base')], 'validator');
        return $this;
    }
}