<?php

namespace Bitrix\Im\V2\Entity\User;

use Bitrix\Im\Integration\Socialnetwork\Extranet;
use Bitrix\Im\V2\Chat\ChatError;
use Bitrix\Im\V2\Entity\User\Data\BotData;
use Bitrix\Im\V2\Result;
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

	protected function checkAccessInternal(User $otherUser): Result
	{
		$result = new Result();

		if (!static::$moduleManager::isModuleInstalled('intranet'))
		{
			if (!$this->hasAccessBySocialNetwork($otherUser->getId()))
			{
				$result->addError(new ChatError(ChatError::ACCESS_DENIED));
			}

			return $result;
		}

		if (Loader::includeModule('imbot') && $this->getBotData()->getCode() === CopilotChatBot::BOT_CODE)
		{
			return $result->addError(new ChatError(ChatError::ACCESS_DENIED));
		}

		global $USER;
		if ($otherUser->isExtranet())
		{
			if ($otherUser->getId() === $USER->GetID())
			{
				if ($USER->IsAdmin())
				{
					return $result;
				}

				if (static::$loader::includeModule('bitrix24'))
				{
					if (\CBitrix24::IsPortalAdmin($otherUser->getId()) || \Bitrix\Bitrix24\Integrator::isIntegrator($otherUser->getId()))
					{
						return $result;
					}
				}
			}

			$inGroup = Extranet::isUserInGroup(
				$this->getId(),
				$otherUser->getId(),
				false
			);

			if ($inGroup)
			{
				return $result;
			}


			return $result->addError(new ChatError(ChatError::ACCESS_DENIED));
		}

		return $result;
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

	public function getType(): UserType
	{
		return UserType::BOT;
	}
}
