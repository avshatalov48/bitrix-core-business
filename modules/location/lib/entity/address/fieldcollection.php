<?php

namespace Bitrix\Location\Entity\Address;

use Bitrix\Main\ArgumentTypeException;

/**
 * Class FieldCollection
 * @package Bitrix\Location\Entity\Address
 * @internal
 */
final class FieldCollection extends \Bitrix\Location\Entity\Generic\FieldCollection
{
	/** @var Field[] */
	protected $items = [];

	/**
	 * @param Field $field
	 * @return int
	 * @throws ArgumentTypeException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function addItem($field): int
	{
		if(!($field instanceof Field))
		{
			throw new ArgumentTypeException('field must be the instance of Field');
		}

		return parent::addItem($field);
	}
}