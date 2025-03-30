<?php

namespace Bitrix\Main\Cli\Command\Make\Service;

use Bitrix\Main\Cli\Command\Make\Service\Component\GenerateDto;
use Bitrix\Main\Cli\Command\Make\Templates\Component\ClassTemplate;
use Bitrix\Main\Cli\Command\Make\Templates\Component\LangTemplate;
use Bitrix\Main\Cli\Command\Make\Templates\Component\TemplateTemplate;
use Bitrix\Main\Cli\Helper\Renderer;
use Bitrix\Main\Engine\Response\Converter;

final class ComponentService
{
	private Renderer $renderer;

	public function __construct()
	{
		$this->renderer = new Renderer();
	}

	public function generateClassContent(GenerateDto $dto): string
	{
		return $this->createClassTemplate($dto)->getContent();
	}

	public function generateTemplateContent(GenerateDto $dto): string
	{
		return $this->createTemplateTemplate($dto)->getContent();
	}

	public function generateLangContent(GenerateDto $dto): string
	{
		return $this->createLangTemplate($dto)->getContent();
	}

	public function generateClassFile(GenerateDto $dto): void
	{
		$fileTemplate = $this->createClassTemplate($dto);

		$filePath = $this->getPathToComponent($dto) . '/class.php';

		$this->renderer->renderToFile($filePath, $fileTemplate);
	}

	public function generateTemplateFile(GenerateDto $dto): void
	{
		$fileTemplate = $this->createTemplateTemplate($dto);

		$filePath = $this->getPathToComponent($dto) . '/templates/.default/template.php';

		$this->renderer->renderToFile($filePath, $fileTemplate);
	}

	public function generateLangFile(GenerateDto $dto): void
	{
		$fileTemplate = $this->createLangTemplate($dto);

		$filePath = $this->getPathToComponent($dto) . '/templates/.default/lang/ru/template.php';

		$this->renderer->renderToFile($filePath, $fileTemplate);
	}

	public function getPathToComponent(GenerateDto $dto): string
	{
		$root = $dto->root ?? (string)$_SERVER['DOCUMENT_ROOT'];

		$bitrixFolder = ($dto->namespace !== 'bitrix' || $dto->local) ? 'local' : 'bitrix';
		$componentsFolder = $dto->noModule ? 'components' : "modules/$dto->module/install/components";

		return "$root/$bitrixFolder/$componentsFolder/$dto->namespace/$dto->name";
	}

	#region internal
	protected function createClassTemplate(GenerateDto $dto): ClassTemplate
	{
		$className = $this->generateClassName($dto->name);

		return new ClassTemplate(
			className: $className,
		);
	}

	protected function generateClassName(string $name): string
	{
		$converter = new Converter(Converter::TO_CAMEL);

		$name = preg_replace('/[.-]/', '_', $name);

		return $converter->process($name) . 'Component';
	}

	protected function createTemplateTemplate(GenerateDto $dto): TemplateTemplate
	{
		return new TemplateTemplate(
			componentTitlePhrase: $this->generateTitlePhrase($dto->name),
			containerId: $this->generateContainerId($dto->name),
			extensionName: $dto->name,
			extensionClass: $this->generateExtensionClass($dto->name),
		);
	}

	protected function createLangTemplate(GenerateDto $dto): LangTemplate
	{
		return new LangTemplate(
			componentTitlePhrase: $this->generateTitlePhrase($dto->name),
			componentTitle: $this->generateTitle($dto->name),
		);
	}

	protected function generateTitlePhrase(string $name): string
	{
		$converter = new Converter(Converter::TO_UPPER);

		$name = preg_replace('/[.-]/', '_', $name);

		return $converter->process($name) . '_TITLE';
	}

	protected function generateTitle(string $name): string
	{
		$converter = new Converter(Converter::TO_CAMEL);

		$name = preg_replace('/[_-]/', '.', $name);

		return trim(array_reduce(explode('.', $name), fn ($acc, $it) => "$acc " . $converter->process($it)));
	}

	protected function generateContainerId(string $name): string
	{
		return preg_replace('/[._]/', '-', $name);
	}

	protected function generateExtensionClass(string $name): string
	{
		$converter = new Converter(Converter::TO_CAMEL);

		$name = preg_replace('/-/', '_', $name);

		return 'BX' . array_reduce(explode('.', $name), fn ($acc, $it) => "$acc." . $converter->process($it));
	}
	#endregion internal
}
