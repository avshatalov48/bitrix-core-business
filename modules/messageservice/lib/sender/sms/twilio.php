<?php
namespace Bitrix\MessageService\Sender\Sms;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;

use Bitrix\MessageService;
use Bitrix\MessageService\Providers\Twilio\ErrorInformant;
use Bitrix\MessageService\Sender;
use Bitrix\MessageService\Sender\Result\MessageStatus;
use Bitrix\MessageService\Sender\Result\SendMessage;

Loc::loadMessages(__FILE__);

class Twilio extends Sender\BaseConfigurable
{
	public const ID = 'twilio';

	public const NOT_SUPPORT_ALPHANUMERIC_NUMBER_STATUS_CODE = 21612;
	public const ON_BEFORE_TWILIO_MESSAGE_SEND = 'OnBeforeTwilioMessageSend';

	public function getId()
	{
		return static::ID;
	}

	public function getName()
	{
		return Loc::getMessage('MESSAGESERVICE_SENDER_SMS_TWILIO_NAME');
	}

	public function getShortName()
	{
		return 'twilio.com';
	}

	public function isRegistered()
	{
		return ($this->getOption('account_sid') !== null);
	}

	public function isDemo()
	{
		return false;
	}

	public function canUse()
	{
		return $this->isRegistered();
	}

	public function getFromList()
	{
		$from = $this->getOption('from_list');
		return is_array($from) ? $from : array();
	}

	public function register(array $fields)
	{
		$sid = (string)$fields['account_sid'];
		$token = (string)$fields['account_token'];

		$result = $this->callExternalMethod(
			HttpClient::HTTP_GET,
			'Accounts/'.$sid, array(), $sid, $token
		);
		if ($result->isSuccess())
		{
			$data = $result->getData();

			if ($data['status'] !== 'active')
			{
				$result->addError(new Error(Loc::getMessage('MESSAGESERVICE_SENDER_SMS_TWILIO_ERROR_ACCOUNT_INACTIVE')));
			}
			else
			{
				$this->setOption('account_sid', $sid);
				$this->setOption('account_token', $token);
				$this->setOption('account_friendly_name', $data['friendly_name']);
			}
		}

		return $result;
	}

	public function getOwnerInfo()
	{
		return array(
			'sid' => $this->getOption('account_sid'),
			'friendly_name' => $this->getOption('account_friendly_name')
		);
	}

	public function getExternalManageUrl()
	{
		return 'https://www.twilio.com/console';
	}

	public function sendMessage(array $messageFields): SendMessage
	{
		$sid = $this->getOption('account_sid');

		if (!$sid)
		{
			$result = new SendMessage();
			$result->addError(new Error(Loc::getMessage('MESSAGESERVICE_SENDER_SMS_TWILIO_CAN_USE_ERROR')));
			return $result;
		}

		$eventResults = self::fireEventBeforeMessageSend($messageFields);
		foreach ($eventResults as $eventResult)
		{
			$eventParams = $eventResult->getParameters();

			if ($eventResult->getType() === \Bitrix\Main\EventResult::ERROR)
			{
				$result = new SendMessage();
				if ($eventParams && is_string($eventParams))
				{
					$result->addError(new Error($eventParams));
				}
				else
				{
					$result->addError(new Error(Loc::getMessage("MESSAGESERVICE_SENDER_SMS_TWILIO_MESSAGE_HAS_NOT_BEEN_SENT")));
				}
				return $result;
			}

			if (is_array($eventParams))
			{
				$messageFields = array_merge($messageFields, $eventParams);
			}
		}

		if (isset($messageFields['MESSAGE_FROM_ALPHANUMERIC']))
		{
			$apiResult = $this->sendMessageByAlphanumericNumber($sid, $messageFields);
			if (
				!$apiResult->isSuccess()
				&& $this->checkSupportErrorAlphanumericNumber($apiResult->getErrorCollection())
			)
			{
				$apiResult = $this->sendMessageByNumber($sid, $messageFields);
			}
		}
		else
		{
			$apiResult = $this->sendMessageByNumber($sid, $messageFields);
		}

		$result = new SendMessage();
		$result->setServiceRequest($apiResult->getHttpRequest());
		$result->setServiceResponse($apiResult->getHttpResponse());

		if (!$apiResult->isSuccess())
		{
			$result->addErrors($apiResult->getErrors());
		}
		else
		{
			$resultData = $apiResult->getData();
			if (isset($resultData['sid']))
			{
				$result->setExternalId($resultData['sid']);
			}
			$result->setAccepted();
		}

		return $result;
	}

