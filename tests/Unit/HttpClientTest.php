<?php

beforeAll(function() {
  $this->baseURL = 'https://httpbin.org';
});

describe('make a valid get requests', function() {
  it ('should return a 200 status code', function() {
    $response = $this->httpClient->get($this->baseURL . '/get');
    expect($response->getStatusCode())->toBe(200);
  });
});