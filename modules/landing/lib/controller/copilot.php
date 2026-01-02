<?php

namespace Bitrix\Landing\Controller;

use Bitrix\Main\Engine;
use Bitrix\Main\Engine\Controller;
use Bitrix\Landing;
use Bitrix\Landing\Copilot\Generation;
use Bitrix\Landing\Copilot\Data;
use Bitrix\Landing\Copilot\Connector;
use Bitrix\Landing\Metrika;
use Bitrix\Main\Error;

class Copilot extends Controller
{
	public function getDefaultPreFilters(): array
	{
		return [
			new Engine\ActionFilter\Authentication(),
			new ActionFilter\Extranet(),
		];
	}

	/**
	 * Save relations between chat, site and user
	 * @param int $siteId
	 * @param int $chatId
	 * @return bool
	 */
	public function addChatToSiteAction(int $siteId, int $chatId): bool
	{
		return (new Landing\Copilot\Connector\Chat\Chat())->setChatForSite($siteId, $chatId);
	}

	/**
	 * Try to find and execute AI generation
	 * @param int $generationId
	 * @return bool
	 */
	public static function executeGenerationAction(int $generationId): bool
	{
		if ($generationId <= 0)
		{
			return false;
		}

		$generation = new Generation();
		if (!$generation->initById($generationId))
		{
			return false;
		}

		return $generation->execute();
	}

	public static function checkBlockGeneratableAction(int $blockId, ?int $chatId = null): bool
	{
		$isGeneratable = Data\Block\Operator::isBlockAvailableForScenarioChangeBlock($blockId);

		$metrika = new Metrika\Metrika(
			Metrika\Categories::BlockEdition,
			Metrika\Events::select,
			Metrika\Tools::ai
		);
		$metrika
			->setSection(Metrika\Sections::siteEditor)
			->setParam(4, 'block', (new Landing\Block($blockId))->getCode())
		;
		if (!$isGeneratable)
		{
			$metrika->setStatus(Metrika\Statuses::UnsupportedBlock);
		}
		$metrika->send();

		if (!$chatId || $chatId <= 0)
		{
			return $isGeneratable;
		}

		/**
		 * @var $chatBot Landing\Copilot\Connector\Chat\ChangeBlockChatBot
		 */
		$chatBot = Landing\Copilot\Connector\Chat\Chat::getChangeBlockChatBot();
		if ($chatBot)
		{
			$message = new Landing\Copilot\Connector\Chat\ChatBotMessageDto($chatId);
			$isGeneratable
				? $chatBot->sendSelectBlockSuccessMessage($message)
				: $chatBot->sendSelectBlockWrongMessage($message);
		}

		return $isGeneratable;
	}

	public function sendBlockGenerationNeedSelectMessageAction(int $siteId)
	{
		$chatId = (new Landing\Copilot\Connector\Chat\Chat())->getChatForSite($siteId);
		if (!$chatId)
		{
			return;
		}

		$chatBot = Landing\Copilot\Connector\Chat\Chat::getChangeBlockChatBot();
		if ($chatBot)
		{
			$message = new Landing\Copilot\Connector\Chat\ChatBotMessageDto($chatId);
			$chatBot->sendNeedSelectBlockMessage($message);
		}
	}

	public function sendBlockGenerationWrongSelectMessageAction(int $siteId)
	{
		$chatId = (new Landing\Copilot\Connector\Chat\Chat())->getChatForSite($siteId);
		if (!$chatId)
		{
			return;
		}

		$chatBot = Landing\Copilot\Connector\Chat\Chat::getChangeBlockChatBot();
		if ($chatBot)
		{
			$message = new Landing\Copilot\Connector\Chat\ChatBotMessageDto($chatId);
			$chatBot->sendSelectBlockWrongMessage($message);
		}
	}

	public function executeBlockGenerationAction(int $siteId, int $blockId, string $wishes): ?int
	{
		if ($siteId <= 0 || $blockId <= 0)
		{
			$this->addError(new Error('Incorrect block or chat ID'));

			return null;
		}

		if (!Data\Block\Operator::isBlockAvailableForScenarioChangeBlock($blockId))
		{
			$this->addError(new Error('Block is unavailable to generate'));

			return null;
		}

		$siteData = new Data\Site();
		$siteData->initByBlockIds([$blockId]);
		$chat = new Connector\Chat\Chat();
		$generation = (new Generation())
			->setScenario(new Generation\Scenario\ChangeBlock())
			->setSiteData($siteData)
			->setWishes((new Data\Wishes())->setWishes([$wishes]))
			->setChatId($chat->getChatForSite($siteId) ?? 0)
			->setMetrikaFields((new Metrika\FieldsDto(
				params: [
					[
						4,
						'block',
						(new Landing\Block($blockId))->getCode(),
					],
				]
			)))
		;

		if ($generation->execute())
		{
			return $generation->getId();
		}

		return null;
	}
}