	public function getMessageStatus(array $messageFields)
	{
		$result = new MessageStatus();
		$result->setId($messageFields['ID']);
		$result->setExternalId($messageFields['EXTERNAL_ID']);

		$sid = $this->getOption('account_sid');
		if (!$sid)
		{
			$result->addError(new Error(Loc::getMessage('MESSAGESERVICE_SENDER_SMS_TWILIO_CAN_USE_ERROR')));
			return $result;
		}

		$apiResult = $this->callExternalMethod(
			HttpClient::HTTP_GET,
			'Accounts/'.$sid.'/Messages/'.$result->getExternalId()
		);
		if (!$apiResult->isSuccess())
		{
			$result->addErrors($apiResult->getErrors());
		}
		else
		{
			$resultData = $apiResult->getData();
			$result->setStatusCode($resultData['status']);
			$result->setStatusText($resultData['status']);
			if (in_array($resultData['status'],
				array('accepted', 'queued', 'sending', 'sent', 'delivered', 'undelivered', 'failed')))
			{
				$result->setStatusText(
					Loc::getMessage('MESSAGESERVICE_SENDER_SMS_TWILIO_MESSAGE_STATUS_'.mb_strtoupper($resultData['status']))
				);
			}
		}

		return $result;
	}

	public static function resolveStatus($serviceStatus)
	{
		$status = parent::resolveStatus($serviceStatus);

		switch ((string)$serviceStatus)
		{
			case 'accepted':
				return MessageService\MessageStatus::ACCEPTED;
				break;
			case 'queued':
				return MessageService\MessageStatus::QUEUED;
				break;
			case 'sending':
				return MessageService\MessageStatus::SENDING;
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
			case 'failed':
				return MessageService\MessageStatus::FAILED;
				break;
		}

		return $status;
	}

	public function sync()
	{
		if ($this->isRegistered())
		{
			$this->loadFromList();
		}
		return $this;
	}

	private function callExternalMethod($httpMethod, $apiMethod, array $params = array(), $sid = null, $token = null): Sender\Result\HttpRequestResult
	{
		$url = $this->getRequestUrl($apiMethod);

		$httpClient = new HttpClient(array(
			"socketTimeout" => $this->socketTimeout,
			"streamTimeout" => $this->streamTimeout,
			"waitResponse" => true,
		));
		$httpClient->setHeader('User-Agent', 'Bitrix24');
		$httpClient->setCharset('UTF-8');

		if (!$sid || !$token)
		{
			$sid = $this->getOption('account_sid');
			$token = $this->getOption('account_token');
		}

		$httpClient->setAuthorization($sid, $token);

		$result = new Sender\Result\HttpRequestResult();
		$answer = array();

		$result->setHttpRequest(new MessageService\DTO\Request([
			'method' => HttpClient::HTTP_POST,
			'uri' => $url,
			'headers' => method_exists($httpClient, 'getRequestHeaders') ? $httpClient->getRequestHeaders()->toArray() : [],
			'body' => $params,
		]));
		if ($httpClient->query($httpMethod, $url, $params))
		{
			try
			{
				$answer = Json::decode($httpClient->getResult());
			}
			catch (ArgumentException $e)
			{
				$result->addError(new Error('Service error'));
			}

			$httpStatus = $httpClient->getStatus();
			if ($httpStatus >= 400)
			{
				$errorInformant = new ErrorInformant($answer['message'], $answer['code'], $answer['more_info'], $httpStatus);
				$result->addError($errorInformant->getError());
				// if (isset($answer['message']) && isset($answer['code']))
				// {
				// 	$result->addError(new Error($answer['message'], $answer['code']));
				// }
				// else
				// {
				// 	$result->addError(new Error('Service error (HTTP Status '.$httpStatus.')'));
				// }
			}
		}
		$result->setHttpResponse(new MessageService\DTO\Response([
			'statusCode' => $httpClient->getStatus(),
			'headers' => $httpClient->getHeaders()->toArray(),
			'body' => $httpClient->getResult(),
			'error' => Sender\Util::getHttpClientErrorString($httpClient)
		]));

		if ($result->isSuccess())
		{
			$result->setData($answer);
		}

		return $result;
	}

