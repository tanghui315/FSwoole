<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: test.proto

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Protobuf type <code>TestRequest</code>
 */
class TestRequest extends \Google\Protobuf\Internal\Message
{
    /**
     * <code>string name = 1;</code>
     */
    private $name = '';
    /**
     * <code>int32 age = 2;</code>
     */
    private $age = 0;

    public function __construct() {
        \GPBMetadata\Test::initOnce();
        parent::__construct();
    }

    /**
     * <code>string name = 1;</code>
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * <code>string name = 1;</code>
     */
    public function setName($var)
    {
        GPBUtil::checkString($var, True);
        $this->name = $var;
    }

    /**
     * <code>int32 age = 2;</code>
     */
    public function getAge()
    {
        return $this->age;
    }

    /**
     * <code>int32 age = 2;</code>
     */
    public function setAge($var)
    {
        GPBUtil::checkInt32($var);
        $this->age = $var;
    }

}

