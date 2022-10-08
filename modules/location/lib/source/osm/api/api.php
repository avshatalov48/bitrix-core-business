<?php

namespace Bitrix\Location\Source\Osm\Api;

use Bitrix\Location\Exception\RuntimeException;
use Bitrix\Location\Source\Osm\OsmSource;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Text\Encoding;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;

/**
 * Class Api
 * @package Bitrix\Location\Source\Osm
 * @see https://nominatim.org/release-docs/develop/api/Overview/
 * @internal
 */
final class Api
{
	/**
	 * HTTP client-related constants
	 */
	private const HTTP_VERSION = HttpClient::HTTP_1_1;
	private const HTTP_SOCKET_TIMEOUT = 10;
	private const HTTP_STREAM_TIMEOUT = 10;

	/**
	 * Method related constants
	 */
	private const API_SEARCH_LIMIT = 10;

	private const API_AUTOCOMPLETE_LIMIT = 7;

	/** @var OsmSource */
	private $source;

	/**
	 * Api constructor.
	 * @param OsmSource $source
	 */
	public function __construct(OsmSource $source)
	{
		$this->source = $source;
	}

	/**
	 * @param array $options
	 * @return array
	 * @throws RuntimeException
	 */
	public function search(array $options): array
	{
		$client = $this->makeHttpClient();

		$body = $client->get(
			$this->buildUrl(
				'location',
				'search',
				$this->wrapQueryData(
					[
						'q' =>  Encoding::convertEncoding($options['q'], SITE_CHARSET, 'UTF-8'),
						'addressdetails' => isset($options['addressdetails']) ? (int)$options['addressdetails'] : 0,
						'limit' => isset($options['limit']) ? (int)$options['limit'] : self::API_SEARCH_LIMIT,
						'accept-language' => $options['accept-language'] ?? '',
						'format' => 'json',
					]
				)
			)
		);

		return $this->getResponse($client, $body);
	}

	/**
	 * @param array $options
	 * @return array
	 */
	public function autocomplete(array $options): array
	{
		$client = $this->makeHttpClient();

		$queryData = [
			'q' =>  Encoding::convertEncoding($options['q'], SITE_CHARSET, 'UTF-8'),
			'limit' => isset($options['limit']) ? (int)$options['limit'] : self::API_AUTOCOMPLETE_LIMIT,
			'lang' => $options['lang'] ?? '',
			'version' => 2,
		];
		if (isset($options['lat']) && isset($options['lon']))
		{
			$queryData['lat'] = $options['lat'];
			$queryData['lon'] = $options['lon'];
		}

		$body = $client->get(
			$this->buildUrl(
				'autocomplete',
				'autocomplete',
				$this->wrapQueryData(
					$queryData
				)
			)
		);

		return $this->getResponse($client, $body);
	}

	/**
	 * @param array $options
	 * @return array
	 * @throws RuntimeException
	 */
	public function lookup(array $options): array
	{
		$client = $this->makeHttpClient();

		$body = $client->get(
			$this->buildUrl(
				'location',
				'lookup',
				$this->wrapQueryData(
					[
						'osm_ids' => $options['osm_ids'] ?? '',
						'addressdetails' => isset($options['addressdetails']) ? (int)$options['addressdetails'] : 0,
						'accept-language' => $options['accept-language'] ?? '',
						'format' => 'json',
					]
				)
			)
		);

		return $this->getResponse($client, $body);
	}

	/**
	 * @param array $options
	 * @return array
	 * @throws RuntimeException
	 */
	public function details(array $options): array
	{
		$client = $this->makeHttpClient();

		$body = $client->get(
			$this->buildUrl(
				'location',
				'details',
				$this->wrapQueryData(
					[
						'osmtype' => $options['osm_type'] ?? '',
						'osmid' => $options['osm_id'] ?? '',
						'format' => 'json',
						'addressdetails' => isset($options['addressdetails']) ? (int)$options['addressdetails'] : 0,
						'linkedplaces' => isset($options['linkedplaces']) ? (int)$options['linkedplaces'] : 0,
						'hierarchy' => isset($options['hierarchy']) ? (int)$options['hierarchy'] : 0,
						'accept-language' => $options['accept-language'] ?? '',
					]
				)
			)
		);

		return $this->getResponse($client, $body);
	}

	/**
	 * @param HttpClient $client
	 * @param string $body
	 * @return array
	 * @throws RuntimeException
	 */
	private function getResponse(HttpClient $client, string $body): array
	{
		$status = $client->getStatus();

		if ($body === false)
		{
			$errors = $client->getError();

			throw new RuntimeException(
				implode('; ', array_map(
					function ($v, $k) { return sprintf("%s=%s", $k, $v); },
					$errors,
					array_keys($errors)
				))
			);
		}

		if ($status != 200)
		{
			throw new RuntimeException(sprintf('Unexpected status code - %s', $status));
		}

		try
		{
			$response = Json::decode($body);
		}
		catch (ArgumentException $e)
		{
			throw new RuntimeException('JSON decode error');
		}

		if (!is_array($response))
		{
			throw new RuntimeException('Response format error');
		}

		return $response;
	}

	/**
	 * @return HttpClient
	 * @throws ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	private function makeHttpClient(): HttpClient
	{
		$token = $this->source->getOsmToken();

		$result = new HttpClient(
			[
				'version' => self::HTTP_VERSION,
				'socketTimeout' => self::HTTP_SOCKET_TIMEOUT,
				'streamTimeout' => self::HTTP_STREAM_TIMEOUT,
			]
		);

		$result->setHeader(
			'Authorization',
			sprintf('Bearer %s', ($token ? $token->getToken() : ''))
		);

		$result->setHeader('Bx-Location-Osm-Host', $this->source->getOsmHostName());

		return $result;
	}

	/**
	 * @param string $controller
	 * @param string $action
	 * @param array $queryData
	 * @return string
	 */
	private function buildUrl(string $controller, string $action, array $queryData): string
	{
		$serviceUrl = $this->source->getOsmApiUrl();

		if (!$serviceUrl)
		{
			throw new RuntimeException('Service url is not specified');
		}

		return sprintf(
			'%s/?%s',
			$serviceUrl,
			http_build_query(
				array_merge(
					$queryData,
					[
						'action' => sprintf('osmgateway.%s.%s', $controller, $action)
					]
				)
			)
		);
	}

	/**
	 * @param array $queryData
	 * @return array[]
	 */
	private function wrapQueryData(array $queryData): array
	{
		return [
			'params' => $queryData
		];
	}
}
