<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2022 Bitrix
 */

namespace Bitrix\Socialnetwork\Helper\UI\Grid\Workgroup;

use Bitrix\Socialnetwork\Component\WorkgroupList;
use Bitrix\Socialnetwork\UserToGroupTable;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Role
{
	public static function getRoleValue(array $params = []): string
	{
		$result = '';

		$relationFields = ($params['RELATION'] ?? []);
		if (empty($relationFields))
		{
			return $result;
		}

		$groupFields = ($params['GROUP'] ?? []);
		if ($groupFields['SCRUM_MASTER_ID'] > 0)
		{
			$suffix = '_SCRUM';
		}
		elseif ($groupFields['PROJECT'])
		{
			$suffix = '_PROJECT';
		}
		else
		{
			$suffix = '';
		}

		$role = $relationFields['ROLE'];

		$classList = [ 'ui-label' ];

		if (in_array($role, UserToGroupTable::getRolesMember(), true))
		{
			$classList[] = 'sonet-ui-grid-role';

			$isScrumMaster = (int)$relationFields['USER_ID'] === (int)$groupFields['SCRUM_MASTER_ID'];

			$parts = [];

			if (!(
				$isScrumMaster
				&& $role === UserToGroupTable::ROLE_MODERATOR
				&& $relationFields['AUTO_MEMBER'] === false
			))
			{
				$parts = [
					Loc::getMessage(
						'SOCIALNETWORK_HELPER_UI_GRID_ROLE_' . $role
						. $suffix
						. ($relationFields['AUTO_MEMBER'] ? '_AUTO' : '')
					),
				];
			}

			if ($isScrumMaster)
			{
				$parts[] = Loc::getMessage('SOCIALNETWORK_HELPER_UI_GRID_ROLE_SCRUM_MASTER');
			}

			$result = implode(', ', $parts);

			switch ($role)
			{
				case UserToGroupTable::ROLE_OWNER:
					$classList[] = '--role-green';
					break;
				case UserToGroupTable::ROLE_MODERATOR:
					$classList[] = '--role-yellow';
					break;
				case UserToGroupTable::ROLE_USER:
					$classList[] = '--role-blue';
					break;
				default:
			}
		}
		elseif ($role === UserToGroupTable::ROLE_REQUEST)
		{
			$groupId = (int) $groupFields['ID'];
			$gridId = ($params['GRID_ID'] ?? 'SONET_GROUP_LIST');
			$type = $relationFields['INITIATED_TYPE'];
			$onclick = 'event.stopPropagation();';

			if ($type === UserToGroupTable::INITIATED_BY_GROUP)
			{
				$acceptOnclick = $onclick . 'BX.Socialnetwork.WorkgroupList.Manager.getById(\''
					. $gridId . '\').getActionManager().act({action: \''
					. WorkgroupList::AJAX_ACTION_JOIN
					. '\',groupId: \'' . $groupId . '\',});';

				$cancelOnclick = $onclick . 'BX.Socialnetwork.WorkgroupList.Manager.getById(\''
					. $gridId . '\').getActionManager().act({action: \''
					. WorkgroupList::AJAX_ACTION_REJECT_OUTGOING_REQUEST
					. '\',groupId: \'' . $groupId . '\',});';

				return '
					<span class="sonet-ui-grid-badge-invite-box">
						<span class="ui-label sonet-ui-grid-badge-invite-accept">
							<span class="ui-label-inner">
								' . Loc::getMessage('SOCIALNETWORK_HELPER_UI_GRID_ROLE_REQUEST_' . $type) . '
							</span>
						</span>
						<span
							class="ui-label sonet-ui-grid-badge-accept"
							onclick="'. $acceptOnclick .'"
							title="' . Loc::getMessage('SOCIALNETWORK_HELPER_UI_GRID_ROLE_JOIN') . '"
						>
							<span class="ui-label-inner"></span>
						</span>
						<span
							class="ui-label sonet-ui-grid-badge-cancel"
							onclick="'. $cancelOnclick .'"
							title="' . Loc::getMessage('SOCIALNETWORK_HELPER_UI_GRID_ROLE_DELETE_OUTGOING_REQUEST') . '"
						>
							<span class="ui-label-inner"></span>
						</span>
					</span>
				';
			}
			elseif ($type === UserToGroupTable::INITIATED_BY_USER)
			{
				$onclick .= 'BX.Socialnetwork.WorkgroupList.Manager.getById(\''
					. $gridId . '\').getActionManager().act({action: \''
					. WorkgroupList::AJAX_ACTION_DELETE_INCOMING_REQUEST
					. '\',groupId: \'' . $groupId . '\',});';

				return '
					<span class="sonet-ui-grid-badge-invite-box">
						<span class="ui-label sonet-ui-grid-badge-invite">
							<span class="ui-label-inner">
								' . Loc::getMessage('SOCIALNETWORK_HELPER_UI_GRID_ROLE_REQUEST_' . $type) . '
							</span>
						</span>
						<span
							class="ui-label sonet-ui-grid-badge-cancel"
							onclick="'. $onclick .'"
							title="' . Loc::getMessage('SOCIALNETWORK_HELPER_UI_GRID_ROLE_DELETE_INCOMING_REQUEST') . '"
						>
							<span class="ui-label-inner"></span>
						</span>
					</span>
				';
			}
		}
		elseif ($role === UserToGroupTable::ROLE_BAN)
		{
			$result = Loc::getMessage('SOCIALNETWORK_HELPER_UI_GRID_ROLE_BAN');
		}

		$result = '
			<span class="' . implode(' ', $classList) . '">
				<span class="ui-label-inner">' . $result . '</span>
			</span>
		';

		return '<span class="sonet-ui-grid-request-cont">' . $result . '</span>';
	}

	public static function getJoinValue(array $params = []): string
	{
		$classList = [
			'ui-label',
			'sonet-ui-grid-join',
		];

		$result = Loc::getMessage('SOCIALNETWORK_HELPER_UI_GRID_ROLE_JOIN');

		if (
			isset($params['OPENED'])
			&& $params['OPENED'] === true
		)
		{
			$result = '<span onclick="' . htmlspecialcharsbx($params['ONCLICK']) . '" class="' . implode(' ', $classList) . '"><span class="ui-label-inner">' . $result . '</span></span>';
		}
		else
		{
			$result = '<a href="'. $params['PATH_TO_JOIN_GROUP'] . '" class="' . implode(' ', $classList) . '"><span class="ui-label-inner">' . $result . '</span></a>';
		}


		return '<span class="sonet-ui-grid-request-cont">' . $result . '</span>';
	}
}
