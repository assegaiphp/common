<?php

namespace Assegai\Common\Util;

use Assegai\Common\Util\Exceptions\PathException;

/**
 *
 */
class Path
{
  /**
   * Constructs a new Path object.
   * @param string $path The filename or directory path.
   */
  public function __construct(private string $path)
  {}

  /**
   * @return $this
   * @throws PathException
   */
  public function make(): self
  {
    if (self::isDirectoryPath($this->path) && !is_dir($this->path))
    {
      if (!mkdir($this->path))
      {
        throw new PathException("Failed to create a directory at $this->path");
      }
    }
    else
    {
      if (!touch($this->path))
      {
        throw new PathException("Failed to create a file at $this->path");
      }
    }

    return $this;
  }

  /**
   * @param string $path
   * @return bool
   */
  public static function isFilePath(string $path): bool
  {
    return boolval(preg_match('/^.*(\.\w{1,4})$/i', $path));
  }

  /**
   * @param string $path
   * @return bool
   */
  public static function isDirectoryPath(string $path): bool
  {
    return !self::isFilePath($path);
  }

  /**
   * Determines whether the given path is an existing directory.
   *
   * @param string $path The path to the directory.
   * @return bool Returns true if the directory exists, false otherwise.
   */
  public static function directoryExists(string $path): bool
  {
    return is_dir($path);
  }

  /**
   * Determines whether the given path is an existing directory.
   *
   * @param string $path The path to the file.
   * @return bool Returns true if the filename exists and is a regular file, false otherwise.
   */
  public static function fileExists(string $path): bool
  {
    return is_file($path);
  }

  /**
   * @param ...$parts
   * @return string
   */
  public static function join(...$parts): string
  {
    $path = '';

    foreach ($parts as $part)
    {
      if (is_string($part))
      {
        $path .= rtrim($part, " \t\n\r\0\x0B" . PATH_SEPARATOR) . PATH_SEPARATOR;
      }
    }

    return sprintf("/%s", ltrim($path, " \t\n\r\0\x0B\/"));
  }
}