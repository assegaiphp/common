<?php

use Assegai\Common\Http\HttpClient;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

describe('make a valid get requests', function() {
  it ('should return a 200 status code', function() {
    $capturedRequest = null;

    $client = new class ($capturedRequest) implements ClientInterface {
      public function __construct(private mixed &$capturedRequest)
      {
      }

      /**
       * @throws ClientExceptionInterface
       */
      public function sendRequest(RequestInterface $request): ResponseInterface
      {
        $this->capturedRequest = $request;

        return new Response(200);
      }
    };

    $httpClient = new HttpClient($client);
    $response = $httpClient->get('https://httpbin.org/get');

    expect($response->getStatusCode())->toBe(200);
    expect($capturedRequest)->toBeInstanceOf(RequestInterface::class);
    expect($capturedRequest->getMethod())->toBe('GET');
    expect((string) $capturedRequest->getUri())->toBe('https://httpbin.org/get');
  });
});
