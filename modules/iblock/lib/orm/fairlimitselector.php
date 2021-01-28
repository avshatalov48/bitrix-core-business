<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2020 Bitrix
 */

namespace Bitrix\Iblock\ORM;

use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Main\ORM\Objectify\Collection;
use Bitrix\Main\ORM\Objectify\EntityObject;

/**
 * @package    bitrix
 * @subpackage main
 */
class FairLimitSelector
{
	/**
	 * @param \Bitrix\Main\ORM\Query\Query $query
	 *
	 * @return Collection|null
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function fetchCollection($query)
	{
		$select = $query->getSelect();

		$query->setSelect([new ExpressionField('DISTINCT_ID', 'DISTINCT %s', 'ID')]);
		$query->addGroup('ID');

		// get elements data
		$ids = [];

		foreach ($query->exec()->fetchAll() as $row)
		{
			$ids[] = $row['DISTINCT_ID'];
		}

		/** @var Collection $elements */
		$dataClass = $query->getEntity()->getDataClass();
		$elements = $dataClass::createCollection();

		if (!empty($ids))
		{

			$query = $dataClass::query();

			$query->setSelect($select);

			$query->whereIn('ID', $ids);

			$resultElements = [];

			foreach ($query->fetchCollection() as $elementObject)
			{
				/** @var EntityObject $elementObject */
				$resultElements[$elementObject->getId()] = $elementObject;
			}

			// original sort
			foreach ($ids as $id)
			{
				$elements->sysAddActual($resultElements[$id]);
			}
		}

		return $elements;
	}
}
