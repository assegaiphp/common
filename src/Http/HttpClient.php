<?php

namespace Assegai\Common\Http;

use Assegai\Attributes\Injectable;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

#[Injectable]
readonly class HttpClient implements ClientInterface
{
  /**
   * Constructs an HttpClient object.
   *
   * @param ClientInterface $client
   */
  public function __construct(protected ClientInterface $client)
  {
  }

  /**
   * @param RequestInterface $request
   * @return ResponseInterface
   * @throws ClientExceptionInterface
   */
  public function sendRequest(RequestInterface $request): ResponseInterface
  {
    return $this->client->sendRequest($request);
  }

  /**
   * @param string $url
   * @param array|object $data
   * @param array|object $options
   * @return ResponseInterface
   * @throws ClientExceptionInterface
   */
  public function get(string $url, array|object $data = [], array|object $options = []): ResponseInterface
  {
    $headers = match (true) {
      is_array($options) && isset($options['headers']) => $options['headers'],
      is_object($options) && isset($options->headers) => $options->headers,
      default => []
    };

    $body = http_build_query($data);
    if ($body) {
      $url .= "?$body";
    }
    $request = new Request('GET', $url, $headers, $body);
    return $this->sendRequest($request);
  }

  /**
   * @param string $url
   * @param array|object $data
   * @param array|object $options
   * @return ResponseInterface
   * @throws ClientExceptionInterface
   */
  public function post(string $url, array|object $data = [], array|object $options = []): ResponseInterface
  {
    $headers = match (true) {
      is_array($options) && isset($options['headers']) => $options['headers'],
      is_object($options) && isset($options->headers) => $options->headers,
      default => []
    };

    if ($data)
    {
      $headers['body'] = (array)$data;
    }

    $request = new Request('POST', $url, $headers);
    return $this->sendRequest($request);
  }

  /**
   * @param string $url
   * @param array|object $data
   * @param array|object $options
   * @return ResponseInterface
   * @throws ClientExceptionInterface
   */
  public function put(string $url, array|object $data = [], array|object $options = []): ResponseInterface
  {
    $headers = match (true) {
      is_array($options) && isset($options['headers']) => $options['headers'],
      is_object($options) && isset($options->headers) => $options->headers,
      default => []
    };

    if ($data)
    {
      $headers['json'] = (array)$data;
    }

    $request = new Request('PUT', $url, $headers);
    return $this->sendRequest($request);
  }

  /**
   * @param string $url
   * @param array|object $data
   * @param array|object $options
   * @return ResponseInterface
   * @throws ClientExceptionInterface
   */
  public function patch(string $url, array|object $data = [], array|object $options = []): ResponseInterface
  {
    $headers = match (true) {
      is_array($options) && isset($options['headers']) => $options['headers'],
      is_object($options) && isset($options->headers) => $options->headers,
      default => []
    };

    if ($data)
    {
      $headers['json'] = (array)$data;
    }

    $request = new Request('PATCH', $url, $headers);
    return $this->sendRequest($request);
  }

  /**
   * @param string $url
   * @param array|object $data
   * @param array|object $options
   * @return ResponseInterface
   * @throws ClientExceptionInterface
   */
  public function delete(string $url, array|object $data = [], array|object $options = []): ResponseInterface
  {
    $headers = match (true) {
      is_array($options) && isset($options['headers']) => $options['headers'],
      is_object($options) && isset($options->headers) => $options->headers,
      default => []
    };

    if ($data)
    {
      $headers['json'] = (array)$data;
    }

    $request = new Request('DELETE', $url, $headers);
    return $this->sendRequest($request);
  }
}