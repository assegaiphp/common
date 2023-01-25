<?php

namespace Assegai\Common\Util\Exceptions;

use Assegai\Core\Util\Debug\Console\Enumerations\Color;
use Exception;
use Psr\Http\Client\ClientExceptionInterface;

class ClientException extends Exception implements ClientExceptionInterface
{
  public function __construct(string $message)
  {
    parent::__construct(sprintf("%sClient Error: %s%s%s", Color::RED->value, $message, Color::RESET->value, PHP_EOL));
  }

  public function __toString(): string
  {
    return $this->getMessage();
  }
}