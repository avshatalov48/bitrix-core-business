<?php

namespace Bitrix\Socialnetwork\Space\List\RecentActivity\Event\PreProcessor;

final class NullPreProcessor extends AbstractPreProcessor
{
	public function isAvailable(): bool
	{
		return false;
	}

	public function process(): void
	{}

	protected function getTypeId(): string
	{
		return 'null';
	}
}