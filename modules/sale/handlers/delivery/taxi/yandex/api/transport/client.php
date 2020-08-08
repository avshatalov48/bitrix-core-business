<?php

namespace Sale\Handlers\Delivery\Taxi\Yandex\Api\Transport;

use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;

/**
 * Class Client
 * @package Sale\Handlers\Delivery\Taxi\Yandex\Api\Transport
 */
class Client
{
	/** @var OauthTokenProvider */
	private $oauthTokenProvider;

	/** @var int */
	private $socketTimeOut = 30;

	/** @var int */
	private $streamTimeOut = 30;

	/** @var bool */
	private $isTestEnvironment = false;

	/** @var int */
	private $apiVersion = 1;

	/**
	 * Client constructor.
	 * @param OauthTokenProvider $oauthTokenProvider
	 */
	public function __construct(OauthTokenProvider $oauthTokenProvider)
	{
		$this->oauthTokenProvider = $oauthTokenProvider;
	}

	/**
	 * @param string $uri
	 * @param array $body
	 * @param array $options
	 * @return Response
	 * @throws Exception
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public function post(string $uri, $body = [], $options = [])
	{
		return $this->request('post', $uri, $body, $options);
	}

	/**
	 * @param string $uri
	 * @param array $options
	 * @return Response
	 * @throws Exception
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public function get(string $uri, $options = [])
	{
		return $this->request('get', $uri, null, $options = []);
	}

	/**
	 * @param string $method
	 * @param string $uri
	 * @param null $body
	 * @param array $options
	 * @return Response
	 * @throws Exception
	 * @throws \Bitrix\Main\ArgumentException
	 */
	private function request(string $method, string $uri, $body = null, $options = [])
	{
		$httpClient = $this->makeHttpClient();

		if ($method == 'post')
		{
			$result = $httpClient->post(
				$this->getBase($options) . $uri,
				json_encode($body, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT)
			);
		}
		else
		{
			$result = $httpClient->get($this->getBase($options) . $uri);
		}

		$status = $httpClient->getStatus();

		if ($result === false || $status == 0 || $status >= 500)
		{
			$errors = implode(';', $httpClient->getError());

			throw new Exception(sprintf('transport_exception: %s', $errors));
		}

		return new Response((int)$httpClient->getStatus(), Json::decode($result));
	}

	/**
	 * @return HttpClient
	 */
	private function makeHttpClient(): HttpClient
	{
		$result = (new HttpClient(
			[
				'version' => HttpClient::HTTP_1_1,
				'socketTimeout' => $this->socketTimeOut,
				'streamTimeout' => $this->streamTimeOut,
			]
		));

		$result
			->setHeader('Authorization', sprintf('Bearer %s', (string)$this->oauthTokenProvider->getToken()))
			->setHeader('Accept-Language', 'ru')
			->setHeader('Content-Type', 'application/json');

		return $result;
	}

	/**
	 * @param array $options
	 * @return string
	 */
	private function getBase($options = []): string
	{
		$baseUriTemplate = isset($options['base_uri_template'])
			? $options['base_uri_template']
			: 'https://b2b.taxi%s.yandex.net/b2b/cargo/integration/v%s/';

		return sprintf(
			$baseUriTemplate,
			($this->isTestEnvironment ? '.tst' : ''),
			$this->apiVersion
		);
	}

	/**
	 * @param bool $isTestEnvironment
	 * @return Client
	 */
	public function setIsTestEnvironment(bool $isTestEnvironment): Client
	{
		$this->isTestEnvironment = $isTestEnvironment;

		return $this;
	}

	/**
	 * @param int $apiVersion
	 * @return Client
	 */
	public function setApiVersion(int $apiVersion): Client
	{
		$this->apiVersion = $apiVersion;

		return $this;
	}

	/**
	 * @param int $socketTimeOut
	 * @return Client
	 */
	public function setSocketTimeOut(int $socketTimeOut): Client
	{
		$this->socketTimeOut = $socketTimeOut;

		return $this;
	}

	/**
	 * @param int $streamTimeOut
	 * @return Client
	 */
	public function setStreamTimeOut(int $streamTimeOut): Client
	{
		$this->streamTimeOut = $streamTimeOut;

		return $this;
	}

	/**
	 * @return OauthTokenProvider
	 */
	public function getOauthTokenProvider(): OauthTokenProvider
	{
		return $this->oauthTokenProvider;
	}
}
