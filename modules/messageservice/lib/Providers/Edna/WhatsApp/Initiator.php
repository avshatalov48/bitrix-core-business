<?php

namespace Bitrix\MessageService\Providers\Edna\WhatsApp;

use Bitrix\MessageService\Providers;

class Initiator extends Providers\Base\Initiator
{
	protected Providers\OptionManager $optionManager;

	public function __construct(Providers\OptionManager $optionManager)
	{
		$this->optionManager = $optionManager;
	}

	public function getFromList(): array
	{
		$fromList = [];
		foreach ($this->optionManager->getOption(Constants::SENDER_ID_OPTION, []) as $subject)
		{
			$fromList[] = [
				'id' => $subject,
				'name' => $subject,
			];
		}
		return $fromList;
	}

	public function isCorrectFrom($from): bool
	{
		$fromList = $this->getFromList();
		foreach ($fromList as $item)
		{
			if ((int)$from === $item['id'])
			{
				return true;
			}
		}
		return false;
	}
}