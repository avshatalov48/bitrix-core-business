<?php

namespace Bitrix\Calendar\Sync\Office365;

use Bitrix\Calendar\Internals\HasStatusInterface;
use Bitrix\Calendar\Internals\ObjectStatusTrait;
use Bitrix\Calendar\Sync\Exceptions\ApiException;
use Bitrix\Calendar\Sync\Exceptions\AuthException;
use Bitrix\Calendar\Sync\Exceptions\GoneException;
use Bitrix\Calendar\Sync\Internals\ContextInterface;
use Bitrix\Calendar\Sync\Internals\HasContextTrait;
use Bitrix\Calendar\Sync\Exceptions\ConflictException;
use Bitrix\Calendar\Sync\Exceptions\NotFoundException;
use Bitrix\Calendar\Sync\Util\RequestLogger;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;
use Exception;

/**
 * Low level controller for working with rest-api.
 */
class ApiClient implements HasStatusInterface
{
	use ObjectStatusTrait, HasContextTrait;

	/** @var HttpClient */
	protected HttpClient $httpClient;
	/** @var RequestLogger */
	protected RequestLogger $logger;

	/**
	 * @param HttpClient $httpClient
	 *
	 * @param Office365Context $context
	 */
	public function __construct(HttpClient $httpClient, ContextInterface $context)
	{
		$this->httpClient = $httpClient;
		$this->context = $context;
	}

	/**
	 * @param string $method
	 * @param string $uri
	 * @param array $params
	 *
	 * @return array
	 *
	 * @throws ApiException
	 * @throws ArgumentException
	 * @throws AuthException
	 * @throws ConflictException
	 * @throws GoneException
	 * @throws NotFoundException
	 */
	public function request(string $method, string $uri, array $params): array
	{
		$getLogContext = static function (int $statusCode, $response, string $error = '' )
			use ($method, $uri, $params): array
		{
			return [
				'serviceName' => Helper::ACCOUNT_TYPE,
				'host' => Helper::SERVER_PATH,
				'method' => $method,
				'url' => $uri,
				'requestParams' => $params,
				'statusCode' => $statusCode,
				'error' => $error,
				'response' => $response,
			];
		};
		$this->getStatus()->resetErrors();
		try
		{
			$response = [];
			$paramString = $this->prepareParams($params);

			$uri = Helper::SERVER_PATH . $uri;

			$this->httpClient->waitResponse(true);
			$this->httpClient->query($method, $uri, $paramString);

			if ($this->httpClient->getStatus() < 300)
			{
				$response = $this->prepareResponse();
				$this->context->getLogger()
					->debug("API office365 success" . $this->httpClient->getStatus()
						, $getLogContext(
							$this->httpClient->getStatus(),
							$this->httpClient->getResult(),
						)
					);
			}
			else
			{
				try
				{
					$error = Json::decode($this->httpClient->getResult());
					$this->getStatus()->addError(
						"CONNECTION",
						"[" . $error['error']['code'] . "] " . $error['error']['message'],
					);
					$this->context->getLogger()
						->warning("API office365 returned error code "
								. $this->httpClient->getStatus()
								. ": " . $error['error']['message'],
							$getLogContext(
								$this->httpClient->getStatus(),
								$this->httpClient->getResult(),
								$error['error']['message']
							)
						);
					switch ($this->httpClient->getStatus())
					{
						case 401:
							throw new AuthException(
								$error['error']['code'],
								401,
								__FILE__,
								__LINE__
							);
						case 404:
							throw new NotFoundException(
								$error['error']['code'],
								404,
								__FILE__,
								__LINE__
							);
						case 409:
							throw new ConflictException(
								$error['error']['code'],
								409,
								__FILE__,
								__LINE__
							);
						case 410:
							throw new GoneException(
								$error['error']['code'],
								410,
								__FILE__,
								__LINE__
							);
						default:
							throw new ApiException(
								$error['error']['code'],
								$this->httpClient->getStatus(),
								__FILE__,
								__LINE__
							);
					}
				}
				catch (ArgumentException $exception)
				{
					$this->context->getLogger()
						->error("ArgumentException from office365", $getLogContext(
							$this->httpClient->getStatus(),
							$this->httpClient->getResult(),
							$exception->getMessage()
						));
					foreach($this->httpClient->getError() as $code => $error)
					{
						$this->getStatus()->addError($code, $error);
					}
				}
			}
		}
		catch (ApiException $e)
		{
			$this->context->getLogger()
				->error("ApiException from office365", $getLogContext(
					$e->getCode(),
					'',
					$e->getMessage()
					)
				);
			throw $e;
		}
		catch (Exception $e)
		{
			$this->context->getLogger()
				->error("Exception from office365: " . $e->getMessage(), $getLogContext(
					$e->getCode(),
					'',
					$e->getMessage()
					)
				);
			throw $e;
		}

		return $response;
	}

