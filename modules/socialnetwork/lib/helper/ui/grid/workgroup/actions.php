<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2022 Bitrix
 */

namespace Bitrix\Socialnetwork\Helper\UI\Grid\Workgroup;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Uri;
use Bitrix\Socialnetwork\EO_Workgroup;
use Bitrix\Socialnetwork\EO_UserToGroup;
use Bitrix\Socialnetwork\Component\WorkgroupList;
use Bitrix\Socialnetwork\UserToGroupTable;

class Actions
{
	public static function getActions(
		?EO_Workgroup $group,
		?EO_UserToGroup $currentUserRelationItem,
		array $actions = [],
		array $params = []
	): array
	{
		$result = [];

		$isProject = (bool)$group->get('PROJECT');
		$isScrum = ($isProject && $group->get('SCRUM_MASTER_ID') > 0);

		if (in_array(WorkgroupList::AVAILABLE_ACTION_VIEW, $actions, true))
		{
			$text = Loc::getMessage('SOCIALNETWORK_HELPER_UI_GRID_ACTION_VIEW');
			$title = Loc::getMessage('SOCIALNETWORK_HELPER_UI_GRID_ACTION_VIEW_TITLE');
			if ($isScrum)
			{
				$title = Loc::getMessage('SOCIALNETWORK_HELPER_UI_GRID_ACTION_VIEW_TITLE_SCRUM');
			}
			elseif ($isProject)
			{
				$title = Loc::getMessage('SOCIALNETWORK_HELPER_UI_GRID_ACTION_VIEW_TITLE_PROJECT');
			}

			$groupUrl = str_replace(
				[ '#id#', '#ID#', '#GROUP_ID#', '#group_id#' ],
				$group->getId(),
				$params['PATH_TO_GROUP']
			);
			if ($isScrum)
			{
				$groupUrl = (new Uri($groupUrl))
					->addParams(['scrum' => 'Y'])
					->getUri()
				;
			}

			$result[] = [
				'text' => $text,
				'title' => $title,
				'href' => $groupUrl,
			];
		}

		if (in_array(WorkgroupList::AVAILABLE_ACTION_ADD_TO_FAVORITES, $actions, true))
		{
			$text = Loc::getMessage('SOCIALNETWORK_HELPER_UI_GRID_ACTION_ADD_TO_FAVORITES');
			$title = Loc::getMessage('SOCIALNETWORK_HELPER_UI_GRID_ACTION_ADD_TO_FAVORITES_TITLE');
			if ($isScrum)
			{
				$title = Loc::getMessage('SOCIALNETWORK_HELPER_UI_GRID_ACTION_ADD_TO_FAVORITES_TITLE_SCRUM');
			}
			elseif ($isProject)
			{
				$title = Loc::getMessage('SOCIALNETWORK_HELPER_UI_GRID_ACTION_ADD_TO_FAVORITES_TITLE_PROJECT');
			}

			$result[] = [
				'text' => $text,
				'title' => $title,
				'onclick' => 'BX.Socialnetwork.WorkgroupList.Manager.getById("' . $params['GRID_ID'] . '").getActionManager().act({
					action: "' . WorkgroupList::AJAX_ACTION_ADD_TO_FAVORITES . '",
					groupId: "' . $group->getId() . '",
				})',
			];
		}

		if (in_array(WorkgroupList::AVAILABLE_ACTION_REMOVE_FROM_FAVORITES, $actions, true))
		{
			$text = Loc::getMessage('SOCIALNETWORK_HELPER_UI_GRID_ACTION_REMOVE_FROM_FAVORITES');
			$title = Loc::getMessage('SOCIALNETWORK_HELPER_UI_GRID_ACTION_REMOVE_FROM_FAVORITES_TITLE');
			if ($isScrum)
			{
				$title = Loc::getMessage('SOCIALNETWORK_HELPER_UI_GRID_ACTION_REMOVE_FROM_FAVORITES_TITLE_SCRUM');
			}
			elseif ($isProject)
			{
				$title = Loc::getMessage('SOCIALNETWORK_HELPER_UI_GRID_ACTION_REMOVE_FROM_FAVORITES_TITLE_PROJECT');
			}

			$result[] = [
				'text' => $text,
				'title' => $title,
				'onclick' => 'BX.Socialnetwork.WorkgroupList.Manager.getById("' . $params['GRID_ID'] . '").getActionManager().act({
					action: "' . WorkgroupList::AJAX_ACTION_REMOVE_FROM_FAVORITES . '",
					groupId: "' . $group->getId() . '",
				})',
			];
		}

		if (in_array(WorkgroupList::AVAILABLE_ACTION_EDIT, $actions, true))
		{
			$text = Loc::getMessage('SOCIALNETWORK_HELPER_UI_GRID_ACTION_EDIT');
			$title = Loc::getMessage('SOCIALNETWORK_HELPER_UI_GRID_ACTION_EDIT_TITLE');
			if ($isScrum)
			{
				$title = Loc::getMessage('SOCIALNETWORK_HELPER_UI_GRID_ACTION_EDIT_TITLE_SCRUM');
			}
			elseif ($isProject)
			{
				$title = Loc::getMessage('SOCIALNETWORK_HELPER_UI_GRID_ACTION_EDIT_TITLE_PROJECT');
			}

			$result[] = [
				'text' => $text,
				'title' => $title,
				'href' => str_replace([ '#id#', '#ID#', '#GROUP_ID#', '#group_id#' ], $group->getId(), $params['PATH_TO_GROUP_EDIT']),
			];
		}

		if (in_array(WorkgroupList::AVAILABLE_ACTION_JOIN, $actions, true))
		{
			$text = Loc::getMessage('SOCIALNETWORK_HELPER_UI_GRID_ACTION_JOIN');
			$title = Loc::getMessage('SOCIALNETWORK_HELPER_UI_GRID_ACTION_JOIN_TITLE');
			if ($isScrum)
			{
				$title = Loc::getMessage('SOCIALNETWORK_HELPER_UI_GRID_ACTION_JOIN_TITLE_SCRUM');
			}
			elseif ($isProject)
			{
				$title = Loc::getMessage('SOCIALNETWORK_HELPER_UI_GRID_ACTION_JOIN_TITLE_PROJECT');
			}

			$menuItem = [
				'text' => $text,
				'title' => $title,
			];

			if (
				$group->getOpened()
				|| (
					$currentUserRelationItem
					&& $currentUserRelationItem->get('ROLE') === UserToGroupTable::ROLE_REQUEST
					&& $currentUserRelationItem->get('INITIATED_BY_TYPE') === UserToGroupTable::INITIATED_BY_GROUP
				)
			)
			{
				$menuItem['onclick'] = 'BX.Socialnetwork.WorkgroupList.Manager.getById("' . $params['GRID_ID'] . '").getActionManager().act({
					action: "' . WorkgroupList::AJAX_ACTION_JOIN . '",
					groupId: "' . $group->getId() . '",
				})';
			}
			else
			{
				$menuItem['href'] = str_replace([ '#id#', '#ID#', '#GROUP_ID#', '#group_id#' ], $group->getId(), $params['PATH_TO_JOIN_GROUP']);
			}

			$result[] = $menuItem;
		}

		if (in_array(WorkgroupList::AVAILABLE_ACTION_LEAVE, $actions, true))
		{
			$text = Loc::getMessage('SOCIALNETWORK_HELPER_UI_GRID_ACTION_LEAVE');
			$title = Loc::getMessage('SOCIALNETWORK_HELPER_UI_GRID_ACTION_LEAVE_TITLE');
			if ($isScrum)
			{
				$title = Loc::getMessage('SOCIALNETWORK_HELPER_UI_GRID_ACTION_LEAVE_TITLE_SCRUM');
			}
			elseif ($isProject)
			{
				$title = Loc::getMessage('SOCIALNETWORK_HELPER_UI_GRID_ACTION_LEAVE_TITLE_PROJECT');
			}

			$result[] = [
				'text' => $text,
				'title' => $title,
				'href' => str_replace([ '#id#', '#ID#', '#GROUP_ID#', '#group_id#' ], $group->getId(), $params['PATH_TO_LEAVE_GROUP']),
			];
		}

		if (in_array(WorkgroupList::AVAILABLE_ACTION_DELETE_INCOMING_REQUEST, $actions, true))
		{
			$text = Loc::getMessage('SOCIALNETWORK_HELPER_UI_GRID_ACTION_DELETE_INCOMING_REQUEST');
			$title = Loc::getMessage('SOCIALNETWORK_HELPER_UI_GRID_ACTION_DELETE_INCOMING_REQUEST_TITLE');
			if ($isScrum)
			{
				$title = Loc::getMessage('SOCIALNETWORK_HELPER_UI_GRID_ACTION_DELETE_INCOMING_REQUEST_TITLE_SCRUM');
			}
			elseif ($isProject)
			{
				$title = Loc::getMessage('SOCIALNETWORK_HELPER_UI_GRID_ACTION_DELETE_INCOMING_REQUEST_TITLE_PROJECT');
			}

			$result[] = [
				'text' => $text,
				'title' => $title,
				'onclick' => 'BX.Socialnetwork.WorkgroupList.Manager.getById("' . $params['GRID_ID'] . '").getActionManager().act({
					action: "' . WorkgroupList::AJAX_ACTION_DELETE_INCOMING_REQUEST . '",
					groupId: "' . $group->getId() . '",
				})',
			];
		}

		if (in_array(WorkgroupList::AVAILABLE_ACTION_ADD_TO_ARCHIVE, $actions, true))
		{
			$text = Loc::getMessage('SOCIALNETWORK_HELPER_UI_GRID_ACTION_ADD_TO_ARCHIVE');
			$title = Loc::getMessage('SOCIALNETWORK_HELPER_UI_GRID_ACTION_ADD_TO_ARCHIVE_TITLE');
			if ($isScrum)
			{
				$title = Loc::getMessage('SOCIALNETWORK_HELPER_UI_GRID_ACTION_ADD_TO_ARCHIVE_TITLE_SCRUM');
			}
			elseif ($isProject)
			{
				$title = Loc::getMessage('SOCIALNETWORK_HELPER_UI_GRID_ACTION_ADD_TO_ARCHIVE_TITLE_PROJECT');
			}

			$result[] = [
				'text' => $text,
				'title' => $title,
				'onclick' => 'BX.Socialnetwork.WorkgroupList.Manager.getById("' . $params['GRID_ID'] . '").getActionManager().act({
					action: "' . WorkgroupList::AJAX_ACTION_ADD_TO_ARCHIVE . '",
					groupId: "' . $group->getId() . '",
				})',
			];
		}

		if (in_array(WorkgroupList::AVAILABLE_ACTION_REMOVE_FROM_ARCHIVE, $actions, true))
		{
			$text = Loc::getMessage('SOCIALNETWORK_HELPER_UI_GRID_ACTION_REMOVE_FROM_ARCHIVE');
			$title = Loc::getMessage('SOCIALNETWORK_HELPER_UI_GRID_ACTION_REMOVE_FROM_ARCHIVE_TITLE');
			if ($isScrum)
			{
				$title = Loc::getMessage('SOCIALNETWORK_HELPER_UI_GRID_ACTION_REMOVE_FROM_ARCHIVE_TITLE_SCRUM');
			}
			elseif ($isProject)
			{
				$title = Loc::getMessage('SOCIALNETWORK_HELPER_UI_GRID_ACTION_REMOVE_FROM_ARCHIVE_TITLE_PROJECT');
			}

			$result[] = [
				'text' => $text,
				'title' => $title,
				'onclick' => 'BX.Socialnetwork.WorkgroupList.Manager.getById("' . $params['GRID_ID'] . '").getActionManager().act({
					action: "' . WorkgroupList::AJAX_ACTION_REMOVE_FROM_ARCHIVE . '",
					groupId: "' . $group->getId() . '",
				})',
			];
		}

		if (in_array(WorkgroupList::AVAILABLE_ACTION_DELETE, $actions, true))
		{
			$text = Loc::getMessage('SOCIALNETWORK_HELPER_UI_GRID_ACTION_DELETE');
			$title = Loc::getMessage('SOCIALNETWORK_HELPER_UI_GRID_ACTION_DELETE_TITLE');
			if ($isScrum)
			{
				$title = Loc::getMessage('SOCIALNETWORK_HELPER_UI_GRID_ACTION_DELETE_TITLE_SCRUM');
			}
			elseif ($isProject)
			{
				$title = Loc::getMessage('SOCIALNETWORK_HELPER_UI_GRID_ACTION_DELETE_TITLE_PROJECT');
			}

			$result[] = [
				'text' => $text,
				'title' => $title,
				'href' => str_replace([ '#id#', '#ID#', '#GROUP_ID#', '#group_id#' ], $group->getId(), $params['PATH_TO_GROUP_DELETE']),
			];
		}

		return $result;
	}
}
