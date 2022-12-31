<?php

namespace Bitrix\MessageService\Providers\Edna\SMS\Old;

use Bitrix\Main\Result;
use Bitrix\MessageService\Providers\Constants\InternalOption;
use Bitrix\MessageService\Providers\Edna\SMS\ExternalSender;

class Registrar extends \Bitrix\MessageService\Providers\Edna\SMS\Registrar
{
	public function register(array $fields): Result
	{
		$this->optionManager->setOption(InternalOption::API_KEY, $fields[InternalOption::API_KEY]);
		$externalSender = new ExternalSender($fields[InternalOption::API_KEY], Constants::API_ENDPOINT);

		return $externalSender->callExternalMethod('smsSubject/');
	}

	/**
	 * @return array{apiKey: string, subject: array}
	 */
	public function getOwnerInfo(): array
	{
		$initiator = new Initiator($this->optionManager,$this, $this->utils);

		return [
			InternalOption::API_KEY => $this->optionManager->getOption(InternalOption::API_KEY),
			InternalOption::SENDER_ID => array_column($initiator->getFromList(), 'name'),
		];
	}

	public function getExternalManageUrl(): string
	{
		return 'https://sms.edna.ru/';
	}

	public function isRegistered(): bool
	{
		return $this->optionManager->getOption(InternalOption::API_KEY, '') !== '';
	}

}