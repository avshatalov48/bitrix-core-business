<?php

namespace Bitrix\Main\Cli\Command\Make\Templates;

use Bitrix\Main\Cli\Command\Make\Templates\Tablet\FieldTemplate;
use Bitrix\Main\Cli\Helper\Renderer\Template;
use Bitrix\Main\ORM\Data\DataManager;

final class TabletTemplate implements Template
{
	private string $usedClassesCode;
	private string $fieldsCode;

	/**
	 * @param string $tableName
	 * @param string $className
	 * @param string $namespace
	 * @param FieldTemplate[] $fieldsTemplates
	 */
	public function __construct(
		private readonly string $tableName,
		private readonly string $className,
		private readonly string $namespace,
		private readonly array $fieldsTemplates,
	)
	{
		$this->processFields();
	}

	public function getContent(): string
	{
		return <<<PHP
<?php

namespace {$this->namespace};

{$this->usedClassesCode}

final class {$this->className} extends DataManager
{
	public static function getTableName()
	{
		return '{$this->tableName}';
	}

	public static function getMap()
	{
		return [\n{$this->fieldsCode}\t\t];
	}
}
PHP;
	}

	private function processFields(): void
	{
		$usedClasses = [
			DataManager::class,
		];
		$fieldsCodes = [];

		foreach ($this->fieldsTemplates as $fieldTemplate)
		{
			$fieldsCodes[] = $fieldTemplate->getContent();
			array_push($usedClasses, ... $fieldTemplate->getUsedClasses());
		}

		$usedClasses = array_unique($usedClasses);
		sort($usedClasses);

		$this->usedClassesCode = join(
			"\n",
			array_map(
				static fn($line) => "use {$line};",
				$usedClasses,
			),
		);

		$this->fieldsCode = join('', $fieldsCodes);
	}
}
