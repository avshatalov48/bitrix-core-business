<?php


namespace Bitrix\Catalog\RestView;


use Bitrix\Rest\Integration\View\Base;

final class Enum extends Base
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