<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Creates MIME headers.
 *
 * @author Chris Corbyn
 */
class Swift_Mime_SimpleHeaderFactory implements Swift_Mime_HeaderFactory
{
    /**
     * The HeaderEncoder used by these headers
     *
     * @var Swift_Mime_HeaderEncoder
     */
    private $_encoder;

    /**
     * The Encoder used by parameters
     *
     * @var Swift_Encoder
     */
    private $_paramEncoder;

    /**
     * The Email Validator
     *
     * @var Swift_EmailValidatorBridge
     */
    private $_emailValidator;

    /**
     * The charset of created Headers
     *
     * @var null|string
     */
    private $_charset;

    /**
     * Creates a new SimpleHeaderFactory using $encoder and $paramEncoder.
     *
     * @param Swift_Mime_HeaderEncoder   $encoder
     * @param Swift_Encoder              $paramEncoder
     * @param Swift_EmailValidatorBridge $emailValidator
     * @param string|null                $charset
     */
    public function __construct(Swift_Mime_HeaderEncoder $encoder, Swift_Encoder $paramEncoder, Swift_EmailValidatorBridge $emailValidator, $charset = null)
    {
        $this->_encoder = $encoder;
        $this->_paramEncoder = $paramEncoder;
        $this->_emailValidator = $emailValidator;

        $this->charsetChanged($charset);
    }

    /**
     * Create a new Mailbox Header with a list of $addresses.
     *
     * @param string            $name
     * @param array|string|null $addresses
     *
     * @return Swift_Mime_Header
     */
    public function createMailboxHeader($name, $addresses = null)
    {
        $header = new Swift_Mime_Headers_MailboxHeader($name, $this->_encoder, $this->_emailValidator);
        
        if ($addresses !== null) {
            $header->setFieldBodyModel($addresses);
        }
        
        $this->_setHeaderCharset($header);

        return $header;
    }

    /**
     * Create a new Date header using $dateTime.
     *
     * @param string   $name
     * @param DateTimeInterface|null $dateTime
     *
     * @return Swift_Mime_Header
     */
    public function createDateHeader($name, DateTimeInterface $dateTime = null)
    {
        $header = new Swift_Mime_Headers_DateHeader($name);
        
        if ($dateTime !== null) {
            $header->setFieldBodyModel($dateTime);
        }
        
        $this->_setHeaderCharset($header);

        return $header;
    }

    /**
     * Create a new basic text header with $name and $value.
     *
     * @param string $name
     * @param string $value
     *
     * @return Swift_Mime_Header
     */
    public function createTextHeader($name, $value = null)
    {
        $header = new Swift_Mime_Headers_UnstructuredHeader($name, $this->_encoder);
        
        if ($value !== null) {
            $header->setFieldBodyModel($value);
        }
        
        $this->_setHeaderCharset($header);

        return $header;
    }

    /**
     * Create a new ParameterizedHeader with $name, $value and $params.
     *
     * @param string $name
     * @param string $value
     * @param array  $params
     *
     * @return Swift_Mime_Headers_ParameterizedHeader
     */
    public function createParameterizedHeader($name, $value = null, $params = array())
    {
        $parameterEncoding = null;
        if (Swift::strtolowerWithStaticCache($name) === 'content-disposition') {
            $parameterEncoding = $this->_paramEncoder;
        }

        $header = new Swift_Mime_Headers_ParameterizedHeader($name, $this->_encoder, $parameterEncoding);

        if ($value !== null) {
            $header->setFieldBodyModel($value);
        }

        foreach ($params as $k => $v) {
            $header->setParameter($k, $v);
        }

        $this->_setHeaderCharset($header);

        return $header;
    }

    /**
     * Create a new ID header for Message-ID or Content-ID.
     *
     * @param string            $name
     * @param string|array|null $ids
     *
     * @return Swift_Mime_Header
     */
    public function createIdHeader($name, $ids = null)
    {
        $header = new Swift_Mime_Headers_IdentificationHeader($name, $this->_emailValidator);

        if (!empty($ids)) {
            $header->setFieldBodyModel($ids);
        }

        $this->_setHeaderCharset($header);

        return $header;
    }

    /**
     * Create a new Path header with an address (path) in it.
     *
     * @param string $name
     * @param string $path
     *
     * @return Swift_Mime_Header
     */
    public function createPathHeader($name, $path = null)
    {
        $header = new Swift_Mime_Headers_PathHeader($name, $this->_emailValidator);
        
        if ($path !== null) {
            $header->setFieldBodyModel($path);
        }
        
        $this->_setHeaderCharset($header);

        return $header;
    }

    /**
     * Notify this observer that the entity's charset has changed.
     *
     * @param string|null $charset
     *
     * @return bool
     */
    public function charsetChanged($charset)
    {
        if ($charset) {
            $this->_charset = $charset;
            $this->_encoder->charsetChanged($charset);
            $this->_paramEncoder->charsetChanged($charset);
            
            return true;
        }
        
        return false;
    }

    /**
     * Make a deep copy of object.
     */
    public function __clone()
    {
        $this->_encoder = clone $this->_encoder;
        $this->_paramEncoder = clone $this->_paramEncoder;
    }

    /**
     * Apply the charset to the Header
     *
     * @param Swift_Mime_Header $header
     */
    private function _setHeaderCharset(Swift_Mime_Header $header)
    {
        if (null !== $this->_charset) {
            $header->setCharset($this->_charset);
        }
    }
}
