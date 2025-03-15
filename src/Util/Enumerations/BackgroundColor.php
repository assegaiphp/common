<?php

namespace Assegai\Common\Util\Enumerations;

/**
 * Enumerates the background colors available for the terminal.
 *
 * @package Assegai\Common\Util\Enumerations
 */
enum BackgroundColor: string
{
  case RESET = "\e[0m";
  case BLACK = "\e[0;40m";
  case RED = "\e[0;41m";
  case GREEN = "\e[0;42m";
  case YELLOW = "\e[0;43m";
  case BLUE = "\e[0;44m";
  case MAGENTA = "\e[0;45m";
  case CYAN = "\e[0;46m";
  case WHITE = "\e[0;47m";
  case BRIGHT_BLACK = "\e[1;40m";
  case BRIGHT_RED = "\e[1;41m";
  case BRIGHT_GREEN = "\e[1;42m";
  case BRIGHT_YELLOW = "\e[1;43m";
  case BRIGHT_BLUE = "\e[1;44m";
  case BRIGHT_MAGENTA = "\e[1;45m";
  case BRIGHT_CYAN = "\e[1;46m";
  case BRIGHT_WHITE = "\e[1;47m";
  case DARK_BLACK = "\e[2;40m";
  case DARK_RED = "\e[2;41m";
  case DARK_GREEN = "\e[2;42m";
  case DARK_YELLOW = "\e[2;43m";
  case DARK_BLUE = "\e[2;44m";
  case DARK_MAGENTA = "\e[2;45m";
  case DARK_CYAN = "\e[2;46m";
  case DARK_WHITE = "\e[2;47m";

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
