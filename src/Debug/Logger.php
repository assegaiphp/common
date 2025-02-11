<?php

namespace Assegai\Common\Debug;

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ConsoleLogger - A logger implementation for the console.
 *
 * @package Assegai\Common\Debug
 */
class Logger implements LoggerInterface
{
  /**
   * Constructs a ConsoleLogger object.
   *
   * @param OutputInterface $output
   */

  public function __construct(protected OutputInterface $output)
  {
  }

  /**
   * @inheritDoc
   */
  public function emergency($message, array $context = []): void
  {
    $this->output->writeln("\e[31m[EMERGENCY]\e[0m $message");
  }

  /**
   * @inheritDoc
   */
  public function alert($message, array $context = []): void
  {
    $this->output->writeln("\e[31m[ALERT]\e[0m $message");
  }

  /**
   * @inheritDoc
   */
  public function critical($message, array $context = []): void
  {
    $this->output->writeln("\e[31m[CRITICAL]\e[0m $message");
  }

  /**
   * @inheritDoc
   */
  public function error($message, array $context = []): void
  {
    $this->output->writeln("\e[31m[ERROR]\e[0m $message");
  }

  /**
   * @inheritDoc
   */
  public function warning($message, array $context = []): void
  {
    $this->output->writeln("\e[33m[WARNING]\e[0m $message");
  }

  /**
   * @inheritDoc
   */
  public function notice($message, array $context = []): void
  {
    $this->output->writeln("\e[33m[NOTICE]\e[0m $message");
  }

  /**
   * @inheritDoc
   */
  public function info($message, array $context = []): void
  {
    $this->output->writeln("\e[34m[INFO]\e[0m $message");
  }

  /**
   * @inheritDoc
   */
  public function debug($message, array $context = []): void
  {
    $this->output->writeln("\e[2;37m[DEBUG]\e[0m $message");
  }

  /**
   * @inheritDoc
   */
  public function log($level, $message, array $context = []): void
  {
    $this->output->writeln("\e[2;37m[$level]\e[0m $message");
  }
}