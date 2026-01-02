<?php

namespace Bitrix\Rest\V3\Schema;

class MethodDescription
{
	public function __construct(
		private readonly string $method,
		private readonly ?string $controller,
		private readonly string $scope,
		private readonly string $module,
		private readonly ?string $class = null,
	)
	{
	}

	public function getMethod(): string
	{
		return $this->method;
	}

	public function getController(): ?string
	{
		return $this->controller;
	}

	public function getScope(): string
	{
		return $this->scope;
	}

	public function getModule(): string
	{
		return $this->module;
	}

	public function getClass(): ?string
	{
		return $this->class;
	}
}