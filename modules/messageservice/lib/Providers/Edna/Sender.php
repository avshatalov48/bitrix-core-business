<?php

namespace Bitrix\MessageService\Providers\Edna;

use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\MessageService\Providers;
use Bitrix\MessageService\Sender\Result\MessageStatus;
use Bitrix\MessageService\Sender\Result\SendMessage;

abstract class Sender extends Providers\Base\Sender
{

	protected Providers\OptionManager $optionManager;
	protected Providers\SupportChecker $supportChecker;
	protected EdnaRu $utils;
	protected Providers\ExternalSender $externalSender;

	abstract protected function initializeDefaultExternalSender(): Providers\ExternalSender;
	abstract protected function getSendMessageParams(array $messageFields): Result;
	abstract protected function getSendMessageMethod(array $messageFields): string;
	abstract protected function isTemplateMessage(array $messageFields): bool;
	abstract protected function sendHSMtoChat(array $messageFields): Result;

	/**
	 * @param Providers\OptionManager $optionManager
	 * @param Providers\SupportChecker $supportChecker
	 */
	public function __construct(
		Providers\OptionManager $optionManager,
		Providers\SupportChecker $supportChecker,
		EdnaRu $utils
	)
	{
		$this->optionManager = $optionManager;
		$this->supportChecker = $supportChecker;
		$this->utils = $utils;
		$this->externalSender = $this->initializeDefaultExternalSender();
	}


	public function sendMessage(array $messageFields): SendMessage
	{
		if (!$this->supportChecker->canUse())
		{
			$result = new SendMessage();
			$result->addError(new Error('Service is unavailable'));

			return $result;
		}

		$paramsResult = $this->getSendMessageParams($messageFields);
		if (!$paramsResult->isSuccess())
		{
			$result = new SendMessage();
			$result->addErrors($paramsResult->getErrors());

			return $result;
		}

		$requestParams = $paramsResult->getData();
		$method = $this->getSendMessageMethod($messageFields);

		if ($this->isTemplateMessage($messageFields))
		{
			$this->sendHSMtoChat($messageFields);
		}

		$result = new SendMessage();

		$requestResult = $this->externalSender->callExternalMethod($method, $requestParams);
		if (!$requestResult->isSuccess())
		{
			$result->addErrors($requestResult->getErrors());

			return $result;
		}

		$apiData = $requestResult->getData();
		$result->setExternalId($apiData['requestId']);
		$result->setAccepted();

		return $result;
	}

	public function getMessageStatus(array $messageFields): MessageStatus
	{
		return new MessageStatus();
	}
}