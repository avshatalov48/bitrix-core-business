<?php
declare(strict_types=1);

namespace Bitrix\Landing\Copilot\Generation\Scenario;

use Bitrix\Landing\Copilot\Connector;
use Bitrix\Landing\Copilot\Connector\Chat\ICopilotChatBot;
use Bitrix\Landing\Copilot\Generation;
use Bitrix\Landing\Copilot\Generation\Step;
use Bitrix\Landing\Metrika;
use Bitrix\Landing\Rights;

class CreateSite extends BaseScenario
{
	/**
	 * @inheritdoc
	 */
	public function getMap(): array
	{
		return [
			10 => new Step\TaskInitSite(),
			20 => new Step\RequestSiteData(),
			30 => new Step\RequestPreviewImage(),
			40 => new Step\RequestSiteContent(),
			50 => new Step\TaskCreateSite(),
			60 => new Step\TaskCreateBlocks(),
			70 => new Step\RequestImages(),
			1000 => new Step\Finish(),
		];
	}

	/**
	 * @inheritdoc
	 */
	public function getChatbot(): ?ICopilotChatBot
	{
		return Connector\Chat\Chat::getCreateSiteChatBot();
	}

	/**
	 * Returns the number of the scenario step at which to check request limits.
	 *
	 * @return int
	 */
	public function getQuotaCalculateStep(): int
	{
		return 20;
	}

	public function getAnalyticCategory(): Metrika\Categories
	{
		return Metrika\Categories::SiteGeneration;
	}

	/**
	 * @inheritdoc
	 */
	public function onFinish(Generation $generation): void
	{
		Rights::setGlobalOff();
		$metrikaParams = new Metrika\FieldsDto(
			type: Metrika\Types::ai,
		);
		$generation->getSiteData()->getLandingInstance()?->publication(null, $metrikaParams);
		Rights::setGlobalOn();
	}
}