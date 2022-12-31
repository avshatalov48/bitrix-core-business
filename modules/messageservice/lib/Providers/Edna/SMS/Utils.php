<?php

namespace Bitrix\MessageService\Providers\Edna\SMS;

use Bitrix\Main\Result;
use Bitrix\MessageService\Providers;
use Bitrix\MessageService\Providers\Edna\EdnaUtils;
use Bitrix\MessageService\Providers\OptionManager;

class Utils extends EdnaUtils
{
	protected string $providerId;
	protected OptionManager $optionManager;

	public function __construct(string $providerId, OptionManager $optionManager)
	{
		$this->providerId = $providerId;

		parent::__construct($optionManager);
	}

	public function getSentTemplateMessage(string $from, string $to): string
	{
		return '';
	}

	public function initializeDefaultExternalSender(): Providers\ExternalSender
	{
		return new ExternalSender(
			$this->optionManager->getOption(Providers\Constants\InternalOption::API_KEY),
			Constants::API_ENDPOINT,
			$this->optionManager->getSocketTimeout(),
			$this->optionManager->getStreamTimeout()
		);
	}

	public function getMessageTemplates(string $subject = ''): Result
	{
		return new Result();
	}
}