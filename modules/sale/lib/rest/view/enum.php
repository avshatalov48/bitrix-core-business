<?php

namespace Bitrix\Sale\Rest\View;

class Enum extends Base
{
	public function getFields()
	{
	return [];
	}

	public function externalizeResult($name, $fields): array
	{
		return $fields;
	}

	public function internalizeArguments($name, $arguments): array
	{
		return $arguments;
	}
}
