<?php

namespace Bitrix\Location\Source\Google\Requesters;

use Bitrix\Location\Common\CachedPool;
use Bitrix\Location\Exception\RuntimeException;
use Bitrix\Location\Infrastructure\Service\LoggerService;
use Bitrix\Location\Infrastructure\Service\LoggerService\LogLevel;
use Bitrix\Location\Infrastructure\Service\LoggerService\EventType;
use Bitrix\Location\Source\Google\ErrorCodes;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;

/**
 * class Base
 * @package Bitrix\Location\Source\Google\Requesters
 */
abstract class BaseRequester
{
	/** @var HttpClient  */
	protected $httpClient = null;
	/** @var mixed|string  */
	protected $url = '';
	/** @var array  */
	protected $requiredFields = [];
	/** @var array  */
	protected $fieldsToEncode = [];
	/** @var UrlMaker */
	protected $urlMaker = null;
	/** @var ?CachePool  */
	protected $cachePool = null;

	/**
	 * BaseRequester constructor.
	 * @param HttpClient $httpClient
	 * @param CachedPool|null $cachePool
	 */
	public function __construct(HttpClient $httpClient, CachedPool $cachePool = null)
	{
		$this->httpClient = $httpClient;
		$this->cachePool = $cachePool;
		$this->urlMaker = new UrlMaker();
	}

	/**
	 * @param array $params
	 * @param string $template
	 * @return string
	 * @throws \Bitrix\Main\ArgumentNullException
	 */
	protected function makeUrl(array $params): string
	{
		return $this->urlMaker->make(
			$params,
			$this->url,
			$this->requiredFields,
			$this->fieldsToEncode
		);
	}

	/**
	 * @return LoggerService
	 */
	protected function getLoggerService(): LoggerService
	{
		return LoggerService::getInstance();
	}

	/**
	 * @param string $url
	 * @return string
	 */
	protected function createCacheItemKey(string $url): string
	{
		return md5(
			preg_replace('/&location=[0-9.,]*&/', '&', $url)
		);
	}

	/**
	 * @param array $params
	 * @return array|bool
	 */
	public function request(array $params)
	{
		$url = $this->makeUrl($params);
		$result = false;
		$httpRes = false;
		$loggerService = $this->getLoggerService();
		$loggerService->log(LogLevel::DEBUG, $url, EventType::SOURCE_GOOGLE_REQUESTER_URL);
		$cacheItemKey = $this->createCacheItemKey($url);
		$takenFromCache = false;

		if($this->cachePool && $cachedAnswer = $this->cachePool->getItem($cacheItemKey))
		{
			$httpRes = $cachedAnswer['httpRes'];
			$errors = $cachedAnswer['errors'];
			$status = $cachedAnswer['status'];

			$takenFromCache = true;
		}
		else
		{
			if (@$this->httpClient->get($url))
			{
				$httpRes = $this->httpClient->getResult();
			}

			$errors = $this->httpClient->getError();
			$status = $this->httpClient->getStatus();
		}

		$loggerService->log(
			LogLevel::DEBUG,
			'{"httpRes":"'.$httpRes.'","errors":"'.	$this->convertErrorsToString($errors).'","status":"'.$status.'"}',
			EventType::SOURCE_GOOGLE_REQUESTER_RESULT
		);

		if(!$httpRes && !empty($errors))
		{
			throw new RuntimeException(
				$this->convertErrorsToString($errors),
				ErrorCodes::REQUESTER_BASE_HTTP_ERROR
			);
		}
		else
		{
			$result = [];

			if($httpRes)
			{
				try
				{
					$result = Json::decode($httpRes);
				}
				catch(\Exception $e)
				{
					$message = 'Can\'t decode Google server\'s answer.'
						. ' URL: ' . $url
						. ' Answer: '. $httpRes;

					throw new RuntimeException($message, ErrorCodes::REQUESTER_BASE_JSON_ERROR);
				}

				if(!$takenFromCache
					&& $this->cachePool
					&& $status === 200
					&& empty($errors)
					&& isset($result['status'])
					&& $result['status'] === 'OK'
				)
				{
					$this->cachePool->addItem(
						$cacheItemKey,
						[
							'httpRes' => $httpRes,
							'errors' => $errors,
							'status' => $status
						]
					);
				}
			}

			if ($status != 200)
			{
				$message = 'Http status: '.$status
					. ' URL: ' . $url
					. ' Answer: '. $httpRes;

				throw new RuntimeException($message, ErrorCodes::REQUESTER_BASE_STATUS_ERROR);
			}
		}

		return $result;
	}

	protected function convertErrorsToString(array $errors): string
	{
		$result = '';

		foreach ($errors as $code => $message)
		{
			$result .= $message.'['.$code.'], ';
		}

		return $result;
	}
}