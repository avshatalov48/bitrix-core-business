<?php
namespace Bitrix\Report\VisualConstructor\Controller;

use Bitrix\Report\VisualConstructor\Entity\DashboardRow;
use Bitrix\Report\VisualConstructor\Helper\Util;
use Bitrix\Report\VisualConstructor\Helper\Dashboard as DashboardHelper;
use Bitrix\Report\VisualConstructor\Internal\Error\Error;

/**
 * Class Row
 * @package Bitrix\Report\VisualConstructor\Controller
 */
class Row extends Base
{
	/**
	 * Add row to board.
	 *
	 * @param array $params Parameters for adding row to board.
	 * @return array|bool
	 */
	public function addAction($params)
	{
		$dashboardForUser = DashboardHelper::getDashboardByKeyForCurrentUser($params['boardKey']);
		if ($dashboardForUser)
		{
			$row = new DashboardRow();
			$row->setLayoutMap($params['layoutMap']);
			$row->setGId(Util::generateUserUniqueId());
			$row->setBoardId($params['boardId']);
			$row->setWeight(0);
			$dashboardForUser->addRows($row);
			$dashboardForUser->save();
			return array('id' => $row->getGId());
		}
		else
		{
			$this->adderror(new Error('No dashboard for current user'));
			return false;
		}
	}

	/**
	 * Adjust rows weights. for saving rows sorting.
	 *
	 * @param string $boardKey Board key.
	 * @param array $rows Row parameters like [gid => [weight => 1]].
	 * @return array|bool
	 */
	public function adjustWeightsAction($boardKey, $rows)
	{
		$dashboardForUser = DashboardHelper::getDashboardByKeyForCurrentUser($boardKey);
		if ($dashboardForUser)
		{
			$dashboardForUser->loadAttribute('rows');
			$savedRows = $dashboardForUser->getRows();
			if ($savedRows)
			{
				foreach ($savedRows as $row)
				{
					if (isset($rows[$row->getGId()]))
					{
						$row->setWeight($rows[$row->getGId()]['weight']);
					}
				}
			}


			$dashboardForUser->save();
			return true;
		}
		else
		{
			$this->adderror(new Error('No dashboard for current user'));
			return false;
		}
	}

	/**
	 * Delete row action.
	 *
	 * @param array $params Parameters like [boardId => 'some_board_id', rowId => 'some_sow_gid'].
	 * @return int|bool
	 */
	public function deleteAction($params)
	{
		$boardKey = $params['boardId'];
		$rowId = $params['rowId'];
		$dashboardForUser = DashboardHelper::getDashboardByKeyForCurrentUser($boardKey);
		if ($dashboardForUser)
		{
			$row = DashboardRow::getCurrentUserRowByGId($rowId);
			if ($row)
			{
				return $row->delete();
			}
			else
			{
				$this->adderror(new Error('No Row with this id'));
				return false;
			}
		}
		else
		{
			$this->adderror(new Error('No dashboard for current user'));
			return false;
		}
	}
}