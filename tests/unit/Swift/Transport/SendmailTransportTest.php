<?php

/**
 * Class Swift_Transport_SendmailTransportTest
 */
class Swift_Transport_SendmailTransportTest extends Swift_Transport_AbstractSmtpEventSupportTest
{
    /**
     * @param Swift_Transport_IoBuffer|\Mockery\Mock $buf
     * @param Swift_Events_EventDispatcher|null      $dispatcher
     * @param string                                 $command
     *
     * @return Swift_Transport_SendmailTransport
     */
    protected function _getTransport($buf, $dispatcher = null, $command = '/usr/sbin/sendmail -bs')
    {
        if (!$dispatcher) {
            $dispatcher = $this->_createEventDispatcher();
        }
        $transport = new Swift_Transport_SendmailTransport($buf, $dispatcher, 'example.org');
        $transport->setCommand($command);

        return $transport;
    }

    /**
     * @param      $buf
     * @param null $dispatcher
     *
     * @return Swift_Transport_SendmailTransport
     */
    protected function _getSendmail($buf, $dispatcher = null)
    {
        if (!$dispatcher) {
            $dispatcher = $this->_createEventDispatcher();
        }

        return new Swift_Transport_SendmailTransport($buf, $dispatcher, 'example.org');
    }

    public function testCommandCanBeSetAndFetched()
    {
        $buf = $this->_getBuffer();
        $sendmail = $this->_getSendmail($buf);

        $sendmail->setCommand('/usr/sbin/sendmail -bs');
        $this->assertSame('/usr/sbin/sendmail -bs', $sendmail->getCommand());
        $sendmail->setCommand('/usr/sbin/sendmail -oi -t');
        $this->assertSame('/usr/sbin/sendmail -oi -t', $sendmail->getCommand());
    }

    public function testSendingMessageIn_t_ModeUsesSimplePipe()
    {
        $buf = $this->_getBuffer();
        $sendmail = $this->_getSendmail($buf);
        $message = $this->_createMessage();

        $message->shouldReceive('getTo')
            ->zeroOrMoreTimes()
            ->andReturn(array('foo@bar' => 'Foobar', 'zip@button' => 'Zippy'));
        $message->shouldReceive('toByteStream')
            ->once()
            ->with($buf);
        $buf->shouldReceive('initialize')
            ->once();
        $buf->shouldReceive('terminate')
            ->once();
        $buf->shouldReceive('setWriteTranslations')
            ->once()
            ->with(array("\r\n" => "\n", "\n." => "\n.."));
        $buf->shouldReceive('setWriteTranslations')
            ->once()
            ->with(array());

        $sendmail->setCommand('/usr/sbin/sendmail -t');
        $this->assertSame(2, $sendmail->send($message));
    }

    public function testSendingIn_t_ModeWith_i_FlagDoesntEscapeDot()
    {
        $buf = $this->_getBuffer();
        $sendmail = $this->_getSendmail($buf);
        $message = $this->_createMessage();

        $message->shouldReceive('getTo')
            ->zeroOrMoreTimes()
            ->andReturn(array('foo@bar' => 'Foobar', 'zip@button' => 'Zippy'));
        $message->shouldReceive('toByteStream')
            ->once()
            ->with($buf);
        $buf->shouldReceive('initialize')
            ->once();
        $buf->shouldReceive('terminate')
            ->once();
        $buf->shouldReceive('setWriteTranslations')
            ->once()
            ->with(array("\r\n" => "\n"));
        $buf->shouldReceive('setWriteTranslations')
            ->once()
            ->with(array());

        $sendmail->setCommand('/usr/sbin/sendmail -i -t');
        $this->assertSame(2, $sendmail->send($message));
    }

    public function testSendingInTModeWith_oi_FlagDoesntEscapeDot()
    {
        $buf = $this->_getBuffer();
        $sendmail = $this->_getSendmail($buf);
        $message = $this->_createMessage();

        $message->shouldReceive('getTo')
            ->zeroOrMoreTimes()
            ->andReturn(array('foo@bar' => 'Foobar', 'zip@button' => 'Zippy'));
        $message->shouldReceive('toByteStream')
            ->once()
            ->with($buf);
        $buf->shouldReceive('initialize')
            ->once();
        $buf->shouldReceive('terminate')
            ->once();
        $buf->shouldReceive('setWriteTranslations')
            ->once()
            ->with(array("\r\n" => "\n"));
        $buf->shouldReceive('setWriteTranslations')
            ->once()
            ->with(array());

        $sendmail->setCommand('/usr/sbin/sendmail -oi -t');
        $this->assertSame(2, $sendmail->send($message));
    }

    public function testSendingMessageRegeneratesId()
    {
        $buf = $this->_getBuffer();
        $sendmail = $this->_getSendmail($buf);
        $message = $this->_createMessage();

        $message->shouldReceive('getTo')
            ->zeroOrMoreTimes()
            ->andReturn(array('foo@bar' => 'Foobar', 'zip@button' => 'Zippy'));
        $message->shouldReceive('generateId');
        $buf->shouldReceive('initialize')
            ->once();
        $buf->shouldReceive('terminate')
            ->once();
        $buf->shouldReceive('setWriteTranslations')
            ->once()
            ->with(array("\r\n" => "\n", "\n." => "\n.."));
        $buf->shouldReceive('setWriteTranslations')
            ->once()
            ->with(array());

        $sendmail->setCommand('/usr/sbin/sendmail -t');
        $this->assertSame(2, $sendmail->send($message));
    }

    public function testFluidInterface()
    {
        $buf = $this->_getBuffer();
        $sendmail = $this->_getTransport($buf);

        $ref = $sendmail->setCommand('/foo');
        $this->assertSame($ref, $sendmail);
    }
}
