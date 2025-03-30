<?php

declare(strict_types=1);

namespace Bitrix\Main\Cli\Command\Dev\Service\Locator;

final class LocatorCodesDto
{
	public function __construct(
		public readonly string $module,
		public readonly string $code,
	)
	{

	}
}