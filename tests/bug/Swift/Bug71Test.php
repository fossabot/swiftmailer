<?php

class Swift_Bug71Test extends \PHPUnit\Framework\TestCase
{
    private $_message;

    protected function setUp()
    {
        $this->_message = new Swift_Message('test');
    }

    public function testCallingToStringAfterSettingNewBodyReflectsChanges()
    {
        $this->_message->setBody('BODY1');
        $this->assertRegExp('/BODY1/', $this->_message->toString());

        $this->_message->setBody('BODY2');
        $this->assertRegExp('/BODY2/', $this->_message->toString());
    }
}
