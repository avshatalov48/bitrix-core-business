<?php

namespace Bitrix\Socialnetwork\Internals\Space\Counter;

class InMemoryProvider implements ProviderInterface
{
	public const METRICS_PLAIN = 'InMemoryProviderPlainMetrics';

	public function getTotal(int $spaceId = 0): int
	{
		return 10;
	}

	public function getValue(int $spaceId = 0, array $metrics = []): int
	{
		return 1;
	}

	public function getAvailableMetrics(): array
	{
		return [
			self::METRICS_PLAIN,
		];
	}
}