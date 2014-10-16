<?php
/*
 * This file is a part of Solve framework.
 *
 * @author Alexandr Viniychuk <alexandr.viniychuk@icloud.com>
 * @copyright 2009-2014, Alexandr Viniychuk
 * created: 15.10.14 21:20
 */

namespace Solve\DataTools;


use Solve\DataTools\Rules\BaseRule;

class DataProcessor {

    const TYPE_VALIDATION = 'validation';
    const TYPE_PROCESS    = 'process';

    private static $_loadedValidationRules = array();
    private static $_loadedProcessRules    = array();
    private        $_fieldValidationRules  = array();
    private        $_fieldProcessRules     = array();
    private        $_validationErrors      = array();
    private        $_resultData            = array();

    public function __construct($rulesForFields = array()) {
        if (!empty($rulesForFields)) $this->addRules($rulesForFields);
    }

    public static function createInstance($rulesForFields = array()) {
        return new DataProcessor($rulesForFields);
    }

    public function addRules($rulesForFields) {
        foreach($rulesForFields as $fieldName => $allRules) {
            if (!empty($allRules[0])) {
                foreach($allRules[0] as $ruleName => $ruleParams) {
                    if (is_numeric($ruleName)) {
                        $ruleName = $ruleParams;
                        $ruleParams = array();
                    }
                    $this->addValidationRule($fieldName, $ruleName, $ruleParams);
                }
            }
            if (!empty($allRules[1])) {
                foreach($allRules[1] as $ruleName => $ruleParams) {
                    if (is_numeric($ruleName)) {
                        $ruleName = $ruleParams;
                        $ruleParams = array();
                    }
                    $this->addProcessRule($fieldName, $ruleName, $ruleParams);
                }
            }
        }
        return $this;
    }

    public function addValidationRule($fieldName, $ruleName, $ruleParams = array()) {
        if (empty($this->_fieldValidationRules[$fieldName])) $this->_fieldValidationRules[$fieldName] = array();
        $this->_fieldValidationRules[$fieldName][$ruleName] = $ruleParams;
        return $this;
    }

    public function addProcessRule($fieldName, $ruleName, $ruleParams = array()) {
        if (empty($this->_fieldProcessRules[$fieldName])) $this->_fieldProcessRules[$fieldName] = array();
        $this->_fieldProcessRules[$fieldName][$ruleName] = $ruleParams;
        return $this;
    }

    public function updateValidationRule($fieldName, $ruleName, $ruleParams) {
        if (!empty($this->_fieldValidationRules[$fieldName][$fieldName])) {
            $this->_fieldValidationRules[$fieldName][$fieldName] = $ruleParams;
        }
        return $this;
    }

    public function updateProcessRule($fieldName, $ruleName, $ruleParams) {
        if (!empty($this->_fieldProcessRules[$fieldName][$fieldName])) {
            $this->_fieldProcessRules[$fieldName][$fieldName] = $ruleParams;
        }
        return $this;
    }

    public function removeValidationRule($fieldName, $ruleName) {
        if (!empty($this->_fieldValidationRules[$fieldName][$fieldName])) {
            unset($this->_fieldValidationRules[$fieldName][$fieldName]);
        }
        return $this;
    }

    public function removeProcessRule($fieldName, $ruleName) {
        if (!empty($this->_fieldProcessRules[$fieldName][$fieldName])) {
            unset($this->_fieldProcessRules[$fieldName][$fieldName]);
        }
        return $this;
    }

    public function clearValidationRules($fieldName = null) {
        if (!empty($fieldName) && !empty($this->_fieldValidationRules[$fieldName])) {
            $this->_fieldValidationRules[$fieldName] = array();
        } else {
            $this->_fieldValidationRules = array();
        }
        return $this;
    }

    public function clearProcessRules($fieldName = null) {
        if (!empty($fieldName) && !empty($this->_fieldProcessRules[$fieldName])) {
            $this->_fieldProcessRules[$fieldName] = array();
        } else {
            $this->_fieldProcessRules = array();
        }
        return $this;
    }

    public function process($data) {
        $this->clearErrors();

        foreach ($this->_fieldProcessRules as $fieldName => $rules) {
            foreach ($rules as $ruleName => $ruleParams) {
                $callable         = $this->_loadRule('process', $ruleName, $ruleParams);
                $value            = array_key_exists($fieldName, $data) ? $data[$fieldName] : null;
                $data[$fieldName] = call_user_func($callable, $fieldName, $value, $ruleParams, $this);
            }
        }

        foreach ($this->_fieldValidationRules as $fieldName => $rules) {
            foreach ($rules as $ruleName => $ruleParams) {
                $callable         = $this->_loadRule('validation', $ruleName, $ruleParams);
                $value            = array_key_exists($fieldName, $data) ? $data[$fieldName] : null;
                $validationResult = call_user_func($callable, $fieldName, $value, $ruleParams, $this);
                if ($validationResult === true) {
                    continue;
                } elseif ($validationResult === false) {
                    $errorMessage = !empty($ruleParams['error_message']) ? $ruleParams['error_message'] : 'Error in field ' . $fieldName . ' - ' . $ruleName;
                    $this->addError($fieldName, $errorMessage);
                }

            }
        }

        $this->_resultData = $data;
        return $this;
    }

    public function hasErrors() {
        return !empty($this->_validationErrors) ? true : false;
    }

    public function isValid() {
        return !$this->hasErrors();
    }

    public function addError($fieldName, $errorMessage) {
        if (empty($this->_validationErrors[$fieldName])) $this->_validationErrors[$fieldName] = array();
        $this->_validationErrors[$fieldName][] = $errorMessage;
        return $this;
    }

    public function getErrors() {
        return $this->_validationErrors;
    }

    public function clearErrors() {
        $this->_validationErrors = array();
        return $this;
    }

    public function getData($fieldName = null) {
        if ($fieldName) {
            return array_key_exists($fieldName, $this->_resultData) ? $this->_resultData[$fieldName] : null;
        } else {
            return $this->_resultData;
        }
    }


    private function _loadRule($ruleType, $ruleName, $params) {
        $ruleType = ucfirst($ruleType);
        if (!empty($params['class'])) {
            $ruleClassName = $params['class'];
            $ruleMethod    = strtolower($ruleType) . ucfirst($ruleName);
        } else {
            $ruleClassName = '\\Solve\\DataTools\\Rules\\' . ucfirst($ruleName) . $ruleType . 'Rule';
            $ruleMethod    = 'process';
        }
        $staticArrayName = '_loaded' . $ruleType . 'Rules';
        $staticStorage   = &self::$$staticArrayName;

        if (empty($staticStorage[$ruleName])) {
            if (!class_exists($ruleClassName)) throw new \Exception('Cannot load ' . strtolower($ruleType) . ' rule: ' . $ruleName . ', class:[' . $ruleClassName . '] Check file names.');

            $staticStorage[$ruleName] = new $ruleClassName($this);
        }
        if ($staticStorage[$ruleName] instanceof BaseRule) {
            $ruleMethod = 'process';
        }
        return array($staticStorage[$ruleName], $ruleMethod);
    }
}