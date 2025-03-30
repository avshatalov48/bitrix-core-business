<?php

namespace Bitrix\Main\Cli\Command\Make\Service\Component;

use InvalidArgumentException;

final class GenerateDto
{
	public function __construct(
		public readonly string $name,
		public readonly string $namespace,
		public readonly string $module,
		public readonly bool $noModule = false,
		public readonly bool $local = false,
		public readonly ?string $root = null,
	)
	{
		if (empty($name))
		{
			throw new InvalidArgumentException('Empty component name');
		}
	}
}
