<?php

namespace Assegai\Common\Interfaces\Queues;

/**
 * Interface QueueProcessorInterface
 *
 * Defines the method for processing a job in a queue.
 * @template T
 */
interface QueueProcessorInterface
{
  /**
   * Processes a job in the queue.
   *
   * @param T $job The job to be processed. It can be an object or an array.
   * @return QueueProcessResultInterface<T> The result of processing the job.
   */
  public function process(mixed $job): QueueProcessResultInterface;
}