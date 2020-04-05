<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage iblock
 * @copyright  2001-2018 Bitrix
 */

namespace Bitrix\Iblock\ORM;


use Bitrix\Iblock\ORM\Fields\PropertyReference;
use Bitrix\Main\ORM\Data\Result;
use Bitrix\Main\ORM\Objectify\EntityObject;
use Bitrix\Main\ORM\Objectify\State;
use Bitrix\Main\ORM\Objectify\Values;

/**
 * @property-read ElementV2Entity $entity
 *
 * @package    bitrix
 * @subpackage iblock
 */
abstract class ElementV2 extends CommonElement
{
	public function sysSaveRelations(Result $result)
	{
		parent::sysSaveRelations($result);

		// by default property references are not handled
		// we can collect them here and compose into one sql query
		// and mark them as ACTUAL after query

		/** @var EntityObject[] $valueObjects objects to save */
		$valueObjects = [];

		// save single value references
		foreach ($this->entity->getFields() as $field)
		{
			if ($field instanceof PropertyReference)
			{
				if ($this->has($field->getName()))
				{
					$valueObject = $this->get($field->getName());

					if ($valueObject->state == State::RAW || $valueObject->state == State::CHANGED)
					{
						$valueObjects[$field->getName()] = $valueObject;
					}
				}
			}
		}

		// compose data for update query
		$valuesToDb = [];

		foreach ($valueObjects as $valueObject)
		{
			// collect changed values and descriptions from all the objects
			if ($valueObject->isChanged('VALUE'))
			{
				$columnName = $valueObject->entity->getField('VALUE')->getColumnName();
				$valuesToDb[$columnName] = $valueObject->get('VALUE');
			}

			if ($valueObject->entity->hasField('DESCRIPTION') && $valueObject->isChanged('DESCRIPTION'))
			{
				$columnName = $valueObject->entity->getField('DESCRIPTION')->getColumnName();
				$valuesToDb[$columnName] = $valueObject->get('DESCRIPTION');
			}
		}

		if (!empty($valuesToDb))
		{
			// execute update
			$connection = $this->entity->getConnection();
			$helper = $connection->getSqlHelper();

			$tableName = $this->entity->getSingleValueTableName();
			$update = $helper->prepareUpdate($tableName, $valuesToDb);
			$where = $helper->prepareAssignment($tableName, 'IBLOCK_ELEMENT_ID', $this->getId());

			$sql = "UPDATE ".$helper->quote($tableName)." SET ".$update[0]." WHERE ".$where;
			$connection->queryExecute($sql, $update[1]);
		}

		// get current values and set them as actual
		foreach ($valueObjects as $fieldName => $valueObject)
		{
			$currentValues = $valueObject->collectValues(Values::CURRENT);

			foreach ($currentValues as $propFieldName => $propFieldValue)
			{
				$valueObject->sysSetActual($propFieldName, $propFieldValue);
			}

			$valueObject->sysPostSave();
		}
	}
}
