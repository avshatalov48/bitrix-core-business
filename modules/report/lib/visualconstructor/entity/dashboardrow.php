<?php

namespace Bitrix\Report\VisualConstructor\Entity;

use Bitrix\Main\Entity\Query;
use Bitrix\Report\VisualConstructor\Config\Common;
use Bitrix\Report\VisualConstructor\Internal\DashboardRowTable;
use Bitrix\Report\VisualConstructor\Internal\Model;


/**
 * Row entity for operate with dashboard row table and with it's references
 *
 * @method addWidgets(Widget | Widgets[] $widget) add widget/widgets to this row
 * @method deleteReports(Widget | Widgets[] $widget) delete widget connection and if it is ONE-TO-MANY delete Widget entity
 */
class DashboardRow extends Model
{
	protected $gId;
	protected $boardId;
	protected $weight;
	protected $layoutMap = '';

	/**@var Widget[] $widgets * */
	protected $widgets = array();

	/**@var Dashboard $dashboard * */
	protected $dashboard;

	/**
	 * @return string
	 */
	public static function getTableClassName()
	{
		return DashboardRowTable::getClassName();
	}

	/**
	 * Returns the list of pair for mapping data and object properties.
	 * Key is field in DataManager, value is object property.
	 *
	 * @return array
	 */
	public static function getMapAttributes()
	{
		$attributes = parent::getMapAttributes();
		$attributes['GID'] = 'gId';
		$attributes['BOARD_ID'] = 'boardId';
		$attributes['WEIGHT'] = 'weight';
		$attributes['LAYOUT_MAP'] = 'layoutMap';
		return $attributes;
	}

	/**
	 * @return array
	 */
	public static function getMapReferenceAttributes()
	{
		return array(
			'widgets' => array(
				'type' => Common::ONE_TO_MANY,
				'targetEntity' => Widget::getClassName(),
				'mappedBy' => 'row'
			),
			'dashboard' => array(
				'type' => Common::MANY_TO_ONE,
				'targetEntity' => Dashboard::getClassName(),
				'inversedBy' => 'rows',
				'join' => array(
					'field' => array('boardId', 'id')
				)
			),
		);
	}

	/**
	 * @return DashboardRow
	 */
	public function getCopyForCurrentUser()
	{
		$coreRow = clone $this;
		$copyRow = new DashboardRow();
		$copyRow->setBoardId($coreRow->getBoardId());
		$copyRow->setGId($coreRow->getGId());
		$copyRow->setWeight($coreRow->getWeight());
		$copyRow->setLayoutMap($coreRow->getLayoutMap());

		$widgets = $coreRow->getWidgets();
		if (is_array($widgets))
		{
			foreach ($widgets as $widget)
			{
				$copyRow->addWidgets($widget->getCopyForCurrentUser());
			}
		}


		return $copyRow;
	}


	/**
	 * @return Widget[]
	 */
	public function getWidgets()
	{
		return $this->widgets;
	}

	/**
	 * @return string
	 */
	public function getBoardId()
	{
		return $this->boardId;
	}

	/**
	 * Setter for board id.
	 *
	 * @param string $boardId Board id.
	 * @return void
	 */
	public function setBoardId($boardId)
	{
		$this->boardId = $boardId;
	}

	/**
	 * @return int
	 */
	public function getWeight()
	{
		return $this->weight;
	}

	/**
	 * Setter of Weight in rows list of dashboard. (for sorting).
	 *
	 * @param mixed $weight Weight of report.
	 * @return void
	 */
	public function setWeight($weight)
	{
		$this->weight = $weight;
	}

	/**
	 * Load Rows list by board id and rows gIds.
	 *
	 * @param array $gIds Collection of gIds.
	 * @param string $boardId Board id.
	 * @return static[]
	 */
	public static function getRowsByGIdsAndBoardId(array $gIds, $boardId)
	{
		return static::getModelList(array(
			'select' => array('*'),
			'filter' => Query::filter()->where('BOARD_ID', $boardId)->logic('and')->whereIn('GID', $gIds)
		));
	}

	/**
	 * Load and return rows list by board id.
	 *
	 * @param string $boardId Board id.
	 * @return static[]
	 */
	public static function getRowsWithWidgetsByBoard($boardId)
	{
		$rows = static::getModelList(array(
			'select' => array('*'),
			'filter' => array('=BOARD_ID' => $boardId),
			'with' => array('widgets', 'widgets.configurations'),
			'order' => array('WEIGHT' => 'ASC'),
		));
		return $rows;
	}

	/**
	 * Load and return row by gId.
	 *
	 * @param string $gId Row gId.
	 * @return static
	 */
	public static function loadByGId($gId)
	{
		return static::load(array('GID' => $gId));
	}

	/**
	 * Laod current users row by row gId.
	 *
	 * @param string $rowGId Value of row gId.
	 * @return DashboardRow
	 */
	public static function getCurrentUserRowByGId($rowGId)
	{
		global $USER;
		if ($USER)
		{
			$row = static::load(
				array(
					'GID' => $rowGId,
					'DASHBOARD.USER_ID' => $USER->getId()
				)
			);
			return $row;
		}
		return null;
	}

	/**
	 * Get rows with nested widgets and reprts.
	 *
	 * @param string $boardId Board id.
	 * @return static[]
	 */
	public static function getRowsWithReportsByBoard($boardId)
	{
		$rows = static::getModelList(array(
			'select' => array('*'),
			'filter' => Query::filter()->where('BOARD_ID', $boardId),
			'with' => array('widgets', 'widgets.configurations', 'widgets.reports.configurations'),
			'order' => array('WEIGHT' => 'ASC'),
		));
		return $rows;
	}

	/**
	 * @return array
	 */
	public function getLayoutMap()
	{
		return unserialize($this->layoutMap);
	}

	/**
	 * Serialize and set layout map array.
	 *
	 * @param array $layoutMap Layout map.
	 * @return void
	 */
	public function setLayoutMap($layoutMap)
	{
		$this->layoutMap = serialize($layoutMap);
	}

	/**
	 * @return Dashboard
	 */
	public function getDashboard()
	{
		return $this->dashboard;
	}

	/**
	 * @return string
	 */
	public function getGId()
	{
		return $this->gId;
	}

	/**
	 * Setter for gId value.
	 *
	 * @param string $gId Value of gId.
	 * @return void
	 */
	public function setGId($gId)
	{
		$this->gId = $gId;
	}

}