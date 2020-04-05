<?php

namespace Bitrix\Report\VisualConstructor\Entity;

use Bitrix\Main\Entity\Query;
use Bitrix\Report\VisualConstructor\Config\Common;
use Bitrix\Report\VisualConstructor\Helper\Util;
use Bitrix\Report\VisualConstructor\Internal\DashboardTable;
use Bitrix\Report\VisualConstructor\Internal\Model;

/**
 * Dashboard entity for operate with dashboard table and with it's references.
 *
 * @method addRows(DashboardRow | DashboardRow[] $row) add row/rows to this board.
 * @method deleteRows(DashboardRow | DashboardRow[] $row) delete row connection and if it is ONE-TO-MANY delete Row entity.
 * @package Bitrix\Report\VisualConstructor\Entity
 */
class Dashboard extends Model
{
	protected $gId;
	protected $boardKey;
	protected $userId;

	/**
	 * @var string for checking this board is apply in db or not.
	 */
	protected $version = '';

	/**
	 * @var $rows DashboardRow[]
	 */
	protected $rows;

	/**
	 * @return string
	 */
	public static function getTableClassName()
	{
		return DashboardTable::getClassName();
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
		$attributes['BOARD_KEY'] = 'boardKey';
		$attributes['USER_ID'] = 'userId';
		$attributes['VERSION'] = 'version';
		return $attributes;
	}

	/**
	 * Map to set relations of this entity with other entities.
	 *
	 * @return array
	 */
	public static function getMapReferenceAttributes()
	{
		return array(
			'rows' => array(
				'type' => Common::ONE_TO_MANY,
				'targetEntity' => DashboardRow::getClassName(),
				'mappedBy' => 'dashboard'
			),
		);
	}


	/**
	 * Return board with fully loaded rows and widgets by board key and user id.
	 *
	 * @param string $boardKey Board key.
	 * @param int $userId User id.
	 * @return static
	 */
	public static function getBoardWithRowsAndWidgetsByBoardKeyUserId($boardKey, $userId)
	{
		$with = array('rows', 'rows.widgets', 'rows.widgets.configurations');
		$filter = Query::filter();
		$filter->where('BOARD_KEY', $boardKey);
		$filter->logic('and');
		$filter->where('USER_ID', $userId);
		$order = array('\Bitrix\Report\VisualConstructor\Internal\DashboardRow:DASHBOARD.WEIGHT' => 'ASC');
		$board = static::load($filter, $with, $order);
		return $board;
	}

	/**
	 * Return default dashboard with relation entities.
	 * if not exist dashboard with this key, by default create new dashboard with this key, and return it.
	 *
	 * @param string $boardKey Board key.
	 * @param bool $createIfNotExist Marker define create or not default board, when call this method.
	 * @return static
	 */
	public static function getDefaultBoardWithEverythingByBoardKey($boardKey, $createIfNotExist = true)
	{
		$with = array('rows', 'rows.widgets', 'rows.widgets.configurations', 'rows.widgets.reports', 'rows.widgets.reports.configurations');
		$filter = Query::filter();
		$filter->where('BOARD_KEY', $boardKey);
		$filter->logic('and');
		$filter->where('USER_ID', 0);
		$order = array('\Bitrix\Report\VisualConstructor\Internal\DashboardRow:DASHBOARD.WEIGHT' => 'ASC');
		$board = static::load($filter, $with, $order);

		if (!$board && $createIfNotExist)
		{
			$board = new Dashboard();
			$board->setGId(Util::generateUserUniqueId());
			$board->setBoardKey($boardKey);
			$board->setUserId(0);
			$board->save();
		}

		return $board;
	}

	/**
	 * Retun dashbaord by board key.
	 *
	 * @param sting $boardKey Board key.
	 * @return static
	 */
	public static function getDefaultBoardByBoardKey($boardKey)
	{
		$filter = Query::filter();
		$filter->where('BOARD_KEY', $boardKey);
		$filter->logic('and');
		$filter->where('USER_ID', 0);
		return static::load($filter);
	}

	/**
	 * Return dasboard with all nested relations for current user by board key.
	 *
	 * @param string $boardKey Board key.
	 * @return static
	 */
	public static function getCurrentUserBoardWithEverythingByBoardKey($boardKey)
	{
		/** @var \CUser $USER */
		global $USER;

		$userId = $USER->getId();

		$with = array('rows', 'rows.widgets', 'rows.widgets.configurations', 'rows.widgets.reports', 'rows.widgets.reports.configurations');
		$filter = Query::filter();
		$filter->where('BOARD_KEY', $boardKey);
		$filter->logic('and');
		$filter->where('USER_ID', $userId);
		$order = array('\Bitrix\Report\VisualConstructor\Internal\DashboardRow:DASHBOARD.WEIGHT' => 'ASC');
		$board = static::load($filter, $with, $order);
		return $board;
	}

	/**
	 * Get rows collection of current board.
	 *
	 * @return DashboardRow[]
	 */
	public function getRows()
	{
		return $this->rows;
	}

	/**
	 * Get Copy of Board and nested entities for createing board.
	 * All priamry keys are nulled.
	 *
	 * @return Dashboard
	 */
	public function getCopyForCurrentUser()
	{
		global $USER;

		$coreBoard = clone $this;
		$copyBoard = new Dashboard();
		$copyBoard->setBoardKey($coreBoard->getBoardKey());
		$copyBoard->setUserId($USER->getID());
		$copyBoard->setGId($coreBoard->getGId());
		$rows = $coreBoard->getRows();
		if (is_array($rows))
		{
			foreach ($rows as $row)
			{
				$copyBoard->addRows($row->getCopyForCurrentUser());
			}
		}


		return $copyBoard;
	}

	/**
	 * @return string
	 */
	public function getBoardKey()
	{
		return $this->boardKey;
	}

	/**
	 * Setter vor board key.
	 * @param string $boardKey Board key.
	 * @return void
	 */
	public function setBoardKey($boardKey)
	{
		$this->boardKey = $boardKey;
	}

	/**
	 * @return int
	 */
	public function getUserId()
	{
		return $this->userId;
	}

	/**
	 * User id setter.
	 *
	 * @param int $userId User id.
	 * @return void
	 */
	public function setUserId($userId)
	{
		$this->userId = $userId;
	}

	/**
	 * Load Dashboard by board key and user id.
	 *
	 * @param string $boardKey Board key.
	 * @param int $userId User id.
	 * @return Dashboard
	 */
	public static function loadByBoardKeyAndUserId($boardKey, $userId)
	{
		return static::load(array(
			'=BOARD_KEY' => $boardKey,
			'=USER_ID' => $userId
		));
	}

	/**
	 * Load multiple boards by board key.
	 *
	 * @param string $boardKey Board key.
	 * @return Dashboard[]
	 */
	public static function loadByBoardKeyMultiple($boardKey)
	{
		$filter = Query::filter();
		$filter->where('BOARD_KEY', $boardKey);
		return static::getModelList(array(
			'filter' => $filter
		));
	}

	/**
	 * @return string
	 */
	public function getGId()
	{
		return $this->gId;
	}

	/**
	 * Gid setter.
	 *
	 * @param string $gId Gid for current board.
	 * @return void
	 */
	public function setGId($gId)
	{
		$this->gId = $gId;
	}

	/**
	 * Get version.
	 *
	 * @return string
	 */
	public function getVersion()
	{
		return $this->version;
	}

	/**
	 * Setterr for version.
	 *
	 * @param string $version Version value.
	 * @return void
	 */
	public function setVersion($version)
	{
		$this->version = $version;
	}

}