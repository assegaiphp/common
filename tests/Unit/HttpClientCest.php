<?php


namespace Tests\Unit;

use Assegai\Common\Http\HttpClient;
use GuzzleHttp\Client;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Tests\Support\UnitTester;

/**
 *
 */
class HttpClientCest
{
  /**
   * @var ClientInterface|null
   */
  protected ?ClientInterface $client = null;
  /**
   * @var HttpClient|null
   */
  protected ?HttpClient $httpClient = null;
  /**
   * @var string 
   */
  protected string $baseURL = 'https://httpbin.org';

  /**
   * @param int $code
   * @return bool
   */
  protected function isSuccessful(int $code): bool
  {
    return (200 <= $code) && (300 > $code);
  }

  /**
   * @param int $code
   * @return bool
   */
  protected function isClientError(int $code): bool
  {
    return (400 <= $code) && (500 > $code);
  }

  /**
   * @param int $code
   * @return bool
   */
  protected function isServerError(int $code): bool
  {
    return (500 <= $code) && (600 > $code);
  }

  /**
   * @param int $code
   * @return bool
   */
  protected function isError(int $code): bool
  {
    return $this->isClientError($code) || $this->isServerError($code);
  }

  /**
   * @param UnitTester $I
   * @return void
   */
  public function _before(UnitTester $I)
  {
    $this->client = new Client();
    $this->httpClient = new HttpClient($this->client);
  }

  // tests
  /**
   * @param UnitTester $I
   * @return void
   * @throws ClientExceptionInterface
   */
  public function checkValidGetRequestWithBody(UnitTester $I)
  {
    $url = "$this->baseURL/get";
    $response = $this->httpClient->get($url, ['limit' => 10, 'skip' => 0]);
    $responseCode = $response->getStatusCode();
    $responseBodyContents = $response->getBody()->getContents();

    expect($response)->toBeInstanceOf(ResponseInterface::class);
    expect($this->isSuccessful($responseCode))
      ->toBeTrue();

    $responseBodyContents = json_decode($responseBodyContents);

    expect(json_last_error() === JSON_ERROR_NONE)
      ->toBeTrue();

    $I->assertObjectHasAttribute('args', $responseBodyContents);
    $I->assertObjectHasAttribute('headers', $responseBodyContents);
    $I->assertObjectHasAttribute('origin', $responseBodyContents);
    $I->assertObjectHasAttribute('url', $responseBodyContents);
  }

  /**
   * @param UnitTester $I
   * @return void
   * @throws ClientExceptionInterface
   */
  public function checkValidGetRequestWithoutBody(UnitTester $I)
  {
    $url = "$this->baseURL/get";
    $response = $this->httpClient->get($url);
    $responseCode = $response->getStatusCode();
    $responseBodyContents = $response->getBody()->getContents();

    expect($response)->toBeInstanceOf(ResponseInterface::class);
    expect($this->isSuccessful($responseCode))
      ->toBeTrue();

    $responseBodyContents = json_decode($responseBodyContents);

    expect(json_last_error() === JSON_ERROR_NONE)
      ->toBeTrue();

    $I->assertObjectHasAttribute('args', $responseBodyContents);
    $I->assertObjectHasAttribute('headers', $responseBodyContents);
    $I->assertObjectHasAttribute('origin', $responseBodyContents);
    $I->assertObjectHasAttribute('url', $responseBodyContents);
  }

  /**
   * @param UnitTester $I
   * @return void
   * @throws ClientExceptionInterface
   */
  public function checkInvalidGetRequestWithBody(UnitTester $I)
  {
    $url = "$this->baseURL/get/invalid";
    $response = $this->httpClient->get($url, ['limit' => 10, 'skip' => 0]);
    $responseCode = $response->getStatusCode();

    expect($response)->toBeInstanceOf(ResponseInterface::class);
    expect($this->isError($responseCode))
      ->toBeTrue();
  }

  /**
   * @param UnitTester $I
   * @return void
   * @throws ClientExceptionInterface
   */
  public function checkInvalidGetRequestWithoutBody(UnitTester $I)
  {
    $url = "$this->baseURL/get/invalid";
    $response = $this->httpClient->get($url);
    $responseCode = $response->getStatusCode();

    expect($response)->toBeInstanceOf(ResponseInterface::class);
    expect($this->isError($responseCode))
      ->toBeTrue();
  }

