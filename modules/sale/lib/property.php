<?php

namespace Bitrix\Sale;

use Bitrix\Main;
use Bitrix\Sale\Internals\OrderPropsRelationTable;
use Bitrix\Sale\Internals\OrderPropsTable;

/**
 * Class Property
 * @package Bitrix\Sale
 */
class Property extends PropertyBase
{
	/**
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	protected function loadRelations()
	{
		$relations = [];

		$dbRes = OrderPropsRelationTable::getList([
			'select' => ['ENTITY_ID', 'ENTITY_TYPE'],
			'filter' => [
				'=PROPERTY_ID' => $this->getId(),
				'@ENTITY_TYPE' => ['P', 'D']
			]
		]);

		while ($data = $dbRes->fetch())
		{
			$relations[] = $data;
		}

		return $relations;
	}
}
