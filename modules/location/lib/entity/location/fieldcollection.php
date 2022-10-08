<?php

namespace Bitrix\Location\Entity\Location;

use Bitrix\Main\ArgumentTypeException;

/**
 * Class FieldCollection
 * @package Bitrix\Location\Entity\Location
 * @internal
 */
final class FieldCollection extends \Bitrix\Location\Entity\Generic\FieldCollection
{
	/** @var Field[] */
	protected $items = [];

	public function addItem($field): int
	{
		if(!($field instanceof Field))
		{
			throw new ArgumentTypeException('field must be the instance of Field');
		}

		return parent::addItem($field);
	}
}