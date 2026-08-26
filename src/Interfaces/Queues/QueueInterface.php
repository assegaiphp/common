<?php

namespace Assegai\Common\Interfaces\Queues;

/**
 * Interface QueueInterface
 *
 * Defines the methods for managing a queue of jobs.
 * @template T of object
 */
interface QueueInterface
{
  /**
   * Adds a job to the queue.
   *
   * @param T $job The job to be added to the queue.
   * @param object|array|null $options Optional parameters for the job, such as priority or delay.
   */
  public function add(object $job, object|array|null $options = null): void;

  /**
   * Processes at most one available job from the queue.
   *
   * The callback receives a hydrated domain object. A successful result must
   * expose that object through getJob(); an empty queue returns a successful
   * result whose job is null. Implementations must settle the transport
   * delivery only after the callback succeeds, and must capture any Throwable
   * raised while decoding or processing a delivery.
   *
   * @param callable(T): mixed $callback A callback invoked with the hydrated job.
   * @return QueueProcessResultInterface<T> The result of the delivery attempt.
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

  /**
   * Creates a new instance of the queue with the given configuration.
   *
   * @param array $config Configuration options for the queue.
   * @return static A new instance of the queue.
   */
  public static function create(array $config): self;
}
