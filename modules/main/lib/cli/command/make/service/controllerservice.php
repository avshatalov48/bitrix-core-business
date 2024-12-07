<?php

namespace Bitrix\Main\Cli\Command\Make\Service;

use Bitrix\Main\Cli\Command\Make\Service\Controller\GenerateDto;
use Bitrix\Main\Cli\Helper\PathGenerator;
use Bitrix\Main\Cli\Helper\NamespaceGenerator;
use Bitrix\Main\Cli\Helper\Renderer;
use Bitrix\Main\Cli\Command\Make\Templates\ControllerTemplate;
use InvalidArgumentException;

final class ControllerService
{
	private Renderer $renderer;
	private PathGenerator $PathGenerator;
	private NamespaceGenerator $NamespaceGenerator;
	private string $defaultRootFolder;

	public function __construct()
	{
		$this->renderer = new Renderer();
		$this->NamespaceGenerator = new NamespaceGenerator();
		$this->defaultRootFolder = (string)$_SERVER['DOCUMENT_ROOT'];
	}

	public function generateContent(GenerateDto $dto): string
	{
		$namespace = $this->generateNamespace($dto);
		$className = $this->normalizeControllerName($dto->name);
		$fileTemplate = new ControllerTemplate($className, $namespace);

		return $fileTemplate->getContent();
	}

	public function generateFile(GenerateDto $dto): void
	{
		$namespace = $this->generateNamespace($dto);
		$className = $this->normalizeControllerName($dto->name);
		$fileTemplate = new ControllerTemplate($className, $namespace);

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

			$namespace = $this->NamespaceGenerator->generateNamespaceForModule($moduleId, 'Controller');
		}

		return $namespace;
	}

	private function normalizeControllerName(string $name): string
	{
		$name = preg_replace('/Controller$/i', '', $name);
		if (empty($name))
		{
			throw new InvalidArgumentException('Invalid controller name');
		}

		return ucfirst($name) . 'Controller';
	}

	#endregion internal
}
