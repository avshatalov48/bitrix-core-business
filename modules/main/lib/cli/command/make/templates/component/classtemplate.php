<?php

namespace Bitrix\Main\Cli\Command\Make\Templates\Component;

use Bitrix\Main\Cli\Helper\Renderer\Template;

final class ClassTemplate implements Template
{
	public function __construct(
		private readonly string $className,
	)
	{}

	public function getContent(): string
	{
		return <<<PHP
<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

class {$this->className} extends CBitrixComponent
{
	public function onPrepareComponentParams(\$arParams): array
	{
		// TODO: prepare or format component params
		\$arParams['USERNAME'] ??= 'default value';
		
		return \$arParams;
	}
	
	public function executeComponent(): void
	{
		\$this->prepareResult();
		
		\$this->includeComponentTemplate();
	}
	
	private function prepareResult(): void
	{
		// TODO: prepare result for template.php
		\$this->arResult['FRUITS'] = [
			'apple',
			'banana',
			'ananas',
		];
	}
}

PHP;
	}
}
