<?php

namespace Assegai\Common\Interfaces\Queues;

use Throwable;

/**
 * Interface QueueProcessResultInterface
 *
 * Defines the methods for handling the result of processing a job in a queue.
 * @template T
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
   * Returns the job that was processed.
   *
   * @return object|null The job that was processed, or null if no job was processed.
   */
  public function getJob(): ?object;
}