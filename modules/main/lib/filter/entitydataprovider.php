<?php
namespace Bitrix\Main\Filter;

use Bitrix\Main;

abstract class EntityDataProvider extends DataProvider
{
	/**
	 * Get specified entity field caption.
	 * @param string $fieldID Field ID.
	 * @return string
	 * @throws Main\NotImplementedException
	 */
	protected function getFieldName($fieldID)
	{
		throw new Main\NotImplementedException('Method getFieldName must be overridden');
	}
	/**
	 * Create filter field.
	 * @param string $fieldID Field ID.
	 * @param array|null $params Field parameters (optional).
	 * @return Field
	 * @throws Main\NotImplementedException
	 */
	protected function createField($fieldID, array $params = null)
	{
		if(!is_array($params))
		{
			$params = [];
		}

		if(!isset($params['name']))
		{
			$params['name'] = $this->getFieldName($fieldID);
		}

		return new Field($this, $fieldID, $params);
	}
}