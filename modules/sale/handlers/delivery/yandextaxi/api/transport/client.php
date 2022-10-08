<?php

namespace Sale\Handlers\Delivery\YandexTaxi\Api\Transport;

use Bitrix\Main\ArgumentException;
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
	 * @param int $version
	 * @param string $method
	 * @param string $endpoint
	 * @param array|null $queryParams
	 * @param mixed|null $body
	 * @return Response
	 * @throws Exception
	 */
	public function request(
		int $version,
		string $method,
		string $endpoint,
		?array $queryParams = null,
		$body = null
	)
	{
		$httpClient = $this->makeHttpClient();

		if ($method === HttpClient::HTTP_POST)
		{
			$result = $httpClient->post(
				$this->getUrl($version, $endpoint, $queryParams),
				json_encode($body, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT)
			);
		}
		else
		{
			$result = $httpClient->get(
				$this->getUrl($version, $endpoint, $queryParams)
			);
		}

		$status = $httpClient->getStatus();

		if (
			$result === false
			|| $status === 0
			|| $status >= 500
		)
		{
			$errors = implode(';', $httpClient->getError());

			$this->logger->log(
				static::LOG_SOURCE,
				(string)$status,
				$errors
			);

			throw new Exception(sprintf('transport_exception: %s', $errors));
		}

		try
		{
			$response = Json::decode($result);
		}
		catch (ArgumentException $e)
		{
			throw new Exception(sprintf('transport_exception: unexpected JSON format: %s', $result));
		}

		return new Response((int)$httpClient->getStatus(), $response);
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
	 * @param int $version
	 * @param string $endpoint
	 * @param array|null $queryParams
	 * @return string
	 */
	private function getUrl(int $version, string $endpoint, ?array $queryParams): string
	{
		return sprintf(
			'https://b2b.taxi%s.yandex.net/b2b/cargo/integration/v%s/%s?%s',
			($this->isTestEnvironment ? '.tst' : ''),
			$version,
			$endpoint,
			$queryParams ? http_build_query($queryParams) : ''
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
