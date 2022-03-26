<?php

namespace Bitrix\MessageService\Sender\Sms;

use Bitrix\Main\Application;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;
use Bitrix\Main\Text\StringHelper;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;
use Bitrix\MessageService\DTO;
use Bitrix\MessageService\MessageStatus;
use Bitrix\MessageService\Sender;

class SmsEdnaru extends Sender\BaseConfigurable
{
	use Sender\Traits\RussianProvider;

	public const ID = 'smsednaru';

	protected const JSON_API_URL = 'https://sms.edna.ru/connector_sme/api/';

	public function getId()
	{
		return static::ID;
	}

	public function getName()
	{
		return Loc::getMessage('MESSAGESERVICE_SENDER_SMS_SMSEDNARU_NAME');
	}

	public function getShortName()
	{
		return 'sms.edna.ru';
	}

	public function isRegistered()
	{
		return !is_null($this->getOption('apiKey'));
	}

	public function register(array $fields)
	{
		$this->setOption('apiKey', $fields['apiKey']);

		return $this->callExternalMethod('smsSubject/');
	}

	public function getOwnerInfo()
	{
		return [
			'apiKey' => $this->getOption('apiKey'),
			'subjects' => array_column($this->getFromList(), 'name'),
		];
	}

	public function getExternalManageUrl()
	{
		return 'https://sms.edna.ru/';
	}

	public function getRegistrationUrl(): string
	{
		return 'https://edna.ru/sms-bitrix/';
	}

	public function getMessageStatus(array $messageFields)
	{
		$result = new Sender\Result\MessageStatus();
		$result->setId($messageFields['ID']);
		$result->setExternalId($messageFields['ID']);

		if (!$this->canUse())
		{
			$result->addError(new Error(Loc::getMessage('MESSAGESERVICE_SENDER_SMS_SMSEDNARU_USE_ERROR')));
			return $result;
		}

		$apiResult = $this->callExternalMethod("smsOutMessage/{$messageFields['ID']}");
		if (!$apiResult->isSuccess())
		{
			$result->addErrors($apiResult->getErrors());
		}
		else
		{
			$apiData = $apiResult->getData();

			$result->setStatusText($apiData['dlvStatus']);
			$result->setStatusCode(static::resolveStatus($apiData['dlvStatus']));
		}

		return $result;
	}

	public function sendMessage(array $messageFields)
	{
		$result = new Sender\Result\SendMessage();

		if (!$this->canUse())
		{
			$result->addError(new Error('Cant use'));
			return $result;
		}

		$validationResult = $this->validatePhoneNumber($messageFields['MESSAGE_TO']);

		if ($validationResult->isSuccess())
		{
			$phoneNumber = $validationResult->getData()['validNumber'];
		}
		else
		{
			$result->addErrors($validationResult->getErrors());
			return $result;
		}

		$params = [
			'id' => uniqid('', true),
			'subject' => $messageFields['MESSAGE_FROM'],
			'address' => $phoneNumber,
			'priority' => 'high',
			'contentType' => 'text',
			'content' => $messageFields['MESSAGE_BODY'],
		];

		$apiResult = $this->callExternalMethod('smsOutMessage', $params);
		$result->setServiceRequest($apiResult->getHttpRequest());
		$result->setServiceResponse($apiResult->getHttpResponse());

		if (!$apiResult->isSuccess())
		{
			$result->addErrors($apiResult->getErrors());
		}
		else
		{
			$apiData = $apiResult->getData();

			$result->setExternalId($apiData['id']);
			$result->setAccepted();
		}

		return $result;
	}

	protected function validatePhoneNumber(string $number): Result
	{
		$result = new Result();

		$number = str_replace('+', '', $number);
		$apiResult = $this->callExternalMethod("validatePhoneNumber/{$number}");
		if ($apiResult->isSuccess())
		{
			$result->setData(['validNumber' => $number]);
		}
		else
		{
			$result->addErrors($apiResult->getErrors());
		}

		return $result;
	}

	public function getFromList()
	{
		$fromList = [];
		if (!$this->canUse())
		{
			return $fromList;
		}

		$apiResult = $this->callExternalMethod('smsSubject/');
		if (!$apiResult->isSuccess())
		{
			return $fromList;
		}
		else
		{
			foreach ($apiResult->getData() as $subjectInfo)
			{
				if ($subjectInfo['active'])
				{
					$fromList[] = [
						'id' => $subjectInfo['subject'],
						'name' => $subjectInfo['subject'],
					];
				}
			}

			return $fromList;
		}
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
			default:
				return
					mb_substr($serviceStatus, 0, mb_strlen('error')) === 'error'
						? MessageStatus::ERROR
						: null
					;
		}
	}

	protected function callExternalMethod(string $method, ?array $params = null): Sender\Result\HttpRequestResult
	{
		$url = static::JSON_API_URL . $method;
		$queryMethod = HttpClient::HTTP_GET;

		$httpClient = new HttpClient([
			'socketTimeout' => 10,
			'streamTimeout' => 30,
			'waitResponse' => true,
		]);
		$httpClient->setHeader('User-Agent', 'Bitrix24');
		$httpClient->setHeader('Content-type', 'application/json');
		$httpClient->setHeader('X-API-KEY', $this->getOption('apiKey'));
		$httpClient->setCharset('UTF-8');

		if (is_array($params))
		{
			$queryMethod = HttpClient::HTTP_POST;
			$params = Json::encode($params);
		}

		$result = new Sender\Result\HttpRequestResult();
		$result->setHttpRequest(new DTO\Request([
			'method' => $queryMethod,
			'uri' => $url,
			'headers' => method_exists($httpClient, 'getRequestHeaders') ? $httpClient->getRequestHeaders()->toArray() : [],
			'body' => $params
		]));

		if ($httpClient->query($queryMethod, $url, $params))
		{
			if ($httpClient->getStatus() !== 200)
			{
				$answer = [
					'code' => $httpClient->getStatus(),
					'error' => $this->getMessageByErrorCode('error-' . $httpClient->getStatus()),
				];
			}
			else
			{
				$answer = $this->parseExternalAnswer($httpClient->getResult());
			}
		}
		else
		{
			$error = $httpClient->getError();
			$answer = [
				'code' => key($error),
				'error' => current($error),
			];
		}
		$result->setHttpResponse(new DTO\Response([
			'statusCode' => $httpClient->getStatus(),
			'headers' => $httpClient->getHeaders()->toArray(),
			'body' => $httpClient->getResult(),
			'error' => Sender\Util::getHttpClientErrorString($httpClient)
		]));

		if (array_key_exists('code', $answer) && $answer['code'] !== 'ok')
		{
			$result->addError(new Error($answer['error'], $answer['code'], $answer));
		}
		else
		{
			$result->setData($answer);
		}

		return $result;
	}

	protected function getMessageByErrorCode(string $code)
	{
		$locCode = 'MESSAGESERVICE_SENDER_SMS_SMSEDNARU_';
		$locCode .= StringHelper::str_replace('-', '_', mb_strtoupper($code));

		return Loc::getMessage($locCode) ?? $code;
	}

	protected function parseExternalAnswer(string $httpResult): array
	{
		try
		{
			return Json::decode($httpResult);
		}
		catch (ArgumentException $exception)
		{
			return ['error' => 'error-json-parsing'];
		}
	}
}
