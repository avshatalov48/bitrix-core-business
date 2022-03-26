<?php
namespace Bitrix\MessageService\Sender\Sms;

use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;
use Bitrix\MessageService\DTO;
use Bitrix\MessageService\MessageStatus;
use Bitrix\MessageService\Sender;

/**
 * Class DummyHttp. For testing purposes only. It performs HTTP request with message parameters to the configured address.
 */
class DummyHttp extends Sender\BaseConfigurable
{
	public const ID = 'dummy_http';

	public function getId()
	{
		return static::ID;
	}

	public function getName()
	{
		return 'Dummy HTTP SMS';
	}

	public function getShortName()
	{
		return $this->getName();
	}

	public function getFromList()
	{
		return [
			[
				'id' => 'test',
				'name' => 'test',
			]
		];
	}

	public function sendMessage(array $messageFields): Sender\Result\SendMessage
	{
		$result = new Sender\Result\SendMessage();
		$host = $this->getOption('remoteHost');
		if (!$host)
		{
			return $result->addError(new Error('Provider is not configured'));
		}
		$requestBody = Json::encode($messageFields);

		$result->setServiceRequest(new DTO\Request([
			'method' => HttpClient::HTTP_POST,
			'uri' => $host,
			'body' => $requestBody,
		]));
		$httpClient = new HttpClient();
		$queryResult = $httpClient->query(HttpClient::HTTP_POST, $host, $requestBody);
		if (!$queryResult)
		{
			$error = $httpClient->getError();
			$errorCode = array_key_first($error);
			$result->setServiceResponse(new DTO\Response([
				'error' => Sender\Util::getHttpClientErrorString($httpClient)
			]));
			return $result->addError(new Error($error[$errorCode], $errorCode));
		}

		$result->setServiceResponse(new DTO\Response([
			'statusCode' => $httpClient->getStatus(),
			'headers' => $httpClient->getHeaders()->toArray(),
			'body' => $httpClient->getResult(),
		]));
		$responseCode = $httpClient->getStatus();
		if ($responseCode !== 200)
		{
			return $result->addError(new Error("HTTP response code {$responseCode}", "HTTP_{$responseCode}"));
		}

		$responseBody = $httpClient->getResult();
		if ($responseBody == '')
		{
			return $result->addError(new Error("Empty response", "EMPTY_RESPONSE"));
		}

		try
		{
			$decoded = Json::decode($responseBody);
			$result->setExternalId($decoded['message_id']);
			$result->setAccepted();
		}
		catch (\Throwable $e)
		{
			$result->addError(new Error("JSON decode error", "JSON_ERROR"));
		}
		return $result;
	}

	public function canUse()
	{
		return true;
	}

	public function isRegistered()
	{
		return true;
	}

	public function register(array $fields)
	{
		$this->setOption('remoteHost', $fields['remoteHost']);

		return new Result();
	}

	public function getOwnerInfo()
	{
		// TODO: Implement getOwnerInfo() method.
	}

	public function getRemoteHost()
	{
		return $this->getOption('remoteHost');
	}

	public function getExternalManageUrl()
	{
		return '';
	}

	public function getMessageStatus(array $messageFields)
	{
		$result = new Sender\Result\MessageStatus();
		$result->setId($messageFields['ID']);
		$result->setExternalId($messageFields['ID']);
		$result->setStatusCode(\Bitrix\MessageService\MessageStatus::DELIVERED);

		return $result;
	}

	public static function resolveStatus($serviceStatus)
	{
		switch ($serviceStatus)
		{
			case 'read':
			case 'sent':
				return MessageStatus::SENT;
			case 'enqueued':
				return MessageStatus::QUEUED;
			case 'delayed':
				return MessageStatus::ACCEPTED;
			case 'delivered':
				return MessageStatus::DELIVERED;
			case 'undelivered':
				return MessageStatus::UNDELIVERED;
			case 'failed':
			case 'cancelled':
				return MessageStatus::FAILED;
		}

		return parent::resolveStatus($serviceStatus);
	}
}
