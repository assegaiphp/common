<?php

namespace Assegai\Common\Util\Exceptions;

class PathException extends UtilException
{
  public function __construct(string $message = "")
  {
    parent::__construct($message, "Path Error");
  }
}