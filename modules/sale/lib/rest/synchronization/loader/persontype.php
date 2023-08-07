<?php


namespace Bitrix\Sale\Rest\Synchronization\Loader;


use Bitrix\Sale\Internals\PersonTypeTable;

class PersonType extends Entity
{
	protected function getEntityTable()
	{
		return new PersonTypeTable();
	}
}