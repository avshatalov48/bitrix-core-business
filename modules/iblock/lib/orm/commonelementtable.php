<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage iblock
 * @copyright  2001-2018 Bitrix
 */

namespace Bitrix\Iblock\ORM;

use Bitrix\Iblock\ElementTable;
use Bitrix\Iblock\ORM\Fields\PropertyOneToMany;
use Bitrix\Iblock\ORM\Fields\PropertyReference;
use Bitrix\Iblock\PropertyIndex\Manager;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Entity;
use Bitrix\Main\ORM\Event;
use Bitrix\Main\ORM\EventResult;
use Bitrix\Main\ORM\Fields\FieldError;
use CIBlock;
use CIBlockProperty;

/**
 * @package    bitrix
 * @subpackage iblock
 */
abstract class CommonElementTable extends DataManager
{
	/**
	 * @return Entity|string
	 */
	public static function getEntityClass()
	{
		return ElementEntity::class;
	}

	public static function getQueryClass()
	{
		return Query::class;
	}

	public static function setDefaultScope($query)
	{
		return $query->where("IBLOCK_ID", static::getEntity()->getIblock()->getId());
	}

	public static function getTableName()
	{
		return ElementTable::getTableName();
	}

	public static function getMap()
	{
		return ElementTable::getMap();
	}

	/**
	 * Clones specific Element entity for inherited Tables
	 *
	 * @return Entity
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function getEntity()
	{
		$class = static::getEntityClass()::normalizeEntityClass(get_called_class());

		if (!isset(static::$entity[$class]))
		{
			/** @var DataManager $parentClass */
			$parentClass = get_parent_class(get_called_class());

			if (!in_array(
				$parentClass,
				[ElementV1Table::class, ElementV2Table::class]
			))
			{
				static::$entity[$class] = clone $parentClass::getEntity();
				static::$entity[$class]->reinitialize($class);
			}
			else
			{
				static::$entity[$class] = parent::getEntity();
			}
		}

		return static::$entity[$class];
	}

	/**
	 * Iblock elements doesn't support group adding
	 *
	 * @param $rows
	 * @param false $ignoreEvents
	 * @return \Bitrix\Main\ORM\Data\AddResult|null
	 * @throws \Exception
	 */
	public static function addMulti($rows, $ignoreEvents = false)
	{
		$result = null;

		foreach ($rows as $row)
		{
			if (!empty($row['__object']))
			{
				$result = $row['__object']->save();
			}
			else
			{
				$result = static::add($row);
			}
		}

		return $result;
	}

	public static function onBeforeAdd(Event $event)
	{
		$object = $event->getParameter('object');
		$fields = static::getEntity()->getFields();

		$result = new EventResult;

		foreach ($fields as $field)
		{
			// check required properties
			$hasEmptyRequiredValue = false;

			if ($field instanceof PropertyReference || $field instanceof PropertyOneToMany)
			{
				$property = $field->getIblockElementProperty();

				if ($property->getIsRequired())
				{
					/** @var ValueStorage $valueContainer */
					$valueContainer = $object->get($field->getName());

					if (empty($valueContainer))
					{
						$hasEmptyRequiredValue = true;
					}

					// check with GetLength
					if ($valueContainer instanceof ValueStorage)
					{
						$userType = CIBlockProperty::GetUserType($property->getUserType());

						if(array_key_exists("GetLength", $userType))
						{
							$length = call_user_func_array(
								$userType["GetLength"],
								[
									$property->collectValues(),
									["VALUE" => $valueContainer->getValue()]
								]
							);
						}
						else
						{
							$length = mb_strlen($valueContainer->getValue());
						}

						$hasEmptyRequiredValue = ($length <= 0);
					}


					if ($hasEmptyRequiredValue)
					{
						$result->addError(new FieldError(
							$field,
							Loc::getMessage(
								"MAIN_ENTITY_FIELD_REQUIRED",
								["#FIELD#" => $property->getName()]
							),
							FieldError::EMPTY_REQUIRED
						));
					}
				}
			}
		}

		return $result;
	}

	public static function onAfterAdd(Event $event)
	{
		$elementId = (int) end($event->getParameters()['primary']);
		$iblockId = static::getEntity()->getIblock()->getId();

		// clear tag cache
		CIBlock::clearIblockTagCache($iblockId);

		// update index
		Manager::updateElementIndex($iblockId, $elementId);
	}

	public static function onAfterUpdate(Event $event)
	{
		$elementId = (int) end($event->getParameters()['primary']);
		$iblockId = static::getEntity()->getIblock()->getId();

		// clear tag cache
		CIBlock::clearIblockTagCache($iblockId);

		// update index
		Manager::updateElementIndex($iblockId, $elementId);
	}

	public static function onAfterDelete(Event $event)
	{
		parent::onAfterDelete($event);

		$elementId = (int) end($event->getParameters()['primary']);
		$iblockId = static::getEntity()->getIblock()->getId();
		$connection = static::getEntity()->getConnection();

		// delete property values
		$tables = [static::getEntity()->getSingleValueTableName(), static::getEntity()->getMultiValueTableName()];

		foreach (array_unique($tables) as $table)
		{
			$connection->query("DELETE FROM {$table} WHERE IBLOCK_ELEMENT_ID = {$elementId}");
		}

		// clear tag cache
		CIBlock::clearIblockTagCache($iblockId);

		// delete index
		Manager::deleteElementIndex($iblockId, $elementId);
	}
}
