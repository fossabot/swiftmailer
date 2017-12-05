<?php

use voku\helper\UTF8;

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * A MIME part, in a multipart message.
 *
 * @author Chris Corbyn
 */
class Swift_Mime_MimePart extends Swift_Mime_SimpleMimeEntity
{
    /**
     * The format parameter last specified by the user
     */
    protected $_userFormat;

    /**
     * The charset last specified by the user
     *
     * @var string
     */
    protected $_userCharset;

    /**
     * The delsp parameter last specified by the user
     */
    protected $_userDelSp;

    /**
     * The nesting level of this MimePart
     *
     * @var int
     */
    private $_nestingLevel;

    /**
     * Create a new MimePart with $headers, $encoder and $cache.
     *
     * @param Swift_Mime_HeaderSet       $headers
     * @param Swift_Mime_ContentEncoder  $encoder
     * @param Swift_KeyCache             $cache
     * @param Swift_IdGenerator          $idGenerator
     * @param string                     $charset
     */
    public function __construct(Swift_Mime_HeaderSet $headers, Swift_Mime_ContentEncoder $encoder, Swift_KeyCache $cache, Swift_IdGenerator $idGenerator, $charset = null)
    {
        parent::__construct($headers, $encoder, $cache, $idGenerator);

        $this->_setNestingLevel(self::LEVEL_ALTERNATIVE);

        $this->setContentType('text/plain');

        if (null !== $charset) {
            $this->setCharset($charset);
        }
    }

    /**
     * Set the body of this entity, either as a string, or as an instance of
     * {@link Swift_OutputByteStream}.
     *
     * @param mixed  $body
     * @param string $contentType optional
     * @param string $charset     optional
     *
     * @return $this
     */
    public function setBody($body, $contentType = null, $charset = null)
    {
        if ($charset) {
            $this->setCharset($charset);
        }

        if (is_string($body)) {
            $body = $this->_convertString($body);
        }

        parent::setBody($body, $contentType);

        return $this;
    }

    /**
     * Get the character set of this entity.
     *
     * @return string
     */
    public function getCharset()
    {
        return (string)$this->_getHeaderParameter('Content-Type', 'charset');
    }

    /**
     * Set the character set of this entity.
     *
     * @param string $charset
     *
     * @return $this
     */
    public function setCharset($charset)
    {
        $this->_setHeaderParameter('Content-Type', 'charset', $charset);

        if ($charset !== $this->_userCharset) {
            $this->_clearCache();

            $this->_userCharset = $charset;

            parent::charsetChanged($charset);
        }

        return $this;
    }

    /**
     * Get the format of this entity (i.e. flowed or fixed).
     *
     * @return string
     */
    public function getFormat()
    {
        return $this->_getHeaderParameter('Content-Type', 'format');
    }

    /**
     * Set the format of this entity (flowed or fixed).
     *
     * @param string $format
     *
     * @return $this
     */
    public function setFormat($format)
    {
        $this->_setHeaderParameter('Content-Type', 'format', $format);
        $this->_userFormat = $format;

        return $this;
    }

    /**
     * Test if delsp is being used for this entity.
     *
     * @return bool
     */
    public function getDelSp()
    {
        return 'yes' === $this->_getHeaderParameter('Content-Type', 'delsp');
    }

    /**
     * Turn delsp on or off for this entity.
     *
     * @param bool $delsp
     *
     * @return $this
     */
    public function setDelSp($delsp = true)
    {
        $this->_setHeaderParameter('Content-Type', 'delsp', $delsp ? 'yes' : null);
        $this->_userDelSp = $delsp;

        return $this;
    }

    /**
     * Get the nesting level of this entity.
     *
     * @see LEVEL_TOP, LEVEL_ALTERNATIVE, LEVEL_MIXED, LEVEL_RELATED
     *
     * @return int
     */
    public function getNestingLevel()
    {
        return $this->_nestingLevel;
    }

    /**
     * Receive notification that the charset has changed on this document, or a
     * parent document.
     *
     * @param string|null $charset
     */
    public function charsetChanged($charset)
    {
        $this->setCharset($charset);
    }

    /**
     * Fix the content-type and encoding of this entity
     */
    protected function _fixHeaders()
    {
        parent::_fixHeaders();
        if (count($this->getChildren())) {
            $this->_setHeaderParameter('Content-Type', 'charset', null);
            $this->_setHeaderParameter('Content-Type', 'format', null);
            $this->_setHeaderParameter('Content-Type', 'delsp', null);
        } else {
            $this->setCharset($this->_userCharset);
            $this->setFormat($this->_userFormat);
            $this->setDelSp($this->_userDelSp);
        }
    }

    /**
     * Set the nesting level of this entity
     *
     * @param int $level
     */
    protected function _setNestingLevel(int $level)
    {
        $this->_nestingLevel = $level;
    }

    /**
     * Encode charset
     *
     * @param string $string
     *
     * @return string
     */
    protected function _convertString(string $string): string
    {
        return UTF8::encode($this->getCharset(), $string, false);
    }
}
