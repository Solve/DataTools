<?php
/*
 * This file is a part of Solve framework.
 *
 * @author Alexandr Viniychuk <alexandr.viniychuk@icloud.com>
 * @copyright 2009-2014, Alexandr Viniychuk
 * created: 16.10.14 10:07
 */

namespace Solve\DataTools\Rules;


class EmailValidationRule extends BaseRule {

    public function process($fieldName, $value, $params, $dataProcessor) {
        $regex = '#^[\w-]+(?:\.[\w-]+)*@(?:[\w-]+\.)+[a-zA-Z]{2,7}$#';
        if (preg_match($regex, $value)) {
            return true;
        }
        $dataProcessor->addError($fieldName, 'Invalid email');
        return null;
    }


} 