	/**
	 * @param string $response
	 * @param string|null $boundary
	 *
	 * @return array
	 */
	protected function multipartDecode(string $response, string $boundary): array
	{
		$events = [];

		$response = str_replace("--$boundary--", "--$boundary", $response);
		$parts = explode("--$boundary\r\n", $response);

		foreach ($parts as $part)
		{
			$part = trim($part);
			if (!empty($part))
			{
				$partEvent = explode("\r\n\r\n", $part);
				$data = $this->getMetaInfo($partEvent[1]);
				$id = $this->getId($partEvent[0]);

				if ($data['status'] === 200)
				{
					if ($id === null)
					{
						continue;
					}

					try
					{
						$event = Json::decode($partEvent[2]);
					}
					catch(Exception $exception)
					{
						continue;
					}

					$event['etag'] = $data['etag'];
					$events[$id] = $event;
				}
				else
				{
					AddMessage2Log('Event sync error. ID: ' . ($id ?? 'unknown'));
				}
			}
		}

		return $events;
	}

	private function getMetaInfo($headers): array
	{
		$data = [];
		foreach (explode("\n", $headers) as $k => $header)
		{
			if($k === 0)
			{
				if(preg_match('#HTTP\S+ (\d+)#', $header, $find))
				{
					$data['status'] = (int)$find[1];
				}
			}
			elseif(mb_strpos($header, ':') !== false)
			{
				[$headerName, $headerValue] = explode(':', $header, 2);
				if(mb_strtolower($headerName) === 'etag')
				{
					$data['etag'] = trim($headerValue);
				}
			}
		}

		return $data;
	}

	/**
	 * @param string $headers
	 *
	 * @return int|null
	 */
	private function getId(string $headers): ?int
	{
		$id = null;
		foreach (explode("\n", $headers) as $header)
		{
			if(mb_strpos($header, ':') !== false)
			{
				[$headerName, $headerValue] = explode(':', $header, 2);
				if(mb_strtolower($headerName) === 'content-id')
				{
					$part = explode(':', $headerValue);
					$id = (int) rtrim($part[1], ">");
					break;
				}
			}
		}

		return $id;
	}


	/**
	 * @param array $params
	 *
	 * @return string|null
	 *
	 * @throws ArgumentException
	 */
	protected function prepareParams(array $params): ?string
	{
		return $this->formatParams($params);
	}

	/**
	 * @param array $params
	 *
	 * @return string|null
	 *
	 * @throws ArgumentException
	 */
	protected function formatParams(array $params): ?string
	{
		return $params ? Json::encode($params, JSON_UNESCAPED_SLASHES) : null;
	}

	/**
	 * @param string $uri
	 * @param array $params
	 *
	 * @return array
	 *
	 * @throws ApiException
	 * @throws ArgumentException
	 * @throws AuthException
	 * @throws ConflictException
	 * @throws GoneException
	 * @throws NotFoundException
	 */
	public function get(string $uri, array $params = []): array
	{
		if ($params)
		{
			$uri .= (strpos($uri, '?') ? '&' : '?')
				. http_build_query($params)
			;
		}
		return $this->request(HttpClient::HTTP_GET, $uri, $params);
	}

	/**
	 * @param string $uri
	 * @param array $params
	 *
	 * @return array
	 *
	 * @throws ApiException
	 * @throws ArgumentException
	 * @throws AuthException
	 * @throws ConflictException
	 * @throws GoneException
	 * @throws NotFoundException
	 */
	public function post(string $uri, array $params = []): array
	{
		return $this->request(HttpClient::HTTP_POST, $uri, $params);
	}

	/**
	 * @param string $uri
	 * @param array $params
	 *
	 * @return array
	 *
	 * @throws ApiException
	 * @throws ArgumentException
	 * @throws AuthException
	 * @throws ConflictException
	 * @throws GoneException
	 * @throws NotFoundException
	 */
	public function delete(string $uri, array $params = []): array
	{
		return $this->request(HttpClient::HTTP_DELETE, $uri, $params);
	}

	/**
	 * @param string $uri
	 * @param array $params
	 *
	 * @return array
	 *
	 * @throws ApiException
	 * @throws ArgumentException
	 * @throws AuthException
	 * @throws ConflictException
	 * @throws GoneException
	 * @throws NotFoundException
	 */
	public function put(string $uri, array $params = []): array
	{
		return $this->request(HttpClient::HTTP_PUT, $uri, $params);
	}

	/**
	 * @param string $uri
	 * @param array $params
	 *
	 * @return array
	 *
	 * @throws ApiException
	 * @throws ArgumentException
	 * @throws AuthException
	 * @throws ConflictException
	 * @throws GoneException
	 * @throws NotFoundException
	 */
	public function patch(string $uri, array $params = []): array
	{
		return $this->request(HttpClient::HTTP_PATCH, $uri, $params);
	}

	/**
	 * @return array|mixed
	 *
	 * @throws ArgumentException
	 */
	protected function prepareResponse()
	{
		$contentType = $this->httpClient->getHeaders()->getContentType();

		if ($contentType === 'multipart/mixed')
		{
			$response = $this->multipartDecode(
				$this->httpClient->getResult(),
				$this->httpClient->getHeaders()->getBoundary()
			);
		}
		else
		{
			$response = $this->httpClient->getResult()
				? Json::decode($this->httpClient->getResult())
				: [];
		}

		return $response;
	}
}
