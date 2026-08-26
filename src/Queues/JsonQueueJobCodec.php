<?php

namespace Assegai\Common\Queues;

use Assegai\Common\Exceptions\QueueException;
use Assegai\Common\Interfaces\Queues\QueueJobCodecInterface;
use BackedEnum;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use ReflectionClass;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionType;
use ReflectionUnionType;
use SplObjectStorage;
use stdClass;
use Throwable;
use UnitEnum;

/**
 * Versioned JSON codec for queue jobs.
 *
 * New messages contain the top-level job class. Legacy JSON objects remain
 * readable and can be hydrated when the processor declares a concrete type.
 */
final class JsonQueueJobCodec implements QueueJobCodecInterface
{
  public const int VERSION = 1;
  private const string ENVELOPE_KEY = '_assegai_queue';
  private const string VALUE_KEY = '_assegai_value';

  public function encode(object $job): string
  {
    try {
      if ($job instanceof UnitEnum || $job instanceof DateTimeInterface) {
        throw new QueueException('Queue jobs must be domain objects, not enum or date values.');
      }

      $seen = new SplObjectStorage();
      $payload = $this->normalizeObject($job, $seen);

      return json_encode([
        self::ENVELOPE_KEY => [
          'version' => self::VERSION,
          'job' => $job::class,
        ],
        'payload' => $payload,
      ], JSON_THROW_ON_ERROR | JSON_PRESERVE_ZERO_FRACTION);
    } catch (Throwable $throwable) {
      throw $this->wrap('Failed to encode queue job.', $throwable);
    }
  }