  /**
   * @param UnitTester $I
   * @return void
   * @throws ClientExceptionInterface
   */
  public function checkValidPostRequestWithBody(UnitTester $I)
  {
    $url = "$this->baseURL/post";
    $requestBody = (object)[
      'name' => 'morpheus',
      'job' => 'leader',
    ];

    $response = $this->httpClient->post($url, $requestBody);

    $responseCode = $response->getStatusCode();
    $responseBodyContents = $response->getBody()->getContents();

    expect($response)->toBeInstanceOf(ResponseInterface::class);
    expect($this->isSuccessful($responseCode))
      ->toBeTrue();

    expect($responseBodyContents)
      ->notToBeEmpty();

    $responseBodyContents = json_decode($responseBodyContents);

    expect(json_last_error() === JSON_ERROR_NONE)
      ->toBeTrue();

    $I->assertObjectHasAttribute('args', $responseBodyContents);
    $I->assertObjectHasAttribute('data', $responseBodyContents);
    $I->assertObjectHasAttribute('headers', $responseBodyContents);
    $I->assertObjectHasAttribute('origin', $responseBodyContents);
    $I->assertObjectHasAttribute('url', $responseBodyContents);
  }

  /**
   * @param UnitTester $I
   * @return void
   * @throws ClientExceptionInterface
   */
  public function checkValidPostRequestWithoutBody(UnitTester $I)
  {
    $url = "$this->baseURL/post";
    $response = $this->httpClient->post($url);

    $responseCode = $response->getStatusCode();
    $responseBodyContents = $response->getBody()->getContents();

    expect($response)->toBeInstanceOf(ResponseInterface::class);
    expect($this->isSuccessful($responseCode))
      ->toBeTrue();

    expect($responseBodyContents)
      ->notToBeEmpty();

    $responseBodyContents = json_decode($responseBodyContents);

    expect(json_last_error() === JSON_ERROR_NONE)
      ->toBeTrue();

    $I->assertObjectHasAttribute('args', $responseBodyContents);
    $I->assertObjectHasAttribute('data', $responseBodyContents);
    $I->assertObjectHasAttribute('headers', $responseBodyContents);
    $I->assertObjectHasAttribute('origin', $responseBodyContents);
    $I->assertObjectHasAttribute('url', $responseBodyContents);
  }

  /**
   * @param UnitTester $I
   * @return void
   * @throws ClientExceptionInterface
   */
  public function checkInvalidPostRequestWithBody(UnitTester $I)
  {
    $url = "$this->baseURL/post/invalid";
    $requestBody = (object)[
      'name' => 'morpheus',
      'job' => 'leader',
    ];

    $response = $this->httpClient->post($url, $requestBody);

    $responseCode = $response->getStatusCode();

    expect($response)->toBeInstanceOf(ResponseInterface::class);
    expect($this->isError($responseCode))
      ->toBeTrue();
  }

  /**
   * @param UnitTester $I
   * @return void
   * @throws ClientExceptionInterface
   */
  public function checkInvalidPostRequestWithoutBody(UnitTester $I)
  {
    $url = "$this->baseURL/post/invalid";
    $requestBody = [];

    $response = $this->httpClient->post($url, $requestBody);

    $responseCode = $response->getStatusCode();

    expect($response)->toBeInstanceOf(ResponseInterface::class);
    expect($this->isError($responseCode))
      ->toBeTrue();
  }

  /**
   * @param UnitTester $I
   * @return void
   * @throws ClientExceptionInterface
   */
  public function checkValidPutRequestWithBody(UnitTester $I)
  {
    $url = "$this->baseURL/put";
    $requestBody = [
      'name' => 'morpheus',
      'job' => 'leader',
    ];

    $response = $this->httpClient->put($url, $requestBody);

    $responseCode = $response->getStatusCode();
    $responseBodyContents = $response->getBody()->getContents();

    expect($response)->toBeInstanceOf(ResponseInterface::class);
    expect($this->isSuccessful($responseCode))
      ->toBeTrue();

    expect($responseBodyContents)
      ->notToBeEmpty();

    $responseBodyContents = json_decode($responseBodyContents);

    expect(json_last_error() === JSON_ERROR_NONE)
      ->toBeTrue();

    $I->assertObjectHasAttribute('args', $responseBodyContents);
    $I->assertObjectHasAttribute('data', $responseBodyContents);
    $I->assertObjectHasAttribute('headers', $responseBodyContents);
    $I->assertObjectHasAttribute('origin', $responseBodyContents);
    $I->assertObjectHasAttribute('url', $responseBodyContents);
  }

