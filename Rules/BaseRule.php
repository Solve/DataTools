<?php
/*
 * This file is a part of Solve framework.
 *
 * @author Alexandr Viniychuk <alexandr.viniychuk@icloud.com>
 * @copyright 2009-2014, Alexandr Viniychuk
 * created: 16.10.14 09:19
 */

namespace Solve\DataTools\Rules;


use Solve\DataTools\DataProcessor;

abstract class BaseRule {

    /**
     * @var DataProcessor
     */
    protected $_dataProcessor;

    public function __construct($dataProcessorInstance) {
        $this->_dataProcessor = $dataProcessorInstance;
    }

    /**
     * Returned result used for error messaging process
     * true - there is no error
     * false - there is error, but it will be added from params
     * null - error was handled by validation rule
     *
     * @param $fieldName
     * @param $value
     * @param $params
     * @param DataProcessor $dataProcessor
     * @return mixed
     */
    abstract public function process($fieldName, $value, $params, $dataProcessor);

}