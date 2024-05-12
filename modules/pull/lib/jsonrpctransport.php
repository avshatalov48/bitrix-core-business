<?php

namespace Bitrix\Pull;

use Bitrix\Main;

class JsonRpcTransport
{
	protected const VERSION = '2.0';
	protected const METHOD_PUBLISH = 'publish';
	protected const METHOD_GET_LAST_SEEN = 'getUsersLastSeen';
	protected const METHOD_UPDATE_LAST_SEEN = 'updateUsersLastSeen';

	protected string $serverUrl = '';

	function __construct(array $options = [])
	{
		$this->serverUrl = $options['serverUrl'] ?? Config::getJsonRpcUrl();
	}

	/**
	 * @param \Bitrix\Pull\DTO\Message[] $messages
	 * @param array $options
	 * @return Main\Result
	 * @see DTO\Message
	 */
	public function sendMessages(array $messages): TransportResult
	{
		$result = new TransportResult();
		$result->withRemoteAddress($this->serverUrl);
		try
		{
			$batchList = static::createRequestBatches($messages);
		}
		catch (\Throwable $e)
		{
			return $result->addError(new \Bitrix\Main\Error($e->getMessage(), $e->getCode()));
		}

		foreach ($batchList as $batch)
		{
			$executeResult = static::executeBatch($this->serverUrl, $batch);
			if (!$executeResult->isSuccess())
			{
				return $result->addErrors($executeResult->getErrors());
			}
		}

		return $result;
	}

	public function getUsersLastSeen(array $userList): Main\Result
	{
		$rpcResult = static::executeMethod(
			$this->serverUrl,
			static::METHOD_GET_LAST_SEEN,
			[
				'userList' => $userList
			]
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
	 * Communicates users' last seen timestamps to the queue server.
	 *
	 * @param array $userTimestamps USER_ID => LAST_SEEN_TIMESTAMP
	 * @return Main\Result
	 */
	public function updateUsersLastSeen(array $userTimestamps): Main\Result
	{
		return static::executeMethod(
			$this->serverUrl,
			static::METHOD_UPDATE_LAST_SEEN,
			$userTimestamps
		);
	}

	/**
	 * @param \Bitrix\Pull\DTO\Message[] $messages
	 * @return string[]
	 */
	protected static function createRequestBatches(array $messages): array
	{
		// creates just one batch right now
		$maxPayload = \CPullOptions::GetMaxPayload() - 20;

		$result = [];
		$currentBatch = [];
		$currentBatchSize = 2; // opening and closing bracket
		foreach ($messages as $message)
		{
			$message->userList = array_values($message->userList);
			$message->channelList = array_values($message->channelList);
			$jsonRpcMessage = Main\Web\Json::encode(static::createJsonRpcRequest(static::METHOD_PUBLISH, $message));
			if (mb_strlen($jsonRpcMessage) > $maxPayload - 20)
			{
				trigger_error("Pull message exceeds size limit, skipping", E_USER_WARNING);
			}
			if (($currentBatchSize + mb_strlen($jsonRpcMessage)) + 1> $maxPayload)
			{
				// start new batch
				$result[] = "[" . implode(",", $currentBatch) . "]";
				$currentBatch = [];
				$currentBatchSize = 2;
			}
			$currentBatch[] = $jsonRpcMessage;
			$currentBatchSize += (mb_strlen($jsonRpcMessage)) + 1; // + comma
		}
		if (count($currentBatch) > 0)
		{
			$result[] = "[" . implode(",", $currentBatch) . "]";
		}
		return $result;
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

	protected static function executeMethod(string $queueServerUrl, string $method, array $params): Main\Result
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
		$httpResult = static::performHttpRequest($queueServerUrl, $body);
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

	protected static function executeBatch(string $queueServerUrl, string $batchBody): Main\Result
	{
		$result = new Main\Result();
		$httpResult = static::performHttpRequest($queueServerUrl, $batchBody);
		if (!$httpResult->isSuccess())
		{
			return $result->addErrors($httpResult->getErrors());
		}
		$response = $result->getData();

		return $result->setData($response);
	}

	protected static function performHttpRequest(string $queueServerUrl, string $body): Main\Result
	{
		$result = new Main\Result();
		$httpClient = new Main\Web\HttpClient(["streamTimeout" => 1]);

		$signature = \CPullChannel::GetSignature($body);
		$hostId = (string)Config::getHostId();
		$urlWithSignature = \CHTTP::urlAddParams($queueServerUrl, ["hostId" => $hostId, "signature" => $signature]);

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