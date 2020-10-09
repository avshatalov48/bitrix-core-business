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

/**
 * @package    bitrix
 * @subpackage iblock
 */
abstract class ElementV1 extends CommonElement
{
	/**
	 * Accepts PropertyValue and scalar, converts it to property reference
	 *
	 * @param $fieldName
	 * @param $value
	 *
	 * @return EntityObject
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function sysSetValue($fieldName, $value)
	{
		$field = $this->entity->getField($fieldName);

		if ($field instanceof PropertyReference)
		{
			// for v1 preferable to change existing object (sel+upd) instead of new (del+ins)
			if ($this->state !== State::RAW && !$this->sysIsFilled($fieldName))
			{
				$this->fill($fieldName);
			}
		}

		return parent::sysSetValue($fieldName, $value);
	}

	public function sysSaveRelations(Result $result)
	{
		parent::sysSaveRelations($result);

		// save single value references
		foreach ($this->entity->getFields() as $field)
		{
			if ($field instanceof PropertyReference)
			{
				if ($this->sysHasValue($field->getName()))
				{
					/** @var EntityObject $valueObject */
					$valueObject = $this->get($field->getName());

					if ($valueObject->state == State::RAW)
					{
						// previously we made fill, so now we don't need to remove old value
						// it would be insert only, and that's all
						$valueObject->save();
					}
					elseif ($valueObject->state == State::CHANGED)
					{
						// regular update
						$valueObject->save();
					}
				}
			}
		}
	}
}
