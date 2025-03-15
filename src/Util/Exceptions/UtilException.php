<?php

namespace Assegai\Common\Util\Exceptions;

use Assegai\Common\Util\Enumerations\Color;
use Exception;

/**
 * Class UtilException
 *
 * @package Assegai\Common\Util\Exceptions
 */
class UtilException extends Exception
{
  public function __construct(string $message = "", string $prefix = "Util Error")
  {
    parent::__construct(sprintf("%s%s: %s%s%s",
      Color::RED->value, $prefix, $message, Color::RESET->value, PHP_EOL));
  }
}