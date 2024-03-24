<?php

namespace Bitrix\MessageService\Providers\Edna\SMS;

use Bitrix\Main\Result;
use Bitrix\MessageService\Providers;
use Bitrix\MessageService\Providers\Edna\EdnaUtils;


class Utils extends EdnaUtils
{
	public function getSentTemplateMessage(string $from, string $to): string
	{
		return '';
	}

	public function initializeDefaultExternalSender(): Providers\ExternalSender
	{
		return new ExternalSender(
			$this->optionManager->getOption(Providers\Constants\InternalOption::API_KEY),
			RegionHelper::getApiEndPoint(),
			$this->optionManager->getSocketTimeout(),
			$this->optionManager->getStreamTimeout()
		);
	}

	public function getMessageTemplates(string $subject = ''): Result
	{
		return new Result();
	}
}