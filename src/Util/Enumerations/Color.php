<?php

namespace Assegai\Common\Util\Enumerations;

/**
 * An enumeration of colors for use in the terminal.
 *
 * @package Assegai\Common\Util\Enumerations
 */
enum Color: string
{
  case RESET = "\e[0m";
  case BLACK = "\e[0;30m";
  case RED = "\e[0;31m";
  case GREEN = "\e[0;32m";
  case YELLOW = "\e[0;33m";
  case BLUE = "\e[0;34m";
  case MAGENTA = "\e[0;35m";
  case CYAN = "\e[0;36m";
  case WHITE = "\e[0;37m";
  case BRIGHT_BLACK = "\e[1;30m";
  case BRIGHT_RED = "\e[1;31m";
  case BRIGHT_GREEN = "\e[1;32m";
  case BRIGHT_YELLOW = "\e[1;33m";
  case BRIGHT_BLUE = "\e[1;34m";
  case BRIGHT_MAGENTA = "\e[1;35m";
  case BRIGHT_CYAN = "\e[1;36m";
  case BRIGHT_WHITE = "\e[1;37m";
  case DARK_BLACK = "\e[2;30m";
  case DARK_RED = "\e[2;31m";
  case DARK_GREEN = "\e[2;32m";
  case DARK_YELLOW = "\e[2;33m";
  case DARK_BLUE = "\e[2;34m";
  case DARK_MAGENTA = "\e[2;35m";
  case DARK_CYAN = "\e[2;36m";
  case DARK_WHITE = "\e[2;37m";

  public function name(): string
  {
    return match($this) {
      self::RESET => 'none',
      self::BLACK => 'black',
      self::RED => 'red',
      self::GREEN => 'green',
      self::YELLOW => 'yellow',
      self::BLUE => 'blue',
      self::MAGENTA => 'magenta',
      self::CYAN => 'cyan',
      self::WHITE => 'white',
      self::BRIGHT_BLACK => 'bright black',
      self::BRIGHT_RED => 'bright red',
      self::BRIGHT_GREEN => 'bright green',
      self::BRIGHT_YELLOW => 'bright yellow',
      self::BRIGHT_BLUE => 'bright blue',
      self::BRIGHT_MAGENTA => 'bright magenta',
      self::BRIGHT_CYAN => 'bright cyan',
      self::BRIGHT_WHITE => 'bright white',
      self::DARK_BLACK => 'dark black',
      self::DARK_RED => 'dark red',
      self::DARK_GREEN => 'dark green',
      self::DARK_YELLOW => 'dark yellow',
      self::DARK_BLUE => 'dark blue',
      self::DARK_MAGENTA => 'dark magenta',
      self::DARK_CYAN => 'dark cyan',
      self::DARK_WHITE => 'dark white'
    };
  }
}
