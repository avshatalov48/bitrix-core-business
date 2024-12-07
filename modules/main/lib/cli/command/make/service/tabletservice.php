<?php

namespace Bitrix\Main\Cli\Command\Make\Service;

use Bitrix\Main\Application;
use Bitrix\Main\Cli\Command\Make\Service\Tablet\GenerateDto;
use Bitrix\Main\Cli\Command\Make\Templates\Tablet\FieldTemplate;
use Bitrix\Main\Cli\Command\Make\Templates\TabletTemplate;
use Bitrix\Main\Cli\Helper\PathGenerator;
use Bitrix\Main\Cli\Helper\NamespaceGenerator;
use Bitrix\Main\Cli\Helper\Renderer;
use Bitrix\Main\Loader;
use Bitrix\Perfmon\BaseDatabase;
use InvalidArgumentException;

final class TabletService
{
	private Renderer $renderer;
	private PathGenerator $PathGenerator;
	private NamespaceGenerator $NamespaceGenerator;
	private string $defaultRootFolder;

	public function __construct(string $defaultRootFolder = null)
	{
		Loader::requireModule('perfmon');

		$this->renderer = new Renderer();
		$this->NamespaceGenerator = new NamespaceGenerator();
		$this->defaultRootFolder = $defaultRootFolder ?? (string)$_SERVER['DOCUMENT_ROOT'];
	}

	public function generateContent(GenerateDto $dto): string
	{
		$namespace = $this->generateNamespace($dto);
		$className = $this->generateClassName($dto->tableName);
		$fileTemplate = new TabletTemplate(
			$dto->tableName,
			$className,
			$namespace,
			$this->getFieldsTempaltes($dto->tableName),
		);

		return $fileTemplate->getContent();
	}

	public function generateFile(GenerateDto $dto): void
	{
		$namespace = $this->generateNamespace($dto);
		$className = $this->generateClassName($dto->tableName);
		$fileTemplate = new TabletTemplate(
			$dto->tableName,
			$className,
			$namespace,
			$this->getFieldsTempaltes($dto->tableName),
		);

		$this->PathGenerator = new PathGenerator(
			$dto->psr4,
			$dto->rootFolder ?: $this->defaultRootFolder,
		);
		$filePath = $this->PathGenerator->generatePathToClass($namespace, $className);

		$this->renderer->renderToFile($filePath, $fileTemplate);
	}

	#region internal

	private function generateNamespace(GenerateDto $dto): string
	{
		$namespace = $dto->namespace;
		if (empty($namespace))
		{
			$moduleId = $dto->moduleId;
			if (empty($moduleId))
			{
				throw new InvalidArgumentException('If namespace option is not set, module argument MUST BE set!');
			}

			$namespace = $this->NamespaceGenerator->generateNamespaceForModule($moduleId, 'Tablet');
		}

		return $namespace;
	}

	private function generateClassName(string $name): string
	{
		$name = preg_replace('/Table$/i', '', $name);
		if (empty($name))
		{
			throw new InvalidArgumentException('Invalid table name');
		}

		$parts = explode('_', $name);
		$parts = array_map(
			static fn($i) => ucfirst($i),
			$parts,
		);

		return join('', $parts) . 'Table';
	}

	/**
	 * @param string $tableName
	 * @param string|null $connectionName
	 *
	 * @return FieldTemplate[]
	 */
	private function getFieldsTempaltes(string $tableName, ?string $connectionName = null): array
	{
		$connection = Application::getConnection($connectionName ?? 'default');
		$builder = BaseDatabase::createFromConnection($connection);

		$resultTemplates = [];

		[,$columns] = $builder->getTableFields($tableName);
		$uniqueIndexes = $builder->getUniqueIndexes($tableName);
		foreach ($columns as $columnName => $columnInfo)
		{
			$resultTemplates[$columnName] = $this->createFieldTemplate($columnName, $columnInfo, $uniqueIndexes);
		}


		return $resultTemplates;
	}

	private function createFieldTemplate(string $columnName, array $columnInfo, array $uniqueIndexes): FieldTemplate
	{
		$typeOrm = $columnInfo['orm_type'];
		$length = $columnInfo['length'];
		$nullable = $columnInfo['nullable'];
		$default = $columnInfo['default'];
		$increment = $columnInfo['increment'];

		// process size
		$size = null;
		if ($typeOrm === 'integer')
		{
			if (str_starts_with($columnInfo['type~'], 'tinyint'))
			{
				$size = 1;
			}
			elseif (str_starts_with($columnInfo['type~'], 'smallint'))
			{
				$size = 2;
			}
			elseif (str_starts_with($columnInfo['type~'], 'mediumint'))
			{
				$size = 3;
			}
			elseif (str_starts_with($columnInfo['type~'], 'bigint'))
			{
				$size = 8;
			}
		}

		// process indexes
		$primary = false;
		$unique = false;

		// TODO: scan unique and fulltext indexes
		foreach ($uniqueIndexes as $indexName => $columnNames)
		{
			if (in_array($columnName, $columnNames))
			{
				$primary = true;
				break;
			}
		}

		return new FieldTemplate(
			$columnName,
			$typeOrm,
			$primary,
			$unique,
			$increment,
			$nullable,
			$default,
			$size,
			$length,
		);
	}

	#endregion internal
}
