<?php

namespace Assegai\Common\Util\Exceptions;

use Assegai\Core\Util\Debug\Console\Enumerations\Color;

class UtilException extends \Exception
{
  public function __construct(string $message = "", string $prefix = "Util Error")
  {
    parent::__construct(sprintf("%s%s: %s%s%s",
      Color::RED->value, $prefix, $message, Color::RESET->value, PHP_EOL));
  }
}