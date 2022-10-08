<?php

namespace Bitrix\Calendar\Sync\Factories;

use Bitrix\Calendar\Sync\Managers\PushManagerInterface;

interface PushFactoryInterface
{
	public function canSubscribeSection(): bool;

	public function canSubscribeConnection(): bool;

	public function getPushManager(): ?PushManagerInterface;
}