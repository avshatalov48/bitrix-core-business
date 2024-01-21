<?php

namespace Bitrix\Im\V2\Entity\User;

use Bitrix\Im\V2\Entity\User\Data\BotData;
use Bitrix\Imbot\Bot\CopilotChatBot;
use Bitrix\Main\Loader;

class UserBot extends User
{
	private ?BotData $botData = null;

	protected function fillOnlineData(bool $withStatus = false): void
	{
		return;
	}

	public function isOnlineDataFilled(bool $withStatus): bool
	{
		return true;
	}

	protected function checkAccessWithoutCaching(User $otherUser): bool
	{
		if (!static::$moduleManager::isModuleInstalled('intranet'))
		{
			return $this->hasAccessBySocialNetwork($otherUser->getId());
		}

		if (Loader::includeModule('imbot') && $this->getBotData()->getCode() === CopilotChatBot::BOT_CODE)
		{
			return false;
		}

		global $USER;
		if ($otherUser->isExtranet())
		{
			if ($otherUser->getId() === $USER->GetID())
			{
				if ($USER->IsAdmin())
				{
					return true;
				}

				if (static::$loader::includeModule('bitrix24'))
				{
					if (\CBitrix24::IsPortalAdmin($otherUser->getId()) || \Bitrix\Bitrix24\Integrator::isIntegrator($otherUser->getId()))
					{
						return true;
					}
				}
			}

			$inGroup = \Bitrix\Im\Integration\Socialnetwork\Extranet::isUserInGroup($this->getId(), $otherUser->getId());
			if ($inGroup)
			{
				return true;
			}


			return false;
		}

		if ($this->isNetwork())
		{
			return true;
		}

		return true;
	}

	public function toRestFormat(array $option = []): array
	{
		$userData = parent::toRestFormat($option);

		if (isset($userData['botData']))
		{
			return $userData;
		}

		$botData = $this->getBotData()->toRestFormat();
		$userData['botData'] = empty($botData) ? null : $botData;

		return $userData;
	}

	public function getBotData(): BotData
	{
		if ($this->botData !== null)
		{
			return $this->botData;
		}

		return BotData::getInstance($this->getId());
	}
}