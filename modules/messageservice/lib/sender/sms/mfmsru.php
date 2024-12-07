<?php
namespace Bitrix\MessageService\Sender\Sms;

use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Error;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Result;
use Bitrix\Main\Web\HttpClient;

use Bitrix\MessageService\Sender;
use Bitrix\MessageService\Sender\Result\MessageStatus;
use Bitrix\MessageService\Sender\Result\SendMessage;

use Bitrix\MessageService;

class MfmsRu extends Sender\BaseConfigurable
{
	public const ID = 'mfmsru';

	public static function isSupported()
	{
		return (
			ModuleManager::isModuleInstalled('b24network')
			|| Option::get('messageservice', 'mfmsru_enabled', 'N') === 'Y'
		);
	}

	public function getId()
	{
		return static::ID;
	}

	public function getName()
	{
		return 'mfms.ru';
	}

	public function getShortName()
	{
		return 'mfms.ru';
	}

	public function getFromList()
	{
		$from = $this->getOption('from_list');
		return is_array($from) ? $from : [];
	}

	public function isRegistered()
	{
		return (
			$this->getOption('login') !== null
			&&
			$this->getOption('password') !== null
		);
	}

	public function register(array $fields)
	{
		$login = (string) $fields['login'];
		$password = (string) $fields['password'];
		$from = (string) $fields['from_list'];
		$sendUrl = (string) $fields['hpg_send_url'];
		$statusUrl = (string) $fields['hpg_status_url'];

		$result = new Result();
		if ($login && $password && $from && $sendUrl && $statusUrl)
		{
			$this->setOption('login', $login);
			$this->setOption('password', $password);
			$this->setOption('hpg_send_url', $sendUrl);
			$this->setOption('hpg_status_url', $statusUrl);

			$from = array_map(function($v) {
				$v = trim($v);
				return ['id' => $v, 'name' => $v];
			}, explode(';', $from));

			$this->setOption('from_list', $from);
		}
		else
		{
			$result->addError(new Error('Empty required fields.'));
		}

		return $result;
	}

	/**
	 * @return array [
	 * 	'login' => ''
	 * ]
	 */
	public function getOwnerInfo()
	{
		return array(
			'login' => $this->getOption('login'),
			'fromList' => $this->getFromList(),
		);
	}

	public function getExternalManageUrl()
	{
		return 'https://mfms.ru';
	}

	public function sendMessage(array $messageFields)
	{
		if (!$this->canUse())
		{
			$result = new SendMessage();
			$result->addError(new Error('Service is unavailable'));
			return $result;
		}

		return $this->sendViaHpg($messageFields);
	}

	public function getMessageStatus(array $messageFields)
	{
		if (!$this->canUse())
		{
			$result = new MessageStatus();
			$result->addError(new Error('Service is unavailable'));
			return $result;
		}

		return $this->getStatusViaHpg($messageFields);
	}

	public static function resolveStatus($serviceStatus)
	{
		$status = parent::resolveStatus($serviceStatus);

		switch ($serviceStatus)
		{
			case 'delayed':
			case 'enqueued':
				return MessageService\MessageStatus::ACCEPTED;
				break;
			case 'sent':
				return MessageService\MessageStatus::SENT;
				break;
			case 'delivered':
				return MessageService\MessageStatus::DELIVERED;
				break;
			case 'undelivered':
				return MessageService\MessageStatus::UNDELIVERED;
				break;
			case 'cancelled':
			case 'failed':
				return MessageService\MessageStatus::FAILED;
				break;
		}

		return $status;
	}

	private function sendViaHpg(array $messageFields)
	{
		$result = new SendMessage();

		$params = [
			'login' => $this->getOption('login'),
			'pass' => $this->getOption('password'),
			'subject' => $messageFields['MESSAGE_FROM'],
			'address' => str_replace('+', '', $messageFields['MESSAGE_TO']),
			'text' => $this->prepareMessageBodyForSend($messageFields['MESSAGE_BODY']),
		];

		$remoteCallResult = $this->touchHpg($this->getOption('hpg_send_url'), $params);
		$result->setServiceRequest($remoteCallResult->getHttpRequest());
		$result->setServiceResponse($remoteCallResult->getHttpResponse());
		if (!$remoteCallResult->isSuccess())
		{
			$result->addErrors($remoteCallResult->getErrors());
		}
		else
		{
			$answer = $remoteCallResult->getData();
			[$code, $index, $msgId] = $answer;

			if ($msgId)
			{
				$result->setExternalId($msgId);
				$result->setStatus(MessageService\MessageStatus::SENT);
			}
		}

		return $result;
	}

	private function getStatusViaHpg(array $messageFields)
	{
		$result = new MessageStatus();
		$result->setId($messageFields['ID']);
		$result->setExternalId($messageFields['EXTERNAL_ID']);

		$params = [
			'login' => $this->getOption('login'),
			'password' => $this->getOption('password'),
			'providerId' => [$messageFields['EXTERNAL_ID']],
		];

		$remoteCallResult= $this->touchHpg($this->getOption('hpg_status_url'), $params);

		if (!$remoteCallResult->isSuccess())
		{
			$result->addErrors($remoteCallResult->getErrors());
		}
		else
		{
			$answer = $remoteCallResult->getData();
			[$code, $msgId, $status, $date, $reason] = $answer;

			if ($msgId)
			{
				$result->setStatusText($status);
				$result->setStatusCode(self::resolveStatus($status));
			}
		}

		return $result;
	}

	private function touchHpg($url, array $params): Sender\Result\HttpRequestResult
	{
		$result = new Sender\Result\HttpRequestResult();

		$httpClient = new HttpClient(array(
			"socketTimeout" => $this->socketTimeout,
			"streamTimeout" => $this->streamTimeout,
			"waitResponse" => true,
		));
		$httpClient->setHeader('User-Agent', 'Bitrix24');
		$httpClient->setCharset('UTF-8');

		$answer = '';

		$url .= '?'.http_build_query($params);

		if ($httpClient->query(HttpClient::HTTP_GET, $url))
		{
			$answer = $httpClient->getResult();
		}

		if ($httpClient->getStatus() != '200')
		{
			$result->addError(new Error($answer));
		}
		else
		{
			$status = explode(';', $answer)[0];
			if ($status !== 'ok')
			{
				$result->addError(new Error($status));
			}
		}

		$result->setHttpRequest(new MessageService\DTO\Request([
			'method' => HttpClient::HTTP_GET,
			'uri' => $url,
			'headers' => method_exists($httpClient, 'getRequestHeaders') ? $httpClient->getRequestHeaders()->toArray() : [],
		]));
		$result->setHttpResponse(new MessageService\DTO\Response([
			'statusCode' => $httpClient->getStatus(),
			'headers' => $httpClient->getHeaders()->toArray(),
			'body' => $answer,
			'error' => Sender\Util::getHttpClientErrorString($httpClient)
		]));
		$result->setData(explode(';', $answer));

		return $result;
	}
}