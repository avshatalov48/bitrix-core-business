<?php

namespace Bitrix\Location\Entity\Format;

use Bitrix\Main\ArgumentTypeException;
use Bitrix\Main\SystemException;

/**
 * Class FieldCollection
 * @package Bitrix\Location\Entity\Format
 * @internal
 */
final class FieldCollection extends \Bitrix\Location\Entity\Generic\FieldCollection
{
	/** @var Field[] */
	protected $items = [];

	/**
	 * Add Format field to collection
	 * @param Field $field
	 * @return int
	 * @throws SystemException
	 */
	public function addItem($field): int
	{
		if(!($field instanceof Field))
		{
			throw new ArgumentTypeException('field must be the instance of Field');
		}

		$result = parent::addItem($field);

		/*
		 * Sort fields due to sort
		 * @todo: what about performance?
		 */
		usort(
			$this->items,
			function (Field $a, Field $b)
			{
				if ($a->getSort() === $b->getSort())
				{
					return 0;
				}

				return ($a->getSort() < $b->getSort()) ? -1 : 1;
			}
		);

		return $result;
	}
}
