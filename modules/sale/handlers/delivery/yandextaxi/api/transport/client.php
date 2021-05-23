<?php

namespace Sale\Handlers\Delivery\YandexTaxi\Api\Transport;

use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;
use Sale\Handlers\Delivery\YandexTaxi\Common\Logger;
use Sale\Handlers\Delivery\YandexTaxi\Common\ReferralSourceBuilder;

/**
 * Class Client
 * @package Sale\Handlers\Delivery\YandexTaxi\Api\Transport
 * @internal
 */
final class Client
{
	private const LOG_SOURCE = 'transport';

	/** @var OauthTokenProvider */
	private $oauthTokenProvider;

	/** @var Logger */
	private $logger;

	/** @var ReferralSourceBuilder */
	private $referralSourceBuilder;

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
	 * @param Logger $logger
	 * @param ReferralSourceBuilder $referralSourceBuilder
	 */
	public function __construct(OauthTokenProvider $oauthTokenProvider, Logger $logger, ReferralSourceBuilder $referralSourceBuilder)
	{
		$this->oauthTokenProvider = $oauthTokenProvider;
		$this->logger = $logger;
		$this->referralSourceBuilder = $referralSourceBuilder;
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

			$this->logger->log(
				static::LOG_SOURCE,
				(string)$status,
				$errors
			);

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
			->setHeader('Content-Type', 'application/json')
			->setHeader(
				'User-Agent',
				$this->referralSourceBuilder->getReferralSourceValue()
			);

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
	 * @return bool
	 */
	public function isTestEnvironment(): bool
	{
		return $this->isTestEnvironment;
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
