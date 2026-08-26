<?php

namespace Assegai\Common\Interfaces\Queues;

use Throwable;

/**
 * Interface QueueProcessResultInterface
 *
 * Defines the methods for handling the result of processing a job in a queue.
 * @template T of object
 */
interface QueueProcessResultInterface
{
  /**
   * Returns the result data of processing the job.
   *
   * @return mixed The result data of processing the job.
   */
  public function getData(): mixed;

  /**
   * Returns whether the job was processed successfully.
   *
   * @return bool True if the job was processed successfully, false otherwise.
   */
  public function isOk(): bool;

  /**
   * Returns whether there was an error during job processing.
   *
   * @return bool True if there was an error, false otherwise.
   */
  public function isError(): bool;

  /**
   * Returns the errors encountered during job processing.
   *
   * @return Throwable[] An array of error messages or exceptions encountered during processing.
   */
  public function getErrors(): array;

  /**
   * Returns the next error encountered during job processing.
   *
   * @return Throwable|null The next error encountered, or null if no errors occurred.
   */
  public function getNextError(): ?Throwable;

  /**
   * Returns the hydrated job associated with this delivery attempt.
   *
   * This remains available for failed callbacks. Null means that no transport
   * delivery was available or that a job could not be decoded.
   *
   * @return object|null The hydrated job, or null when no job could be produced.
   */
  public function getJob(): ?object;
}