  /**
   * @param UnitTester $I
   * @return void
   * @throws ClientExceptionInterface
   */
  public function checkValidPutRequestWithoutBody(UnitTester $I)
  {
    $url = "$this->baseURL/put";
    $requestBody = [];

    $response = $this->httpClient->put($url, $requestBody);

    $responseCode = $response->getStatusCode();
    $responseBodyContents = $response->getBody()->getContents();

    expect($response)->toBeInstanceOf(ResponseInterface::class);
    expect($this->isSuccessful($responseCode))
      ->toBeTrue();

    expect($responseBodyContents)
      ->notToBeEmpty();

    $responseBodyContents = json_decode($responseBodyContents);

    expect(json_last_error() === JSON_ERROR_NONE)
      ->toBeTrue();

    $I->assertObjectHasAttribute('args', $responseBodyContents);
    $I->assertObjectHasAttribute('data', $responseBodyContents);
    $I->assertObjectHasAttribute('headers', $responseBodyContents);
    $I->assertObjectHasAttribute('origin', $responseBodyContents);
    $I->assertObjectHasAttribute('url', $responseBodyContents);
  }

  /**
   * @param UnitTester $I
   * @return void
   * @throws ClientExceptionInterface
   */
  public function checkInvalidPutRequestWithBody(UnitTester $I)
  {
    $url = "$this->baseURL/put/invalid";
    $requestBody = [
      'name' => 'morpheus',
      'job' => 'leader',
    ];

    $response = $this->httpClient->put($url, $requestBody);
    $responseCode = $response->getStatusCode();

    expect($response)->toBeInstanceOf(ResponseInterface::class);
    expect($this->isError($responseCode))
      ->toBeTrue();
  }

  /**
   * @param UnitTester $I
   * @return void
   * @throws ClientExceptionInterface
   */
  public function checkInvalidPutRequestWithoutBody(UnitTester $I)
  {
    $url = "$this->baseURL/put/invalid";
    $requestBody = [];

    $response = $this->httpClient->put($url, $requestBody);
    $responseCode = $response->getStatusCode();

    expect($response)->toBeInstanceOf(ResponseInterface::class);
    expect($this->isError($responseCode))
      ->toBeTrue();
  }

  /**
   * @param UnitTester $I
   * @return void
   * @throws ClientExceptionInterface
   */
  public function checkValidPatchRequestWithBody(UnitTester $I)
  {
    $url = "$this->baseURL/patch";
    $requestBody = [
      'name' => 'morpheus',
      'job' => 'leader',
    ];

    $response = $this->httpClient->patch($url, $requestBody);

    $responseCode = $response->getStatusCode();
    $responseBodyContents = $response->getBody()->getContents();

    expect($response)->toBeInstanceOf(ResponseInterface::class);
    expect($this->isSuccessful($responseCode))
      ->toBeTrue();

    expect($responseBodyContents)
      ->notToBeEmpty();

    $responseBodyContents = json_decode($responseBodyContents);

    expect(json_last_error() === JSON_ERROR_NONE)
      ->toBeTrue();

    $I->assertObjectHasAttribute('args', $responseBodyContents);
    $I->assertObjectHasAttribute('data', $responseBodyContents);
    $I->assertObjectHasAttribute('headers', $responseBodyContents);
    $I->assertObjectHasAttribute('origin', $responseBodyContents);
    $I->assertObjectHasAttribute('url', $responseBodyContents);
  }

  /**
   * @param UnitTester $I
   * @return void
   * @throws ClientExceptionInterface
   */
  public function checkValidPatchRequestWithoutBody(UnitTester $I)
  {
    $url = "$this->baseURL/patch";
    $requestBody = [];

    $response = $this->httpClient->patch($url, $requestBody);

    $responseCode = $response->getStatusCode();
    $responseBodyContents = $response->getBody()->getContents();

    expect($response)->toBeInstanceOf(ResponseInterface::class);
    expect($this->isSuccessful($responseCode))
      ->toBeTrue();

    expect($responseBodyContents)
      ->notToBeEmpty();

    $responseBodyContents = json_decode($responseBodyContents);

    expect(json_last_error() === JSON_ERROR_NONE)
      ->toBeTrue();

    $I->assertObjectHasAttribute('args', $responseBodyContents);
    $I->assertObjectHasAttribute('data', $responseBodyContents);
    $I->assertObjectHasAttribute('headers', $responseBodyContents);
    $I->assertObjectHasAttribute('origin', $responseBodyContents);
    $I->assertObjectHasAttribute('url', $responseBodyContents);
  }

