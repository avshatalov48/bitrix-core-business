<?php

namespace Bitrix\Socialnetwork\Space\List\RecentActivity\Event\Processor;

final class NullProcessor extends AbstractProcessor
{
	public function isAvailable(): bool
	{
		return false;
	}

	protected function getTypeId(): string
	{
		return 'null';
	}

	public function process(): void
	{}
}