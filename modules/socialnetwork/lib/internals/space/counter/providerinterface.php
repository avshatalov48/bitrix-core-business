<?php

namespace Bitrix\Socialnetwork\Internals\Space\Counter;

interface ProviderInterface
{
	public function getTotal(int $spaceId = 0): int;
	public function getValue(int $spaceId = 0, array $metrics = []): int;
	public function getAvailableMetrics(): array;
}