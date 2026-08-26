<?php

namespace Assegai\Common\Queues;

use Assegai\Common\Interfaces\Queues\QueueProcessResultInterface;
use Throwable;

/**
 * Canonical result for one queue delivery attempt.
 *
 * @template T of object
 * @implements QueueProcessResultInterface<T>
 */
class QueueProcessResult implements QueueProcessResultInterface
{
  /**
   * @param Throwable[] $errors
   * @param T|null $job
   */
  public function __construct(
    protected mixed $data = null,
    protected array $errors = [],
    protected ?object $job = null,
  ) {
  }

  public function getData(): mixed
  {
    return $this->data;
  }

  public function isOk(): bool
  {
    return !$this->isError();
  }

  public function isError(): bool
  {
    return $this->errors !== [];
  }

  /**
   * @return Throwable[]
   */
  public function getErrors(): array
  {
    return $this->errors;
  }

  public function getNextError(): ?Throwable
  {
    return $this->errors[0] ?? null;
  }

  public function getJob(): ?object
  {
    return $this->job;
  }
}