	private function loadFromList()
	{
		$sid = $this->getOption('account_sid');
		$result = $this->callExternalMethod(
			HttpClient::HTTP_GET,
			'Accounts/'.$sid.'/IncomingPhoneNumbers'
		);

		if ($result->isSuccess())
		{
			$from = array();
			$resultData = $result->getData();
			if (isset($resultData['incoming_phone_numbers']) && is_array($resultData['incoming_phone_numbers']))
			{
				foreach ($resultData['incoming_phone_numbers'] as $phoneNumber)
				{
					if ($phoneNumber['capabilities']['sms'] === true)
					{
						$from[] = array(
							'id' => $phoneNumber['phone_number'],
							'name' => $phoneNumber['friendly_name']
						);
					}
				}
			}

			$this->setOption('from_list', $from);
		}
	}

	private function checkSupportErrorAlphanumericNumber(ErrorCollection $collection): bool
	{
		if ($collection->getErrorByCode(self::NOT_SUPPORT_ALPHANUMERIC_NUMBER_STATUS_CODE))
		{
			return true;
		}

		return false;
	}

	/**
	 * @param string $sid
	 * @param array $messageFields
	 * @return Result
	 */
	private function sendMessageByAlphanumericNumber(string $sid, array $messageFields): Sender\Result\HttpRequestResult
	{
		$params = [
			'To' => $messageFields['MESSAGE_TO'],
			'Body' => $this->prepareMessageBodyForSend($messageFields['MESSAGE_BODY']),
			'From' => $messageFields['MESSAGE_FROM_ALPHANUMERIC'],
			'StatusCallback' => $this->getCallbackUrl()
		];

		return $this->callExternalMethod(
			HttpClient::HTTP_POST,
			'Accounts/'.$sid.'/Messages/',
			$params
		);
	}

	/**
	 * @param string $sid
	 * @param array $messageFields
	 * @return Result
	 */
	private function sendMessageByNumber(string $sid, array $messageFields): Sender\Result\HttpRequestResult
	{
		$params = [
			'To' => $messageFields['MESSAGE_TO'],
			'Body' => $this->prepareMessageBodyForSend($messageFields['MESSAGE_BODY']),
			'From' => $messageFields['MESSAGE_FROM'],
			'StatusCallback' => $this->getCallbackUrl()
		];

		if (!$params['From'])
		{
			$params['From'] = $this->getDefaultFrom();
		}
		if (is_string($params['From']) && mb_strlen($params['From']) === 34) //unique id of the Messaging Service
		{
			$params['MessagingServiceSid'] = $params['From'];
			unset($params['From']);
		}

		return $this->callExternalMethod(
			HttpClient::HTTP_POST,
			'Accounts/'.$sid.'/Messages/',
			$params
		);
	}

	/**
	 * @param array $messageFields
	 * @return EventResult[]
	 */
	public static function fireEventBeforeMessageSend(array $messageFields): array
	{
		$event = new Event('messageservice', self::ON_BEFORE_TWILIO_MESSAGE_SEND, $messageFields);
		$event->send();

		return $event->getResults();
	}

	private function getRequestUrl(string $apiMethod): string
	{
		$url = Option::get(
			'messageservice',
			'twilio_api_uri_tpl',
			'https://api.twilio.com/2010-04-01/%apiMethod%.json'
		);

		return str_replace('%apiMethod%', $apiMethod, $url);
	}
}