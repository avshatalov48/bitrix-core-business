<?php

namespace Bitrix\Rest\V3\Schema;

class ControllerData
{
	private \ReflectionClass $controller;
	private ?\ReflectionClass $dto = null;

	public function __construct(
		private readonly string $moduleId,
		private readonly string $controllerClass,
		private readonly ?string $dtoClass = null,
		private readonly ?string $namespace = null,
	) {
		$this->controller = new \ReflectionClass($this->controllerClass);
		if ($this->dtoClass)
		{
			$this->dto = new \ReflectionClass($this->dtoClass);
		}
	}

	public function getUri(): string
	{
		$namespace = strtolower(trim($this->namespace, '\\'));

		$controllerName = strtolower($this->controller->getName());
		$controllerUri = str_replace('\\', '.', trim(str_replace($namespace,'', $controllerName), '\\'));

		return $this->moduleId . '.' . $controllerUri;
	}

	public function getMethodUri(string $method): string
	{
		return $this->getUri() . '.' . strtolower($method);
	}

	public function getModuleId(): string
	{
		return $this->moduleId;
	}

	public function getController(): \ReflectionClass
	{
		return $this->controller;
	}

	public function getDto(): ?\ReflectionClass
	{
		return $this->dto;
	}

	public function getNamespace(): ?string
	{
		return $this->namespace;
	}
}
