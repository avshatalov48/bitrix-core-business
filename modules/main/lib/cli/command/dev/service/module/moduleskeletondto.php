<?php

declare(strict_types=1);

namespace Bitrix\Main\Cli\Command\Dev\Service\Module;

class ModuleSkeletonDto
{
	public function __construct(
		public readonly string $module,
		public readonly string $directory,
	)
	{

	}
}