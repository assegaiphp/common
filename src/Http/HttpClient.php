<?php

namespace Assegai\Common\Http;

use Assegai\Core\Attributes\Injectable;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

#[Injectable]
readonly class HttpClient implements ClientInterface
{
  public function __construct(protected ClientInterface $client)
  {}

  /**
   * @param RequestInterface $request
   * @return ResponseInterface
   * @throws ClientExceptionInterface
   */
  public function sendRequest(RequestInterface $request): ResponseInterface
  {
    return $this->client->sendRequest($request);
  }

  public function get(string $url, ): ResponseInterface
  {

  }
}