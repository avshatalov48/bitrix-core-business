<?php

namespace Bitrix\MessageService\Providers\Edna\SMS\Old;

use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;
use Bitrix\Main\Text\Emoji;
use Bitrix\MessageService\Providers\Constants\InternalOption;
use Bitrix\MessageService\Providers\Edna\SMS\ExternalSender;
use Bitrix\MessageService\Providers\Edna\SMS\StatusResolver;
use Bitrix\MessageService\Sender\Result\MessageStatus;
use Bitrix\MessageService\Sender\Result\SendMessage;

class Sender extends \Bitrix\MessageService\Providers\Edna\SMS\Sender
{
	public function sendMessage(array $messageFields): SendMessage
	{
		if (!$this->supportChecker->canUse())
		{
			$result = new SendMessage();
			$result->addError(new Error('Cant use'));
			return $result;
		}


		$validationResult = $this->validatePhoneNumber($messageFields['MESSAGE_TO']);

		if (!$validationResult->isSuccess())
		{
			$result = new SendMessage();
			$result->addErrors($validationResult->getErrors());

			return $result;

		}
		$phoneNumber = $validationResult->getData()['validNumber'];

		$params = [
			'id' => uniqid('', true),
			'subject' => $messageFields['MESSAGE_FROM'],
			'address' => $phoneNumber,
			'priority' => 'high',
			'contentType' => 'text',
			'content' => Emoji::decode($messageFields['MESSAGE_BODY']),
		];

		$externalSender = new ExternalSender(
			$this->optionManager->getOption(InternalOption::API_KEY, ''),
			Constants::API_ENDPOINT
		);
		$apiResult = $externalSender->callExternalMethod('smsOutMessage', $params);

		$result = new SendMessage();
		$result->setServiceRequest($apiResult->getHttpRequest());
		$result->setServiceResponse($apiResult->getHttpResponse());

		if (!$apiResult->isSuccess())
		{
			$result->addErrors($apiResult->getErrors());

			return $result;
		}

		$apiData = $apiResult->getData();

		$result->setExternalId($apiData['id']);
		$result->setAccepted();

		return $result;
	}

	protected function validatePhoneNumber(string $number): Result
	{
		$result = new Result();

		$number = str_replace('+', '', $number);

		$externalSender = new ExternalSender(
			$this->optionManager->getOption(InternalOption::API_KEY, ''),
			Constants::API_ENDPOINT
		);
		$apiResult = $externalSender->callExternalMethod("validatePhoneNumber/{$number}");
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

	public function getMessageStatus(array $messageFields): MessageStatus
	{
		$result = new MessageStatus();
		$result->setId($messageFields['ID']);
		$result->setExternalId($messageFields['ID']);

		if (!$this->supportChecker->canUse())
		{
			$result->addError(new Error(Loc::getMessage('MESSAGESERVICE_SENDER_SMS_SMSEDNARU_USE_ERROR')));
			return $result;
		}

		$externalSender = new ExternalSender($this->optionManager->getOption(InternalOption::API_KEY, ''), Constants::API_ENDPOINT);
		$apiResult = $externalSender->callExternalMethod("smsOutMessage/{$messageFields['ID']}");
		if (!$apiResult->isSuccess())
		{
			$result->addErrors($apiResult->getErrors());
		}
		else
		{
			$apiData = $apiResult->getData();

			$result->setStatusText($apiData['dlvStatus']);
			$result->setStatusCode((new StatusResolver())->resolveStatus($apiData['dlvStatus']));
		}

		return $result;
	}

}