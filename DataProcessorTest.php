<?php
/*
 * This file is a part of Solve framework.
 *
 * @author Alexandr Viniychuk <alexandr.viniychuk@icloud.com>
 * @copyright 2009-2014, Alexandr Viniychuk
 * created: 15.10.14 21:21
 */

namespace Solve\DataTools\Tests;
require_once __DIR__ . '/DataProcessor.php';
require_once __DIR__ . '/Rules/BaseRule.php';
require_once __DIR__ . '/Rules/TrimProcessRule.php';
require_once __DIR__ . '/Rules/EmailValidationRule.php';
require_once __DIR__ . '/Rules/MandatoryValidationRule.php';

use Solve\DataTools\DataProcessor;

class DataProcessorTest extends \PHPUnit_Framework_TestCase {

    public function testBasic() {
        $simpleData = array('login' => '   login with space   ');

        $dp = DataProcessor::createInstance();
        $dp->addProcessRule('login', 'trim');
        $dp->process($simpleData);
        $this->assertEquals('login with space', $dp->getData('login'), 'trim process loaded and worked');

        $dp->addValidationRule('login', 'email');
        $dp->process($simpleData);
        $this->assertArrayHasKey('login', $dp->getErrors(), 'Has validation error on email');

        $data = array(
            'login' => '  a@viniychuk.com ',
            'name'  => '  Alexandr'
        );

        $dp->process($data);
        $this->assertTrue($dp->isValid(), 'Valid email');

        $dp->clearProcessRules();
        $dp->clearValidationRules();

        $allRules = array(
            'name' => array(array('email'), array('trim'))
        );

        $result = DataProcessor::createInstance($allRules)->process(array('name'=>'data  '));
        $this->assertNotEmpty($result->getErrors(), 'Errors on grouped rules');
        $this->assertEquals('data', $result->getData('name'), 'Processing on grouped rules');

        $dp = new DataProcessor();
        $dp->addValidationRule('name', 'mandatory');
        $this->assertNotEmpty($dp->process(array('login'=>'asdf'))->getErrors(), 'Mandatory rule');
    }
}
 