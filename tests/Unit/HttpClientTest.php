<?php

use Assegai\Common\Http\HttpClient;
use GuzzleHttp\Client;

beforeAll(function() {
  $this->baseURL = 'https://httpbin.org';
});

describe('make a valid get requests', function() {
  it ('should return a 200 status code', function() {
    $httpClient = new HttpClient(new Client());
    $response = $httpClient->get($this->baseURL . '/get');
    expect($response->getStatusCode())->toBe(200);
  });
});