<?php
namespace Bitrix\Report\VisualConstructor\Helper;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Report\VisualConstructor\Entity\DashboardRow;

/**
 * Helper functions for Row entity
 * @package Bitrix\Report\VisualConstructor\Helper
 */
class Row
{
	/**
	 * Build row layout map with passed positions.
	 *
	 * @param array $positions Array of available positions in built Row layout map.
	 * @return array
	 */
	public static function getDefaultRowLayoutMap($positions)
	{
		$rowLayout = array(
			'type' => 'cell-container',
			'orientation' => 'horizontal',
			'elements' => array()
		);
		foreach ($positions as $position)
		{
			$rowLayout['elements'][] = array(
				'type' => 'cell',
				'id' => $position
			);
		}

		return $rowLayout;
	}


	/**
	 * Row fabric generate row with layout where exist cells with ids from $positions array.
	 *
	 * @param array $params Parameters like [cellIds => ['cell_1', 'cell_2'], weight => 5].
	 * @return DashboardRow
	 * @throws ArgumentException
	 */
	public static function getRowDefaultEntity($params)
	{
		if (!isset($params['cellIds']))
		{
			$parameter = 'cellIds';
			throw new ArgumentException($parameter);
		}

		$cellsIds = $params['cellIds'];
		$weight = isset($params['weight']) ? $params['weight'] : 0;
		$row = new DashboardRow();
		$layoutMap = self::getDefaultRowLayoutMap($cellsIds);
		$row->setLayoutMap($layoutMap);
		$row->setGId(Util::generateUserUniqueId());
		$row->setWeight($weight);

		return $row;
	}
}