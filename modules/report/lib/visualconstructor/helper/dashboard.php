<?php
namespace Bitrix\Report\VisualConstructor\Helper;

use Bitrix\Main\Config\Option;
use Bitrix\Main\SystemException;
use Bitrix\Report\VisualConstructor\Entity\Dashboard as DashboardEntity;
use Bitrix\Report\VisualConstructor\Entity\Widget as WidgetEntity;
use Bitrix\Report\VisualConstructor\RuntimeProvider\DefaultBoardProvider;

/**
 * Class Dashboard
 * @package Bitrix\Report\VisualConstructor\Helper
 */
class Dashboard
{
	/**
	 * Try find dashboard with key for this user, if not exist create it from copy of default board and return.
	 *
	 * @param string $boardKey Board key.
	 * @return DashboardEntity
	 */
	public static function getDashboardByKeyForCurrentUser($boardKey)
	{
		global $USER;
		$userId = $USER->getId();
		$dashboardForUser = DashboardEntity::loadByBoardKeyAndUserId($boardKey, $userId);
		if (!$dashboardForUser)
		{
			self::renewDefaultDashboard($boardKey);
			$defaultDashboard = DashboardEntity::getDefaultBoardWithEverythingByBoardKey($boardKey);

			$dashboardForUser = $defaultDashboard->getCopyForCurrentUser();
			$dashboardForUser->setVersion('');
			$dashboardForUser->setUserId($userId);
			$dashboardForUser->save();
		}
		return $dashboardForUser;
	}

	/**
	 * Add this widget to end of all boards with key $boardKey.
	 * will create new row and place there $widget.
	 * @param string $boardKey Board key.
	 * @param WidgetEntity $widget Widget entity.
	 * @return array
	 */
	public static function addWidgetToDashboardsWithKey($boardKey, WidgetEntity $widget)
	{
		$dashboards = DashboardEntity::loadByBoardKeyMultiple($boardKey);
		$dashboardIds = array();
		foreach ($dashboards as $dashboard)
		{
			$cellId = 'cell_' . randString(4);
			$row = Row::getRowDefaultEntity(array(
				'cellIds' => array($cellId)
			));
			$widget->setWeight($cellId);
			$widget->setBoardId($boardKey);
			$row->addWidgets($widget->getCopyForCurrentUser());
			$dashboard->addRows($row);
			$dashboard->save();
			$dashboardIds[] = $dashboard->getId();
		}
		return $dashboardIds;
	}


	/**
	 * This method is for service.
	 * Find all default dashboards in product.
	 * Check if version change, then remove dashboard with all nested entities, and isnert new.
	 *
	 * @param string $boardKey Board key.
	 * @throws SystemException
	 * @return void
	 */
	public static function renewDefaultDashboard($boardKey)
	{
		$board = new DefaultBoardProvider();
		$board->addFilter('boardKey', $boardKey);
		$board = $board->execute()->getFirstResult();
		if ($board)
		{
			if (!$board->getVersion())
			{
				throw new SystemException("To renew default dashboard in db state, version of dashboard should exist");
			}

			$boardFromDb = DashboardEntity::getDefaultBoardByBoardKey($boardKey);

			if ($boardFromDb && $boardFromDb->getVersion() !== $board->getVersion())
			{
				$boardFromDb->delete();
				$board->save();
			}
			elseif (!$boardFromDb)
			{
				$board->save();
			}
		}
	}


	/**
	 * @param string $boardKey Board key.
	 * @return bool
	 */
	public static function getBoardModeIsDemo($boardKey)
	{
		$boardModes = \CUserOptions::GetOption('report_dashboard', 'IS_DEMO_MODE_MARKERS', array());
		if (isset($boardModes[$boardKey]))
		{
			 return $boardModes[$boardKey];
		}
		return self::getBoardCustomDefaultModeIsDemo($boardKey);
	}

	public static function setBoardModeIsDemo($boardKey, $mode)
	{
		$boardModes = \CUserOptions::GetOption('report_dashboard', 'IS_DEMO_MODE_MARKERS', array());
		$boardModes[$boardKey] = $mode;
		\CUserOptions::SetOption('report_dashboard', 'IS_DEMO_MODE_MARKERS', $boardModes);
	}

	public static function updateBoardCustomDefaultMode($boardKey, $demo = false)
	{
		if (self::checkBoardCustomDefaultModeIsExist($boardKey))
		{
			if (self::getBoardCustomDefaultModeIsDemo($boardKey) != $demo)
			{
				self::setBoardCustomDefaultModeIsDemo($boardKey, $demo);
			}
		}
		else
		{
			self::setBoardCustomDefaultModeIsDemo($boardKey, $demo);
		}
	}

	private static function setBoardCustomDefaultModeIsDemo($boardKey, $demo = false)
	{
		$modes = Option::get('report', 'BOARD_CUSTOM_DEFAULT_MODES', serialize(array()));
		$modes = unserialize($modes);
		$modes[$boardKey] = $demo ? 1 : 0;
		Option::set('report', 'BOARD_CUSTOM_DEFAULT_MODES', serialize($modes));
	}

	private static function checkBoardCustomDefaultModeIsExist($boardKey)
	{
		$modes = Option::get('report', 'BOARD_CUSTOM_DEFAULT_MODES', serialize(array()));
		$modes = unserialize($modes);
		return isset($modes[$boardKey]);
	}

	private static function getBoardCustomDefaultModeIsDemo($boardKey)
	{
		$modes = Option::get('report', 'BOARD_CUSTOM_DEFAULT_MODES', serialize(array()));
		$modes = unserialize($modes);
		return !empty($modes[$boardKey]);
	}

}