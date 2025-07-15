<?php

namespace Assegai\Common\Exceptions;

use Throwable;

/**
 * Class HttpException
 *
 * Represents an exception that occurs during HTTP operations.
 * This class extends the base AssegaiException class and can be used to throw custom HTTP-related exceptions.
 */
class HttpException extends AssegaiException
{
  /**
   * Constructs a new HttpException.
   *
   * @param string $message The error message.
   * @param int $code The HTTP status code (default is 500).
   * @param Throwable|null $previous The previous exception (if any).
   */
  public function __construct(string $message = 'An error occurred during the HTTP operation.', int $code = 500, ?Throwable $previous = null)
  {
    parent::__construct($message, $code, $previous);
  }
}