<?php


namespace Bitrix\Sale\Rest\Entity;


class Enum extends Base
{

	public function getFields()
	{
		return [];
	}

	public function externalizeResult($name, $fields)
	{
		return $fields;
	}

	public function internalizeArguments($name, $arguments)
	{
		return $arguments;
	}
}