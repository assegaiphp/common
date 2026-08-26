<?php

namespace Assegai\Common\Interfaces\Queues;

/**
 * Encodes queue jobs for transport and restores them for a processor.
 */
interface QueueJobCodecInterface
{
  /**
   * @throws \Assegai\Common\Exceptions\QueueException
   */
  public function encode(object $job): string;

  /**
   * @param class-string|null $expectedClass
   * @throws \Assegai\Common\Exceptions\QueueException
   */
  public function decode(string $payload, ?string $expectedClass = null): object;
}
