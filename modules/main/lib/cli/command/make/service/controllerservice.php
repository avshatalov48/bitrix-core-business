<?php

namespace Bitrix\Main\Cli\Command\Make\Service;

use Bitrix\Main\Cli\Command\Make\Service\Controller\GenerateDto;
use Bitrix\Main\Cli\Helper\PathGenerator;
use Bitrix\Main\Cli\Helper\NamespaceGenerator;
use Bitrix\Main\Cli\Helper\Renderer;
use Bitrix\Main\Cli\Command\Make\Templates\ControllerTemplate;
use Bitrix\Main\Cli\Helper\Renderer\Template;
use InvalidArgumentException;

final class ControllerService
{
	private Renderer $renderer;
	private NamespaceGenerator $namespaceGenerator;
	private string $defaultRootFolder;

	public function __construct()
	{
		$this->renderer = new Renderer();
		$this->namespaceGenerator = new NamespaceGenerator();
		$this->defaultRootFolder = (string)$_SERVER['DOCUMENT_ROOT'];
	}

	public function generateContent(GenerateDto $dto): string
	{
		return $this->getTemplate($dto)->getContent();
	}

	public function generateFile(GenerateDto $dto): void
	{
		$fileTemplate = $this->getTemplate($dto);

		$pathGenerator = new PathGenerator(
			$dto->psr4,
			$dto->rootFolder ?: $this->defaultRootFolder,
		);

		$namespace = $this->generateNamespace($dto);
		$className = $this->normalizeControllerName($dto->name);

		$filePath = $pathGenerator->generatePathToClass($namespace, $className);

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

			$namespace = $this->namespaceGenerator->generateNamespaceForModule($moduleId, 'Controller');
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

		return ucfirst($name);
	}

	private function getTemplate(GenerateDto $dto): Template
	{
		$namespace = $this->generateNamespace($dto);
		$className = $this->normalizeControllerName($dto->name);

		return new ControllerTemplate($className, $namespace, $dto->moduleId, $dto->alias, $dto->actions);
	}

	#endregion internal
}
