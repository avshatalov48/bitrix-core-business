<?php
declare(strict_types=1);

namespace Bitrix\Landing\Copilot\Generation\Step;

use Bitrix\Landing\Copilot\Data\Site;
use Bitrix\Landing\Copilot\Generation\Type\RequestQuotaDto;

abstract class TaskStep extends BaseStep
{
	/**
	 * @inheritdoc
	 */
	protected function initialize(): void
	{
	}

	/**
	 * @inheritdoc
	 */
	public function isStarted(): bool
	{
		return true;
	}

	/**
	 * @inheritdoc
	 */
	public function isFinished(): bool
	{
		return true;
	}

	/**
	 * @inheritdoc
	 */
	public static function getRequestQuota(Site $siteData): ?RequestQuotaDto
	{
		return null;
	}
}