<?php

use Assegai\Common\Exceptions\QueueException;
use Assegai\Common\Queues\JsonQueueJobCodec;
use Assegai\Common\Queues\QueueJobTypeResolver;
use Assegai\Common\Queues\QueueProcessResult;

enum QueueCodecStatus: string
{
  case Pending = 'pending';
}

final readonly class QueueCodecRecipient
{
  public function __construct(public string $email)
  {
  }
}

final readonly class QueueCodecJob
{
  /**
   * @param array<string, int> $counts
   */
  public function __construct(
    public string $streamId,
    public QueueCodecStatus $status,
    public DateTimeImmutable $scheduledAt,
    public QueueCodecRecipient $recipient,
    public array $counts,
  ) {
  }
}

final class QueueCodecProcessor
{
  public function process(QueueCodecJob $job): void
  {
  }

  public function processUntyped(object $job): void
  {
  }
}

describe('JSON queue job codec', function (): void {
  it('round trips typed jobs through a versioned envelope', function (): void {
    $codec = new JsonQueueJobCodec();
    $original = new QueueCodecJob(
      streamId: 'stream-42',
      status: QueueCodecStatus::Pending,
      scheduledAt: new DateTimeImmutable('2026-08-26T12:30:00+02:00'),
      recipient: new QueueCodecRecipient('queue@example.com'),
      counts: ['attempts' => 2],
    );

    $encoded = $codec->encode($original);
    $decoded = $codec->decode($encoded, QueueCodecJob::class);

    expect(json_decode($encoded, true, flags: JSON_THROW_ON_ERROR))
      ->toHaveKey('_assegai_queue.version', JsonQueueJobCodec::VERSION)
      ->toHaveKey('_assegai_queue.job', QueueCodecJob::class);
    expect($decoded)->toBeInstanceOf(QueueCodecJob::class);
    expect($decoded->streamId)->toBe('stream-42');
    expect($decoded->status)->toBe(QueueCodecStatus::Pending);
    expect($decoded->scheduledAt->format(DateTimeInterface::ATOM))->toBe('2026-08-26T12:30:00+02:00');
    expect($decoded->recipient)->toBeInstanceOf(QueueCodecRecipient::class);
    expect($decoded->recipient->email)->toBe('queue@example.com');
    expect($decoded->counts)->toBe(['attempts' => 2]);
  });

  it('hydrates legacy JSON into the processor job type', function (): void {
    $codec = new JsonQueueJobCodec();
    $decoded = $codec->decode(json_encode([
      'streamId' => 'legacy-stream',
      'status' => 'pending',
      'scheduledAt' => '2026-08-26T10:30:00+00:00',
      'recipient' => ['email' => 'legacy@example.com'],
      'counts' => ['attempts' => 1],
    ], JSON_THROW_ON_ERROR), QueueCodecJob::class);

    expect($decoded)->toBeInstanceOf(QueueCodecJob::class);
    expect($decoded->recipient)->toBeInstanceOf(QueueCodecRecipient::class);
    expect($decoded->status)->toBe(QueueCodecStatus::Pending);
  });

  it('returns stdClass for untyped processors without instantiating envelope classes', function (): void {
    $codec = new JsonQueueJobCodec();
    $encoded = $codec->encode(new QueueCodecRecipient('untyped@example.com'));
    $decoded = $codec->decode($encoded);

    expect($decoded)->toBeInstanceOf(stdClass::class);
    expect($decoded->email)->toBe('untyped@example.com');
  });

  it('rejects an envelope that does not match the processor job type', function (): void {
    $codec = new JsonQueueJobCodec();
    $encoded = $codec->encode(new QueueCodecRecipient('wrong@example.com'));

    expect(fn(): object => $codec->decode($encoded, QueueCodecJob::class))
      ->toThrow(QueueException::class, 'is not compatible with processor type');
  });

  it('rejects malformed JSON and circular job graphs', function (): void {
    $codec = new JsonQueueJobCodec();
    $circular = new stdClass();
    $circular->self = $circular;

    expect(fn(): object => $codec->decode('{invalid', QueueCodecJob::class))
      ->toThrow(QueueException::class, 'Failed to decode queue job');
    expect(fn(): string => $codec->encode($circular))
      ->toThrow(QueueException::class, 'circular object references');
  });
});

describe('queue callback and process result contracts', function (): void {
  it('resolves concrete processor job types without treating object as a class', function (): void {
    $processor = new QueueCodecProcessor();

    expect(QueueJobTypeResolver::fromCallback([$processor, 'process']))->toBe(QueueCodecJob::class);
    expect(QueueJobTypeResolver::fromCallback([$processor, 'processUntyped']))->toBeNull();
  });

  it('keeps the hydrated job available on failed results', function (): void {
    $job = new QueueCodecRecipient('failed@example.com');
    $error = new TypeError('processor type mismatch');
    $result = new QueueProcessResult(errors: [$error], job: $job);

    expect($result->isError())->toBeTrue();
    expect($result->getNextError())->toBe($error);
    expect($result->getJob())->toBe($job);
  });
});
