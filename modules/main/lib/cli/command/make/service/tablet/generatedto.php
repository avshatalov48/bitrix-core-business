<?php

namespace Bitrix\Main\Cli\Command\Make\Service\Tablet;

final class GenerateDto
{
	public function __construct(
		public readonly string $tableName,
		public readonly ?string $namespace = null,
		public readonly ?string $moduleId = null,
		public readonly ?string $rootFolder = null,
		public readonly bool $psr4 = true,
	)
	{}
}
