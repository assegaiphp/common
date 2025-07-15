<?php

namespace Assegai\Common\Enumerations;

/**
 * Enum QueueType
 *
 * Represents the different types of queues that can be used in the application.
 * Each type corresponds to a specific queue implementation.
 */
enum QueueType: string
{
  case RabbitMQ = 'rabbitmq';
  case Redis = 'redis';
  case SQS = 'sqs';
  case Kafka = 'kafka';
  case InMemory = 'in_memory';
  case Beanstalkd = 'beanstalkd';
}