  /**
   * @param UnitTester $I
   * @return void
   * @throws ClientExceptionInterface
   */
  public function checkInvalidPatchRequestWithBody(UnitTester $I)
  {
    $url = "$this->baseURL/patch/invalid";
    $requestBody = [];

    $response = $this->httpClient->patch($url, $requestBody);
    $responseCode = $response->getStatusCode();

    expect($response)->toBeInstanceOf(ResponseInterface::class);
    expect($this->isError($responseCode))
      ->toBeTrue();
  }

  /**
   * @param UnitTester $I
   * @return void
   * @throws ClientExceptionInterface
   */
  public function checkInvalidPatchRequestWithoutBody(UnitTester $I)
  {
    $url = "$this->baseURL/patch/invalid";
    $requestBody = [
      'name' => 'morpheus',
      'job' => 'leader',
    ];

    $response = $this->httpClient->patch($url, $requestBody);
    $responseCode = $response->getStatusCode();

    expect($response)->toBeInstanceOf(ResponseInterface::class);
    expect($this->isError($responseCode))
      ->toBeTrue();
  }

  /**
   * @param UnitTester $I
   * @return void
   * @throws ClientExceptionInterface
   */
  public function checkValidDeleteRequestWithBody(UnitTester $I)
  {
    $url = "$this->baseURL/delete";
    $requestBody = [
      'name' => 'morpheus',
      'job' => 'leader',
    ];

    $response = $this->httpClient->delete($url, $requestBody);

    $responseCode = $response->getStatusCode();
    $responseBodyContents = $response->getBody()->getContents();

    expect($response)->toBeInstanceOf(ResponseInterface::class);
    expect($this->isSuccessful($responseCode))
      ->toBeTrue();

    expect($responseBodyContents)
      ->notToBeEmpty();

    $responseBodyContents = json_decode($responseBodyContents);

    expect(json_last_error() === JSON_ERROR_NONE)
      ->toBeTrue();

    $I->assertObjectHasAttribute('args', $responseBodyContents);
    $I->assertObjectHasAttribute('headers', $responseBodyContents);
    $I->assertObjectHasAttribute('origin', $responseBodyContents);
    $I->assertObjectHasAttribute('url', $responseBodyContents);
  }

  /**
   * @param UnitTester $I
   * @return void
   * @throws ClientExceptionInterface
   */
  public function checkValidDeleteRequestWithoutBody(UnitTester $I)
  {
    $url = "$this->baseURL/delete";

    $response = $this->httpClient->delete($url);

    $responseCode = $response->getStatusCode();
    $responseBodyContents = $response->getBody()->getContents();

    expect($response)->toBeInstanceOf(ResponseInterface::class);
    expect($this->isSuccessful($responseCode))
      ->toBeTrue();

    expect($responseBodyContents)
      ->notToBeEmpty();

    $responseBodyContents = json_decode($responseBodyContents);

    expect(json_last_error() === JSON_ERROR_NONE)
      ->toBeTrue();

    $I->assertObjectHasAttribute('args', $responseBodyContents);
    $I->assertObjectHasAttribute('headers', $responseBodyContents);
    $I->assertObjectHasAttribute('origin', $responseBodyContents);
    $I->assertObjectHasAttribute('url', $responseBodyContents);
  }

  /**
   * @param UnitTester $I
   * @return void
   * @throws ClientExceptionInterface
   */
  public function checkInvalidDeleteRequestWithBody(UnitTester $I)
  {
    $url = 'https://httpbin.org/users/invalid';
    $requestBody = [
      'name' => 'morpheus',
      'job' => 'leader',
    ];

    $response = $this->httpClient->delete($url,$requestBody);
    $responseCode = $response->getStatusCode();

    expect($response)->toBeInstanceOf(ResponseInterface::class);
    expect($this->isError($responseCode))
      ->toBeTrue();
  }

  /**
   * @param UnitTester $I
   * @return void
   * @throws ClientExceptionInterface
   */
  public function checkInvalidDeleteRequestWithoutBody(UnitTester $I)
  {
    $url = 'https://httpbin.org/users/invalid';

    $response = $this->httpClient->delete($url);
    $responseCode = $response->getStatusCode();

    expect($response)->toBeInstanceOf(ResponseInterface::class);
    expect($this->isError($responseCode))
      ->toBeTrue();
  }
}
