<?php

declare(strict_types=1);

namespace Bitrix\Rest\Internal\Service;

use Bitrix\Main\Config\Option;

class MemoryLimitChecker
{
	public function __construct(
		private readonly int $memoryLimit,
		private readonly float $memoryRate,
	) {
	}

	public static function createByDefault(): self
	{
		return new self(
			self::memoryLimitFromPhpIniInMb(),
			(float)Option::get('rest', 'memory_limit_rate', 0.9),
		);
	}

	public function executeOnMemoryExcess(callable $callback): void
	{
		if ($this->memoryLimit <= 0 || $this->memoryRate <= 0 || $this->memoryRate > 1)
		{
			return;
		}
		
		$memoryUsageMB = memory_get_usage() / (1024 * 1024);
		$maxAllowedSizeMB = $this->memoryLimit * $this->memoryRate;

		if ($memoryUsageMB > $maxAllowedSizeMB)
		{
			$callback($memoryUsageMB, $maxAllowedSizeMB);
		}
	}

	private static function memoryLimitFromPhpIniInMb(): int
	{
		$memoryLimit = ini_get('memory_limit');
		if ($memoryLimit === false)
		{
			return 0;
		}

		$unit = strtoupper(substr($memoryLimit, -1));
		$value = (int)$memoryLimit;

		return match ($unit)
		{
			'G' => $value * 1024,
			'M' => $value,
			'K' => (int)ceil($value / 1024),
			default => (int)ceil((int)$memoryLimit / (1024 * 1024)),
		};
	}
}
