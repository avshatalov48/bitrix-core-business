<?php


namespace Bitrix\Sale\Rest\Entity;


class DeliveryServices extends Base
{

	public function getFields()
	{
		return [];
	}

	public function internalizeArguments($name, $arguments)
	{
		return $arguments;
	}

	public function externalizeResult($name, $fields)
	{
		return $fields;
	}
}