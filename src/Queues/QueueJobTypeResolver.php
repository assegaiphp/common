<?php

namespace Assegai\Common\Queues;

use Closure;
use ReflectionFunction;
use ReflectionIntersectionType;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionUnionType;

/**
 * Resolves the domain job type declared by a queue processor callback.
 */
final class QueueJobTypeResolver
{
  /**
   * @return class-string|null
   */
  public static function fromCallback(callable $callback): ?string
  {
    $reflection = self::reflect($callback);
    $parameter = $reflection->getParameters()[0] ?? null;

    if (!$parameter instanceof ReflectionParameter) {
      return null;
    }

    $type = $parameter->getType();

    if ($type instanceof ReflectionNamedType) {
      return self::resolveNamedType($type, $parameter);
    }

    if ($type instanceof ReflectionUnionType) {
      $classes = [];

      foreach ($type->getTypes() as $candidate) {
        if (!$candidate instanceof ReflectionNamedType) {
          continue;
        }

        $class = self::resolveNamedType($candidate, $parameter);

        if ($class !== null) {
          $classes[$class] = $class;
        }
      }

      return count($classes) === 1 ? array_values($classes)[0] : null;
    }

    if ($type instanceof ReflectionIntersectionType) {
      return null;
    }

    return null;
  }

  private static function reflect(callable $callback): ReflectionFunction|ReflectionMethod
  {
    if (is_array($callback)) {
      return new ReflectionMethod($callback[0], $callback[1]);
    }

    if (is_string($callback) && str_contains($callback, '::')) {
      return new ReflectionMethod($callback);
    }

    if (is_object($callback) && !$callback instanceof Closure) {
      return new ReflectionMethod($callback, '__invoke');
    }

    return new ReflectionFunction($callback);
  }

  /**
   * @return class-string|null
   */
  private static function resolveNamedType(ReflectionNamedType $type, ReflectionParameter $parameter): ?string
  {
    if ($type->isBuiltin()) {
      return null;
    }

    $name = $type->getName();
    $declaringClass = $parameter->getDeclaringClass();

    if (($name === 'self' || $name === 'static') && $declaringClass !== null) {
      return $declaringClass->getName();
    }

    if ($name === 'parent' && $declaringClass?->getParentClass()) {
      return $declaringClass->getParentClass()->getName();
    }

    /** @var class-string $name */
    return $name;
  }
}
