<?php

class Swift_Mime_IdGeneratorTest extends \PHPUnit_Framework_TestCase
{
    protected $emailValidator;
    protected $originalServerName;

    public function testIdGeneratorSetRightId()
    {
        $idGenerator = new Swift_Mime_IdGenerator('example.net');
        $this->assertEquals('example.net', $idGenerator->getIdRight());

        $idGenerator->setIdRight('example.com');
        $this->assertEquals('example.com', $idGenerator->getIdRight());
    }

    public function testIdGenerateId()
    {
        $idGenerator = new Swift_Mime_IdGenerator('example.net');
        $emailValidator = new Swift_EmailValidatorBridge();

        $id = $idGenerator->generateId();
        $this->assertTrue($emailValidator->isValid($id));
        $this->assertEquals(1, preg_match('/^.{32}@example.net$/', $id));

        $anotherId = $idGenerator->generateId();
        $this->assertNotEquals($id, $anotherId);
    }
}
