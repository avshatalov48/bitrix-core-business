<?php

namespace Bitrix\Main\Cli\Command\Make\Service\Controller;

final class GenerateDto
{
	public function __construct(
		public readonly string $name,
		public readonly ?string $moduleId = null,
		public readonly ?string $namespace = null,
		public readonly ?string $rootFolder = null,
		public readonly bool $psr4 = true,
		public readonly array $actions = [],
		public readonly ?string $alias = null,
	)
	{}
}
