<?php

namespace Bitrix\Pull;

use Bitrix\Main;

class JsonRpcTransport
{
	protected const VERSION = '2.0';
	protected const METHOD_PUBLISH = 'publish';
	protected const METHOD_GET_LAST_SEEN = 'getUsersLastSeen';
	protected const HEADER_HOST_ID = 'X-HostId';

	/**
	 * @param \Bitrix\Pull\DTO\Message[] $messages
	 * @param array $options
	 * @return Main\Result
	 * @throws Main\SystemException
	 * @see DTO\Message
	 */
	public static function sendMessages(array $messages, array $options = []): Main\Result
	{
		$result = new Main\Result();
		if(!Config::isJsonRpcUsed())
		{
			throw new Main\SystemException("Sending messages in json-rpc format is not supported by the queue server");
		}

		$batchList = static::createRequestBatches($messages);

		foreach ($batchList as $batch)
		{
			$executeResult = static::executeBatch($batch, $options);
			if (!$executeResult->isSuccess())
			{
				return $result->addErrors($executeResult->getErrors());
			}
		}

		return $result;
	}

	public static function getUsersLastSeen(array $userList, array $options = []): Main\Result
	{
		if(!Config::isJsonRpcUsed())
		{
			throw new Main\SystemException("Sending messages in json-rpc format is not supported by the queue server");
		}

		$rpcResult = static::executeMethod(
			self::METHOD_GET_LAST_SEEN,
			[
				'userList' => $userList
			],
			$options
		);

		if (!$rpcResult->isSuccess())
		{
			return $rpcResult;
		}

		$response = $rpcResult->getData();
		$data = is_array($response['result']) ? $response['result'] : [];
		$result = new Main\Result();

		return $result->setData($data);
	}

	/**
	 * @param \Bitrix\Pull\DTO\Message[] $messages
	 * @return array[]
	 */
	protected static function createRequestBatches(array $messages): array
	{
		// creates just one batch right now
		$result = [];
		foreach ($messages as $message)
		{
			$message->userList = array_values($message->userList);
			$message->channelList = array_values($message->channelList);
			$result[] = static::createJsonRpcRequest(static::METHOD_PUBLISH, $message);
		}
		return [$result];
	}

	/**
	 * @param string $method
	 * @param mixed $params
	 * @return array
	 */
	protected static function createJsonRpcRequest(string $method, $params): array
	{
		return [
			'jsonrpc' => static::VERSION,
			'method' => $method,
			'params' => $params
		];
	}

	protected static function executeMethod(string $method, array $params, array $options = []): Main\Result
	{
		$result = new Main\Result();
		$rpcRequest = static::createJsonRpcRequest($method, $params);

		try
		{
			$body = Main\Web\Json::encode($rpcRequest);
		}
		catch (\Throwable $e)
		{
			return $result->addError(new \Bitrix\Main\Error($e->getMessage(), $e->getCode()));
		}
		$httpResult = static::performHttpRequest($body, $options);
		if (!$httpResult->isSuccess())
		{
			return $result->addErrors($httpResult->getErrors());
		}
		$response = $httpResult->getData();
		if (!isset($response['jsonrpc']) || $response['jsonrpc'] != static::VERSION)
		{
			return $result->addError(new \Bitrix\Main\Error('Wrong response structure'));
		}
		if (is_array($response['error']))
		{
			return $result->addError(new \Bitrix\Main\Error($response['error']['message'], $response['error']['code']));
		}

		return $result->setData($response);
	}

	protected static function executeBatch(array $requestBatch, array $options = []): Main\Result
	{
		$result = new Main\Result();
		try
		{
			$body = Main\Web\Json::encode($requestBatch);
		}
		catch (\Throwable $e)
		{
			return $result->addError(new \Bitrix\Main\Error($e->getMessage(), $e->getCode()));
		}
		$httpResult = static::performHttpRequest($body, $options);
		if (!$httpResult->isSuccess())
		{
			return $result->addErrors($httpResult->getErrors());
		}
		$response = $result->getData();

		return $result->setData($response);
	}

	protected static function performHttpRequest(string $body, array $options = []): Main\Result
	{
		$result = new Main\Result();
		$httpClient = new Main\Web\HttpClient();
		$httpClient->setHeader(self::HEADER_HOST_ID, (string)Config::getHostId());

		$queueServerUrl = $options['serverUrl'] ?? Config::getJsonRpcUrl();
		$signature = \CPullChannel::GetSignature($body);
		$urlWithSignature = \CHTTP::urlAddParams($queueServerUrl, ["signature" => $signature]);

		$sendResult = $httpClient->query(Main\Web\HttpClient::HTTP_POST, $urlWithSignature, $body);
		if (!$sendResult)
		{
			$errorCode = array_key_first($httpClient->getError());
			$errorMsg = $httpClient->getError()[$errorCode];
			return $result->addError(new Main\Error($errorMsg, $errorCode));
		}
		$responseCode = (int)$httpClient->getStatus();
		if ($responseCode !== 200)
		{
			return $result->addError(new Main\Error("Unexpected server response code {$responseCode}"));
		}
		$responseBody = $httpClient->getResult();
		if ($responseBody == '')
		{
			return $result->addError(new Main\Error('Empty server response'));
		}
		try
		{
			$decodedBody = Main\Web\Json::decode($responseBody);
		}
		catch (\Throwable $e)
		{
			return $result->addError(new Main\Error('Could not decode server response. Raw response: ' . $responseBody));
		}

		return $result->setData($decodedBody);
	}
}