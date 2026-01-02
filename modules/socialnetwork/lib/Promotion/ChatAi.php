<?php

namespace Bitrix\Socialnetwork\Promotion;

use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\Type\DateTime;
use Bitrix\Socialnetwork\Integration\Bitrix24\LeftMenuPreset;
use Bitrix\Socialnetwork\Integration\Bitrix24\Portal;

class ChatAi extends AbstractPromotion
{
	public function getPromotionType(): PromotionType
	{
		return PromotionType::CHAT_AI;
	}

	public function shouldShow(int $userId): bool
	{
		if (!Loader::includeModule('ai') || $this->isViewed($userId))
		{
			return false;
		}

		$preset = new LeftMenuPreset();

		if (!$preset->isCurrentPresetIsSocialAi())
		{
			return false;
		}

		$region = Application::getInstance()->getLicense()->getRegion();

		if ($region === 'cn')
		{
			return false;
		}

		$portalCreateDate = (new Portal())->getCreationDateTime();
		$suitablePortalCreationDate = $this->getMinimumSuitablePortalCreationDate();

		if ($portalCreateDate?->getTimestamp() <= $suitablePortalCreationDate->getTimestamp())
		{
			return false;
		}

		return true;
	}

	private function getMinimumSuitablePortalCreationDate(): DateTime
	{
		return new DateTime('2024-11-26', 'Y-m-d');
	}
}