  public function decode(string $payload, ?string $expectedClass = null): object
  {
    try {
      $decoded = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);
      $isEnvelope = is_array($decoded) && array_key_exists(self::ENVELOPE_KEY, $decoded);
      $encodedClass = null;
      $jobPayload = $decoded;

      if ($isEnvelope) {
        $metadata = $decoded[self::ENVELOPE_KEY];

        if (!is_array($metadata) || ($metadata['version'] ?? null) !== self::VERSION) {
          throw new QueueException('Unsupported or malformed queue job envelope.');
        }

        $encodedClass = $metadata['job'] ?? null;
        $jobPayload = $decoded['payload'] ?? null;

        if (!is_string($encodedClass) || trim($encodedClass) === '') {
          throw new QueueException('Queue job envelope is missing its job class.');
        }
      }

      $expectedClass = $this->normalizeExpectedClass($expectedClass);
      $targetClass = $this->resolveTargetClass($encodedClass, $expectedClass);

      if ($targetClass === null) {
        return $this->toUntypedObject($jobPayload, $isEnvelope);
      }

      return $this->hydrateObject($targetClass, $jobPayload, $isEnvelope);
    } catch (Throwable $throwable) {
      throw $this->wrap('Failed to decode queue job.', $throwable);
    }
  }

  /**
   * @return array<string, mixed>
   */
  private function normalizeObject(object $object, SplObjectStorage $seen): array
  {
    if (isset($seen[$object])) {
      throw new QueueException('Queue jobs cannot contain circular object references.');
    }

    $seen[$object] = true;

    try {
      $values = [];
      $reflection = new ReflectionClass($object);

      foreach ($this->instanceProperties($reflection) as $property) {
        if (!$property->isInitialized($object)) {
          continue;
        }

        $values[$property->getName()] = $this->normalizeValue($property->getValue($object), $seen);
      }

      foreach (get_object_vars($object) as $name => $value) {
        if (!array_key_exists($name, $values)) {
          $values[$name] = $this->normalizeValue($value, $seen);
        }
      }

      return $values;
    } finally {
      unset($seen[$object]);
    }
  }

  /**
   * Returns every instance property in its declaring scope, including private
   * properties inherited from ancestor classes.
   *
   * @return array<string, ReflectionProperty>
   */
  private function instanceProperties(ReflectionClass $reflection): array
  {
    $properties = [];

    for ($scope = $reflection; $scope !== false; $scope = $scope->getParentClass()) {
      foreach ($scope->getProperties() as $property) {
        if ($property->isStatic() || $property->getDeclaringClass()->getName() !== $scope->getName()) {
          continue;
        }

        $name = $property->getName();

        if (isset($properties[$name])) {
          throw new QueueException(
            "Queue job class '{$reflection->getName()}' contains multiple instance properties named '{$name}'."
          );
        }

        $properties[$name] = $property;
      }
    }

    return $properties;
  }

  private function normalizeValue(mixed $value, SplObjectStorage $seen): mixed
  {
    if (is_resource($value)) {
      throw new QueueException('Queue jobs cannot contain resource values.');
    }

    if ($value instanceof BackedEnum) {
      return [self::VALUE_KEY => [
        'type' => 'enum',
        'class' => $value::class,
        'value' => $value->value,
      ]];
    }

    if ($value instanceof UnitEnum) {
      return [self::VALUE_KEY => [
        'type' => 'enum',
        'class' => $value::class,
        'value' => $value->name,
      ]];
    }

    if ($value instanceof DateTimeInterface) {
      return [self::VALUE_KEY => [
        'type' => 'datetime',
        'class' => $value::class,
        'value' => $value->format(DateTimeInterface::ATOM),
        'timezone' => $value->getTimezone()->getName(),
      ]];
    }

    if (is_array($value)) {
      $items = [];

      foreach ($value as $key => $item) {
        $items[$key] = $this->normalizeValue($item, $seen);
      }

      return [self::VALUE_KEY => [
        'type' => 'array',
        'items' => $items,
      ]];
    }

    if (is_object($value)) {
      return [self::VALUE_KEY => [
        'type' => 'object',
        'class' => $value::class,
        'properties' => $this->normalizeObject($value, $seen),
      ]];
    }

    return $value;
  }

  /**
   * @param class-string|null $encodedClass
   * @param class-string|null $expectedClass
   * @return class-string|null
   */
  private function resolveTargetClass(?string $encodedClass, ?string $expectedClass): ?string
  {
    if ($expectedClass === null) {
      return null;
    }

    if (!$this->typeExists($expectedClass)) {
      throw new QueueException("Queue processor job type '{$expectedClass}' does not exist.");
    }

    if ($encodedClass === null) {
      return $expectedClass;
    }

    if (!$this->typeExists($encodedClass)) {
      throw new QueueException("Encoded queue job class '{$encodedClass}' does not exist.");
    }

    if (!is_a($encodedClass, $expectedClass, true)) {
      throw new QueueException(
        "Encoded queue job '{$encodedClass}' is not compatible with processor type '{$expectedClass}'."
      );
    }

    return $encodedClass;
  }

  /**
   * @param class-string $class
   */
  private function hydrateObject(string $class, mixed $payload, bool $normalized): object
  {
    if (!is_array($payload)) {
      throw new QueueException("Queue job payload for '{$class}' must be a JSON object.");
    }

    if ($class === stdClass::class) {
      return $this->toUntypedObject($payload, $normalized);
    }

    if (!class_exists($class)) {
      throw new QueueException("Queue job class '{$class}' does not exist.");
    }

    $reflection = new ReflectionClass($class);

    if (!$reflection->isInstantiable()) {
      throw new QueueException("Queue job class '{$class}' is not instantiable.");
    }

    $constructor = $reflection->getConstructor();
    $properties = $this->instanceProperties($reflection);
    $consumed = [];
    $arguments = [];

    if ($constructor !== null) {
      foreach ($constructor->getParameters() as $parameter) {
        $name = $parameter->getName();

        if (!array_key_exists($name, $payload)) {
          if ($parameter->isDefaultValueAvailable()) {
            $arguments[] = $parameter->getDefaultValue();
            continue;
          }

          if ($parameter->allowsNull()) {
            $arguments[] = null;
            continue;
          }

          throw new QueueException("Queue job '{$class}' is missing constructor value '{$name}'.");
        }

        $scope = $parameter->getDeclaringClass() ?? $reflection;
        $value = $this->hydrateValue($payload[$name], $parameter->getType(), $scope, $normalized);
        $consumed[$name] = true;

        if ($parameter->isVariadic()) {
          if (!is_array($value)) {
            throw new QueueException("Variadic queue job value '{$class}::{$name}' must be an array.");
          }

          array_push($arguments, ...$value);
          continue;
        }

        $arguments[] = $value;
      }
    }

    $job = $reflection->newInstanceArgs($arguments);

    foreach ($payload as $name => $value) {
      $property = $properties[(string) $name] ?? null;

      if (isset($consumed[$name]) || !$property instanceof ReflectionProperty) {
        continue;
      }

      if ($property->isReadOnly() && $property->isInitialized($job)) {
        continue;
      }

      $property->setValue(
        $job,
        $this->hydrateValue($value, $property->getType(), $property->getDeclaringClass(), $normalized)
      );
    }

    return $job;
  }

  private function hydrateValue(
    mixed $value,
    ?ReflectionType $type,
    ReflectionClass $scope,
    bool $normalized,
  ): mixed {
    if ($type instanceof ReflectionUnionType) {
      return $this->hydrateUnion($value, $type, $scope, $normalized);
    }

    if ($type instanceof ReflectionIntersectionType) {
      return $this->hydrateIntersection($value, $type, $scope, $normalized);
    }

    if ($type instanceof ReflectionNamedType) {
      return $this->hydrateNamed($value, $type, $scope, $normalized);
    }

    return $this->hydrateUntypedValue($value, $normalized);
  }

  private function hydrateUnion(
    mixed $value,
    ReflectionUnionType $type,
    ReflectionClass $scope,
    bool $normalized,
  ): mixed {
    $errors = [];

    foreach ($type->getTypes() as $candidate) {
      try {
        return $this->hydrateValue($value, $candidate, $scope, $normalized);
      } catch (Throwable $throwable) {
        $errors[] = $throwable;
      }
    }

    throw new QueueException(
      "Queue job value does not match union type '{$type}'.",
      previous: $errors[0] ?? null,
    );
  }

  private function hydrateIntersection(
    mixed $value,
    ReflectionIntersectionType $type,
    ReflectionClass $scope,
    bool $normalized,
  ): object {
    if ($normalized && $this->isValueEnvelope($value)) {
      $metadata = $value[self::VALUE_KEY];

      if (($metadata['type'] ?? null) !== 'object') {
        throw new QueueException("Queue job value does not match intersection type '{$type}'.");
      }

      $expectedClasses = [];

      foreach ($type->getTypes() as $member) {
        $expectedClasses[] = $this->resolveTypeName($member->getName(), $scope);
      }

      return $this->hydrateObjectEnvelope($metadata, $expectedClasses);
    }

    $candidate = $this->hydrateUntypedValue($value, $normalized);

    if (!is_object($candidate)) {
      throw new QueueException("Queue job value does not match intersection type '{$type}'.");
    }

    foreach ($type->getTypes() as $member) {
      $class = $this->resolveTypeName($member->getName(), $scope);

      if (!$candidate instanceof $class) {
        throw new QueueException("Queue job value does not implement '{$class}'.");
      }
    }

    return $candidate;
  }

  private function hydrateNamed(
    mixed $value,
    ReflectionNamedType $type,
    ReflectionClass $scope,
    bool $normalized,
  ): mixed {
    if ($value === null) {
      if ($type->allowsNull() || $type->getName() === 'null') {
        return null;
      }

      throw new QueueException("Queue job value cannot be null for type '{$type->getName()}'.");
    }

    if ($normalized && $this->isValueEnvelope($value)) {
      return $this->hydrateValueEnvelope($value[self::VALUE_KEY], $type, $scope);
    }

    if ($type->isBuiltin()) {
      return $this->hydrateBuiltin($value, $type->getName(), $normalized);
    }

    $class = $this->resolveTypeName($type->getName(), $scope);

    if ($value instanceof $class) {
      return $value;
    }

    if (is_a($class, BackedEnum::class, true)) {
      /** @var class-string<BackedEnum> $class */
      return $class::from($value);
    }

    if (is_a($class, UnitEnum::class, true)) {
      if (!is_string($value) || !defined($class . '::' . $value)) {
        throw new QueueException("Queue job enum case '{$class}::{$value}' does not exist.");
      }

      return constant($class . '::' . $value);
    }

    if (is_a($class, DateTimeInterface::class, true)) {
      return $this->hydrateDateTime($class, $value, null);
    }

    return $this->hydrateObject($class, $value, $normalized);
  }

  private function hydrateBuiltin(mixed $value, string $type, bool $normalized): mixed
  {
    if (!$normalized && ($type === 'array' || $type === 'iterable') && is_array($value)) {
      return $value;
    }

    $value = $this->hydrateUntypedValue($value, $normalized);

    return match ($type) {
      'mixed' => $value,
      'array' => is_array($value)
        ? $value
        : throw new QueueException('Queue job value must be an array.'),
      'object' => is_object($value)
        ? $value
        : throw new QueueException('Queue job value must be an object.'),
      'iterable' => is_array($value) || $value instanceof \Traversable
        ? $value
        : throw new QueueException('Queue job value must be iterable.'),
      'string' => is_string($value)
        ? $value
        : throw new QueueException('Queue job value must be a string.'),
      'int' => is_int($value)
        ? $value
        : throw new QueueException('Queue job value must be an integer.'),
      'float' => is_float($value) || is_int($value)
        ? (float) $value
        : throw new QueueException('Queue job value must be a float.'),
      'bool' => is_bool($value)
        ? $value
        : throw new QueueException('Queue job value must be a boolean.'),
      'true' => $value === true
        ? true
        : throw new QueueException('Queue job value must be true.'),
      'false' => $value === false
        ? false
        : throw new QueueException('Queue job value must be false.'),
      'null' => $value === null
        ? null
        : throw new QueueException('Queue job value must be null.'),
      default => throw new QueueException("Unsupported queue job value type '{$type}'."),
    };
  }

  /**
   * @param array<string, mixed> $metadata
   */
  private function hydrateValueEnvelope(
    array $metadata,
    ReflectionNamedType $expectedType,
    ReflectionClass $scope,
  ): mixed {
    $kind = $metadata['type'] ?? null;

    if ($kind === 'array') {
      $items = $metadata['items'] ?? null;

      if (!is_array($items)) {
        throw new QueueException('Malformed array value in queue job envelope.');
      }

      if (!$expectedType->isBuiltin() || !in_array($expectedType->getName(), ['array', 'iterable', 'mixed'], true)) {
        throw new QueueException("Queue job array is not compatible with '{$expectedType->getName()}'.");
      }

      $hydrated = [];

      foreach ($items as $key => $item) {
        $hydrated[$key] = $this->hydrateUntypedValue($item, true);
      }

      return $hydrated;
    }

    if ($kind === 'object') {
      $properties = $metadata['properties'] ?? null;
      $encodedClass = $metadata['class'] ?? null;

      if (!is_array($properties) || !is_string($encodedClass)) {
        throw new QueueException('Malformed object value in queue job envelope.');
      }

      if ($expectedType->isBuiltin()) {
        if (!in_array($expectedType->getName(), ['object', 'mixed'], true)) {
          throw new QueueException("Queue job object is not compatible with '{$expectedType->getName()}'.");
        }

        return $this->toUntypedObject($properties, true);
      }

      $expectedClass = $this->resolveTypeName($expectedType->getName(), $scope);

      return $this->hydrateObjectEnvelope($metadata, [$expectedClass]);
    }

    if ($kind === 'enum') {
      $enumClass = $metadata['class'] ?? null;
      $enumValue = $metadata['value'] ?? null;

      if (!is_string($enumClass) || !enum_exists($enumClass)) {
        throw new QueueException('Malformed enum value in queue job envelope.');
      }

      if ($expectedType->isBuiltin()) {
        if (!in_array($expectedType->getName(), ['object', 'mixed'], true)) {
          throw new QueueException("Queue job enum is not compatible with '{$expectedType->getName()}'.");
        }
      } else {
        $expectedClass = $this->resolveTypeName($expectedType->getName(), $scope);

        if (!is_a($enumClass, $expectedClass, true)) {
          throw new QueueException("Encoded enum '{$enumClass}' is not compatible with '{$expectedClass}'.");
        }
      }

      if (is_a($enumClass, BackedEnum::class, true)) {
        /** @var class-string<BackedEnum> $enumClass */
        return $enumClass::from($enumValue);
      }

      if (!is_string($enumValue) || !defined($enumClass . '::' . $enumValue)) {
        throw new QueueException("Encoded enum case '{$enumClass}::{$enumValue}' does not exist.");
      }

      return constant($enumClass . '::' . $enumValue);
    }

    if ($kind === 'datetime') {
      $dateClass = $metadata['class'] ?? DateTimeImmutable::class;
      $dateValue = $metadata['value'] ?? null;
      $timezone = $metadata['timezone'] ?? null;

      if (!is_string($dateClass) || !is_string($dateValue)) {
        throw new QueueException('Malformed date-time value in queue job envelope.');
      }

      if (!is_a($dateClass, DateTimeInterface::class, true)) {
        throw new QueueException("Encoded date class '{$dateClass}' is not a date-time type.");
      }

      if ($expectedType->isBuiltin()) {
        if (!in_array($expectedType->getName(), ['object', 'mixed'], true)) {
          throw new QueueException("Queue job date is not compatible with '{$expectedType->getName()}'.");
        }
      } else {
        $expectedClass = $this->resolveTypeName($expectedType->getName(), $scope);

        if (!is_a($dateClass, $expectedClass, true)) {
          throw new QueueException("Encoded date '{$dateClass}' is not compatible with '{$expectedClass}'.");
        }

        $dateClass = $expectedClass;
      }

      return $this->hydrateDateTime($dateClass, $dateValue, is_string($timezone) ? $timezone : null);
    }

    throw new QueueException('Unknown value type in queue job envelope.');
  }

  /**
   * @param array<string, mixed> $metadata
   * @param list<class-string> $expectedClasses
   */
  private function hydrateObjectEnvelope(array $metadata, array $expectedClasses): object
  {
    $properties = $metadata['properties'] ?? null;
    $encodedClass = $metadata['class'] ?? null;

    if (!is_array($properties) || !is_string($encodedClass) || !$this->typeExists($encodedClass)) {
      throw new QueueException('Malformed object value in queue job envelope.');
    }

    foreach ($expectedClasses as $expectedClass) {
      if (!is_a($encodedClass, $expectedClass, true)) {
        throw new QueueException(
          "Encoded nested job value '{$encodedClass}' is not compatible with '{$expectedClass}'."
        );
      }
    }

    return $this->hydrateObject($encodedClass, $properties, true);
  }

  private function hydrateUntypedValue(mixed $value, bool $normalized): mixed
  {
    if ($normalized && $this->isValueEnvelope($value)) {
      $metadata = $value[self::VALUE_KEY];
      $kind = $metadata['type'] ?? null;

      if ($kind === 'array') {
        $items = $metadata['items'] ?? [];
        $result = [];

        if (!is_array($items)) {
          throw new QueueException('Malformed array value in queue job envelope.');
        }

        foreach ($items as $key => $item) {
          $result[$key] = $this->hydrateUntypedValue($item, true);
        }

        return $result;
      }

      if ($kind === 'object') {
        $properties = $metadata['properties'] ?? null;

        if (!is_array($properties)) {
          throw new QueueException('Malformed object value in queue job envelope.');
        }

        return $this->toUntypedObject($properties, true);
      }

      if ($kind === 'enum') {
        $enumClass = $metadata['class'] ?? null;
        $enumValue = $metadata['value'] ?? null;

        if (!is_string($enumClass) || !enum_exists($enumClass)) {
          throw new QueueException('Malformed enum value in queue job envelope.');
        }

        if (is_a($enumClass, BackedEnum::class, true)) {
          /** @var class-string<BackedEnum> $enumClass */
          return $enumClass::from($enumValue);
        }

        return constant($enumClass . '::' . $enumValue);
      }

      if ($kind === 'datetime') {
        $dateClass = is_string($metadata['class'] ?? null) ? $metadata['class'] : DateTimeImmutable::class;

        if (!is_a($dateClass, DateTimeInterface::class, true)) {
          throw new QueueException("Encoded date class '{$dateClass}' is not a date-time type.");
        }

        return $this->hydrateDateTime(
          $dateClass,
          (string) ($metadata['value'] ?? ''),
          is_string($metadata['timezone'] ?? null) ? $metadata['timezone'] : null,
        );
      }

      throw new QueueException('Unknown value type in queue job envelope.');
    }

    if (!is_array($value)) {
      return $value;
    }

    if (array_is_list($value)) {
      return array_map(fn(mixed $item): mixed => $this->hydrateUntypedValue($item, $normalized), $value);
    }

    return $this->toUntypedObject($value, $normalized);
  }

  private function toUntypedObject(mixed $payload, bool $normalized): object
  {
    if (!is_array($payload)) {
      throw new QueueException('Untyped queue job payload must be a JSON object.');
    }

    $object = new stdClass();

    foreach ($payload as $name => $value) {
      $object->{$name} = $this->hydrateUntypedValue($value, $normalized);
    }

    return $object;
  }

  /**
   * @param class-string $class
   */
  private function hydrateDateTime(string $class, mixed $value, ?string $timezone): DateTimeInterface
  {
    if (!is_string($value)) {
      throw new QueueException('Queue job date-time value must be a string.');
    }

    $target = is_a($class, DateTime::class, true) ? DateTime::class : DateTimeImmutable::class;
    $date = new $target($value);

    if ($timezone !== null) {
      $zone = new \DateTimeZone($timezone);
      $date = $date->setTimezone($zone);
    }

    return $date;
  }

  private function isValueEnvelope(mixed $value): bool
  {
    return is_array($value)
      && count($value) === 1
      && isset($value[self::VALUE_KEY])
      && is_array($value[self::VALUE_KEY]);
  }

  /**
   * @return class-string|null
   */
  private function normalizeExpectedClass(?string $class): ?string
  {
    if ($class === null || trim($class, " \\t\n\r\0\x0B\\") === '') {
      return null;
    }

    /** @var class-string $normalized */
    $normalized = ltrim(trim($class), '\\');

    return $normalized;
  }

  /**
   * @return class-string
   */
  private function resolveTypeName(string $name, ReflectionClass $scope): string
  {
    if ($name === 'self' || $name === 'static') {
      return $scope->getName();
    }

    if ($name === 'parent') {
      $parent = $scope->getParentClass();

      if ($parent === false) {
        throw new QueueException("Queue job class '{$scope->getName()}' has no parent type.");
      }

      return $parent->getName();
    }

    /** @var class-string $name */
    return $name;
  }

  private function typeExists(string $class): bool
  {
    return class_exists($class) || interface_exists($class) || enum_exists($class);
  }

  private function wrap(string $message, Throwable $throwable): QueueException
  {
    if ($throwable instanceof QueueException) {
      return $throwable;
    }

    return new QueueException($message . ' ' . $throwable->getMessage(), (int) $throwable->getCode(), $throwable);
  }
}
