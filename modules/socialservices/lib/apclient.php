<?php
namespace Bitrix\Socialservices;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\SystemException;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Web\Uri;

class ApClient
{
	const ERROR_WRONG_ANSWER = 'WRONG_ANWSER';

	const METHOD_BATCH = 'batch';

	const HTTP_SOCKET_TIMEOUT = 10;
	const HTTP_STREAM_TIMEOUT = 10;

	protected $connection = null;
	protected $errorCollection = null;

	protected static $requiredKeys = array('ENDPOINT');

	public static function init()
	{
		$connection = ApTable::getConnection();
		if($connection)
		{
			return new self($connection);
		}

		return false;
	}

	public static function initById($connectionId)
	{
		$dbRes = ApTable::getById($connectionId);
		$connection = $dbRes->fetch();

		if($connection)
		{
			return new self($connection);
		}

		return false;
	}

	public function __construct(array $connection)
	{
		$this->errorCollection = new ErrorCollection();

		if($this->checkConnection($connection))
		{
			$this->connection = $connection;
		}
	}

	public function getConnection()
	{
		return $this->connection;
	}

	/**
	 * Low-level function for REST method call. Returns method response including paging params and error messages.
	 *
	 * @param string $methodName Method name.
	 * @param array|null $additionalParams Method params.
	 *
	 * @return bool|mixed
	 *
	 * @throws ArgumentException
	 * @throws ArgumentNullException
	 * @throws SystemException
	 */
	public function call($methodName, $additionalParams = null)
	{
		if($this->checkConnection($this->connection))
		{
			$request = $this->prepareRequest($additionalParams);

			$httpClient = $this->getHttpClient();

			$response = $httpClient->post(
				$this->getRequestUrl($methodName),
				$request
			);

			$result = $this->prepareResponse($response);

			if(!$result)
			{
				$this->errorCollection->add(
					array(
						new Error("Wrong answer from service", static::ERROR_WRONG_ANSWER)
					)
				);
			}

			return $result;
		}
		else
		{
			throw new SystemException("No connection credentials");
		}
	}

	public function batch($actions)
	{
		$batch = array();

		if(is_array($actions))
		{
			foreach($actions as $queryKey => $cmdData)
			{
				if(is_array($cmdData))
				{
					list($cmd, $cmdParams) = array_values($cmdData);
					$batch['cmd'][$queryKey] = $cmd.(is_array($cmdParams) ? '?'.http_build_query($this->prepareRequestData($cmdParams)) : '');
				}
				else
				{
					$batch['cmd'][$queryKey] = $cmdData;
				}
			}
		}

		return $this->call(static::METHOD_BATCH, $batch);
	}

	public function getErrorCollection()
	{
		return $this->errorCollection;
	}

	protected function getHttpClient()
	{
		return new HttpClient(array(
			'socketTimeout' => static::HTTP_SOCKET_TIMEOUT,
			'streamTimeout' => static::HTTP_STREAM_TIMEOUT,
		));
	}

	protected function getRequestUrl($methodName)
	{
		return $this->connection['ENDPOINT'].$methodName;
	}

	protected function prepareRequestData($additionalParams)
	{
		if(!is_array($additionalParams))
		{
			$additionalParams = array();
		}

		return $additionalParams;
	}

	protected function prepareRequest($additionalParams)
	{
		$additionalParams = $this->prepareRequestData($additionalParams);

		return $additionalParams;
	}

	protected function prepareResponse($result)
	{
		try
		{
			return Json::decode($result);
		}
		catch(ArgumentException $e)
		{
			return false;
		}
	}

	protected function checkConnection(array $connection)
	{
		foreach(static::$requiredKeys as $key)
		{
			if(empty($connection[$key]))
			{
				throw new ArgumentNullException('connection['.$key.']');
			}
		}

		$endpoint = new Uri($connection['ENDPOINT']);
		if(!$endpoint->getHost())
		{
			throw new ArgumentException('Invalid connection[ENDPOINT] value');
		}

		return true;
	}
}