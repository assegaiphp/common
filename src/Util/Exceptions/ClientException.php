<?php

namespace Assegai\Common\Util\Exceptions;

use Assegai\Common\Util\Enumerations\Color;
use Exception;
use Psr\Http\Client\ClientExceptionInterface;

/**
 * Class ClientException
 *
 * @package Assegai\Common\Util\Exceptions
 */
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