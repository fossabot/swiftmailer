<?php

/*
 CharacterStream implementation using an array in Swift Mailer.
 
 This program is free software: you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.
 
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program.  If not, see <http://www.gnu.org/licenses/>.
 
 */

//@require 'Swift/CharacterStream.php';
//@require 'Swift/OutputByteStream.php';

/**
 * A CharacterStream implementation which stores characters in an internal array.
 * @package Swift
 * @subpackage CharacterStream
 * @author Chris Corbyn
 */
class Swift_CharacterStream_ArrayCharacterStream
  implements Swift_CharacterStream
{

  /**
   * The char reader (lazy-loaded) for the current charset.
   * @var Swift_CharacterReader
   * @access private
   */
  private $_charReader;
  
  /**
   * A factory for creatiing CharacterReader instances.
   * @var Swift_CharacterReaderFactory
   * @access private
   */
  private $_charReaderFactory;
  
  /**
   * The character set this stream is using.
   * @var string
   * @access private
   */
  private $_charset;
  
  /**
   * Array of characters.
   * @var string[]
   * @access private
   */
  private $_array = array();
  
  /**
   * The current character offset in the stream.
   * @var int
   * @access private
   */
  private $_offset = 0;
  
  /**
   * Create a new CharacterStream with the given $chars, if set.
   * @param Swift_CharacterReaderFactory $factory for loading validators
   * @param string $charset used in the stream
   * @param mixed $chars as string or array, optional
   */
  public function __construct(Swift_CharacterReaderFactory $factory,
    $charset, $chars = null)
  {
    $this->setCharacterReaderFactory($factory);
    $this->setCharacterSet($charset);
    
    if (is_array($chars))
    {
      $this->_array = $chars;
    }
    elseif (is_string($chars))
    {
      $this->importString($chars);
    }
  }
  
  /**
   * Set the character set used in this CharacterStream.
   * @param string $charset
   */
  public function setCharacterSet($charset)
  {
    $this->_charset = $charset;
    $this->_charReader = null;
  }
  
  /**
   * Set the CharacterReaderFactory for multi charset support.
   * @param Swift_CharacterReaderFactory $factory
   */
  public function setCharacterReaderFactory(
    Swift_CharacterReaderFactory $factory)
  {
    $this->_charReaderFactory = $factory;
  }
  
  /**
   * Overwrite this character stream using the byte sequence in the byte stream.
   * @param Swift_OutputByteStream $os output stream to read from
   */
  public function importByteStream(Swift_OutputByteStream $os)
  {
    if (!isset($this->_charReader))
    {
      $this->_charReader = $this->_charReaderFactory
        ->getReaderFor($this->_charset);
    }
    
    $startLength = $this->_charReader->getInitialByteSize();
    $c = ''; $offset = 0; $need = $startLength;
    
    while (false !== $bytes = $os->read($need))
    {
      $offset += $need;
      $c .= $bytes;
      $need = $this->_charReader->validateCharacter($c);
      if (0 == $need)
      {
        $need = $startLength;
        $this->_array[] = $c;
        $c = '';
      }
      elseif (-1 == $need)
      {
        throw new Exception(
          'Invalid ' . $this->_charset . ' data at byte offset ' . $offset .
          ' (after ' . count($this->_array) . ' chars).'
          );
      }
    }
  }
  
  /**
   * Import a string a bytes into this CharacterStream, overwriting any existing
   * data in the stream.
   * @param string $string
   */
  public function importString($string)
  {
    $this->flushContents();
    $this->write($string);
  }
  
  /**
   * Read $length characters from the stream and move the internal pointer
   * $length further into the stream.
   * @param int $length
   * @return string[]
   */
  public function read($length)
  {
    if ($this->_offset == count($this->_array))
    {
      return false;
    }
    
    $ret = array_slice($this->_array, $this->_offset, $length);
    $this->_offset += count($ret);
    return implode('', $ret);
  }
  
  /**
   * Write $chars to the end of the stream.
   * @param string $chars
   */
  public function write($chars)
  {
    if (!isset($this->_charReader))
    {
      $this->_charReader = $this->_charReaderFactory
        ->getReaderFor($this->_charset);
    }
    
    $startLength = $this->_charReader->getInitialByteSize();
    $c = ''; $offset = 0; $need = $startLength;
    
    while (strlen($chars) > 0)
    {
      $offset += $need;
      $c .= substr($chars, 0, $need);
      $chars = substr($chars, $need);
      $need = $this->_charReader->validateCharacter($c);
      if (0 == $need)
      {
        $need = $startLength;
        $this->_array[] = $c;
        $c = '';
      }
      elseif (-1 == $need)
      {
        throw new Exception(
          'Invalid ' . $this->_charset . ' data at byte offset ' . $offset .
          ' (after ' . count($this->_array) . ' chars).'
          );
      }
    }
  }
  
  /**
   * Move the internal pointer to $charOffset in the stream.
   * @param int $charOffset
   */
  public function setPointer($charOffset)
  {
    if ($charOffset > count($this->_array))
    {
      $charOffset = count($this->_array);
    }
    elseif ($charOffset < 0)
    {
      $charOffset = 0;
    }
    $this->_offset = $charOffset;
  }
  
  /**
   * Empty the stream and reset the internal pointer.
   */
  public function flushContents()
  {
    $this->_offset = 0;
    $this->_array = array();
  }
  
}