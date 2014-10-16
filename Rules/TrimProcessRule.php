<?php
/*
 * This file is a part of Solve framework.
 *
 * @author Alexandr Viniychuk <alexandr.viniychuk@icloud.com>
 * @copyright 2009-2014, Alexandr Viniychuk
 * created: 16.10.14 09:22
 */

namespace Solve\DataTools\Rules;


class TrimProcessRule extends BaseRule {


    public function process($fieldName, $value, $params, $dataProcessor) {
        if (is_string($value)) {
            return trim($value);
        } else {
            return $value;
        }
    }

}