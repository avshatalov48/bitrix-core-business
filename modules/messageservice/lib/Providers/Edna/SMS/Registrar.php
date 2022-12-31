<?php

namespace Bitrix\MessageService\Providers\Edna\SMS;

use Bitrix\MessageService\Providers\Constants\InternalOption;
use Bitrix\MessageService\Providers\Edna\Constants\CallbackType;
use Bitrix\MessageService\Providers\Edna\Constants\ChannelType;
use Bitrix\MessageService\Providers\Edna\EdnaRu;
use Bitrix\MessageService\Providers\OptionManager;

class Registrar extends \Bitrix\MessageService\Providers\Edna\Registrar
{
	protected string $channelType = ChannelType::SMS;

	public function __construct(string $providerId, OptionManager $optionManager, EdnaRu $utils)
	{
		parent::__construct($providerId, $optionManager, $utils);
		if ($this->isRegistered() && !$this->isMigratedToStandartSettingNames())
		{
			$this->migrateToStandartSettingNames();
		}
	}

	protected function getCallbackTypeList(): array
	{
		return [
			CallbackType::MESSAGE_STATUS,
		];
	}

	private function isMigratedToStandartSettingNames(): bool
	{
		return $this->optionManager->getOption(InternalOption::MIGRATED_TO_STANDART_SETTING_NAMES, 'N') === 'Y';
	}

	private function migrateToStandartSettingNames(): void
	{
		$options = $this->optionManager->getOptions();
		if (isset($options['apiKey']))
		{
			$migratedOptions = [
				InternalOption::API_KEY => $options['apiKey'],
				InternalOption::MIGRATED_TO_STANDART_SETTING_NAMES => 'Y'
			];

			$this->optionManager->setOptions($migratedOptions);
		}
	}
}