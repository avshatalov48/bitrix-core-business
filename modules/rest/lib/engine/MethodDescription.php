<?php

namespace Bitrix\Rest\Engine;
class MethodDescription
{
	public function __construct(private string $scope, private $class, private string $method)
	{
	}

	public function getScope(): string
	{
		return $this->scope;
	}

	/**
	 * @return mixed
	 */
	public function getClass()
	{
		return $this->class;
	}

	public function getMethod(): string
	{
		return $this->method;
	}
}