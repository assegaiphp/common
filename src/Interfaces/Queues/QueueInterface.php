<?php

namespace Assegai\Common\Interfaces\Queues;

/**
 * Interface QueueInterface
 *
 * Defines the methods for managing a queue of jobs.
 * @template T
 * @template-extends QueueProcessResultInterface<T>
 */
interface QueueInterface
{
  /**
   * Adds a job to the queue.
   *
   * @param object<T> $job The job to be added to the queue. It can be an object or an array.
   * @param object|array|null $options Optional parameters for the job, such as priority or delay.
   */
  public function add(object $job, object|array|null $options = null): void;

  /**
   * Processes the next job in the queue.
   *
   * @param callable $callback A callback function that will be called with the job to process.
   *
   * @return QueueProcessResultInterface The result of processing the job, or null if no jobs are available.
   */
  public function process(callable $callback): QueueProcessResultInterface;

  /**
   * Returns the name of the queue.
   *
   * @return string The name of the queue.
   */
  public function getName(): string;

  /**
   * Returns the number of jobs in the queue.
   *
   * @return int The number of jobs in the queue.
   */
  public function getTotalJobs(): int;
}