<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

require_once(__DIR__ . '/api.php');

use Bitrix\Main\Localization\Loc;
use Bitrix\Socialnetwork\Component\WorkgroupUserList;
use Bitrix\Socialnetwork\UserToGroupTable;

/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

Loc::loadMessages(__FILE__);

$componentClassName = get_class($this->getComponent());

$gridColumns = $arResult['GRID_COLUMNS'];

$connectedDepartmentsIdList = [];

if (\Bitrix\Main\ModuleManager::isModuleInstalled('intranet'))
{
	$connectedDepartmentsIdList = array_map(static function($item) {
		return (int)$item['ID'];
	}, $arResult['CONNECTED_DEPARTMENTS_LIST']);
}

$lastConnectedDepartmentId = 0;

$gridData = $arResult['ROWS'];
$arResult['ROWS'] = [];

$canDisconnect = (
	$arResult['GROUP_PERMS']['UserCanModifyGroup']
	|| \Bitrix\Socialnetwork\Helper\Workgroup::isCurrentUserModuleAdmin()
);

foreach ($gridData as $key => $row)
{
	$processedRow = $row;

	$item = $row['data']['ROW_FIELDS'];
	$userItem = $item->getUser();
	$connectedUserDepartmentId = 0;

	if (
		!empty($connectedDepartmentsIdList)
		&& !empty($userItem['UF_DEPARTMENT'])
		&& $item->getRole() === UserToGroupTable::ROLE_USER
		&& $item->getAutoMember()
	)
	{
		$userDepartmentList = array_map(static function ($item) {
			return (int)$item;
		}, array_filter($userItem['UF_DEPARTMENT'], static function($item) {
			return (int)$item > 0;
		}));

		$connectedUserDepartmentIdList = array_intersect($userDepartmentList, $connectedDepartmentsIdList);
		$connectedUserDepartmentId = array_shift($connectedUserDepartmentIdList);

		if (
			$lastConnectedDepartmentId !== $connectedUserDepartmentId
			&& in_array($connectedUserDepartmentId, $connectedDepartmentsIdList, true)
		)
		{
			$lastConnectedDepartmentId = $connectedUserDepartmentId;

			$filteredDepartmentsList = array_filter($arResult['CONNECTED_DEPARTMENTS_LIST'], static function($item) use ($connectedUserDepartmentId) {
				return ((int)$item['ID'] === $connectedUserDepartmentId);
			});
			$connectedDepartmentData = array_shift($filteredDepartmentsList);

			$disconnectDepartmentLink = '';

			if ($canDisconnect)
			{
				if (in_array((int)$connectedDepartmentData['ID'], $arResult['GROUP']['UF_SG_DEPT'], true))
				{
					$disconnectDepartmentLink = '<span class="sonet-group-user-grid-action" data-bx-action="disconnectDepartment" data-bx-department="' . $connectedUserDepartmentId . '">' .
						Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TEMPLATE_DEPARTMENT_DISCONNECT') .
						'</span>';
				}
				else
				{

					$filteredSubDepartmentsList = array_filter(
						$arResult['CONNECTED_SUBDEPARTMENTS_LIST'],
						static function($subDepartmentsList) use ($connectedDepartmentData) {
							return in_array((int)$connectedDepartmentData['ID'], $subDepartmentsList, true);
						}
					);
					if (!empty($filteredSubDepartmentsList))
					{
						$key = (int)array_key_first($filteredSubDepartmentsList);
						$filteredDepartmentsList = array_filter(
							$arResult['CONNECTED_DEPARTMENTS_LIST'],
							static function($departmentData) use ($key) {
								return (int)$departmentData['ID'] === $key;
							}
						);
						if (!empty($filteredDepartmentsList))
						{
							$parentConnectedDepartmentData = array_shift($filteredDepartmentsList);
							$disconnectDepartmentLink =
								'&nbsp;/&nbsp;<a href="' .
								htmlspecialcharsbx($parentConnectedDepartmentData['URL']) .
								'" class="">' . htmlspecialcharsEx($parentConnectedDepartmentData['NAME']) . '</a>&nbsp;' .
								'<span class="sonet-group-user-grid-action" data-bx-action="disconnectDepartment" data-bx-department="' . (int)$parentConnectedDepartmentData['ID'] . '">' .
								Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TEMPLATE_DEPARTMENT_DISCONNECT') .
								'</span>';
						}
					}
				}
			}

			$arResult['ROWS'][] = [
				'id' => 'connected_dept' . $connectedUserDepartmentId,
				'has_child' => true,
				'parent_id' => 0,
				'custom' => '<div class="sonet-group-user-grid-wrapper">' .
						'<a href="' .
							htmlspecialcharsbx($connectedDepartmentData['URL']) .
						'" class="">' . htmlspecialcharsEx($connectedDepartmentData['NAME']) . '</a>' .
						($disconnectDepartmentLink !== '' ? '&nbsp;' . $disconnectDepartmentLink : '') .
					'</div>',
				'not_count' => true,
				'draggable' => false,
				'group_id' => $connectedDepartmentData['ID'],
				'attrs' => [
					'data-type' => 'department',
					'data-group-id' => $connectedUserDepartmentId,
				],
			];
		}
	}

	foreach ($gridColumns as $column)
	{
		switch ($column)
		{
			case 'ID':
				$processedRow['data'][$column] = (int)$userItem->getId();
				break;
			case 'EMAIL':
				$email = $userItem->getEmail();
				$processedRow['data'][$column] = '<a href="mailto:'.htmlspecialcharsbx($email).'">'.htmlspecialcharsEx($email).'</a>';
				break;
			case 'DEPARTMENT':
				$processedRow['data'][$column] = WorkgroupUserListTemplate::getDepartmentValue([
					'FIELDS' => $userItem,
					'PATH' => ($arResult['EXTRANET_SITE'] !== 'Y' ? $arParams['PATH_TO_DEPARTMENT'] : ''),
				]);
				break;
			case 'FULL_NAME':
				$processedRow['data'][$column] = WorkgroupUserListTemplate::getNameFormattedValue([
					'FIELDS' => [
						'ID' => $userItem->getId(),
						'NAME' => $userItem->getName(),
						'LAST_NAME' => $userItem->getLastName(),
						'SECOND_NAME' => $userItem->getSecondName(),
						'PERSONAL_PHOTO' => $userItem->getPersonalPhoto(),
						'PERSONAL_GENDER' => $userItem->getPersonalGender(),
						'WORK_POSITION' => $userItem->getWorkPosition(),
					],
					'PATH' => $arParams['PATH_TO_USER'],
				]);
				break;
			case 'ROLE':
				$processedRow['data'][$column] = WorkgroupUserListTemplate::getRoleValue([
					'FIELDS' => [
						'ID' => $userItem->getId(),
						'ROLE' => $item->getRole(),
						'AUTO_MEMBER' => $item->getAutoMember(),
						'INITIALIZED_BY_TYPE' => $item->getInitiatedByType(),
						'DATE_CREATE' => $item->getDateCreate(),
					],
					'GROUP' => $arResult['GROUP'],
				]);

				break;
			case 'ACTIONS':
				$processedRow['data'][$column] = WorkgroupUserListTemplate::getActionsValue([
					'FIELDS' => [
						'ID' => $userItem->getId(),
					],
					'ACTIONS' => $processedRow['actions'],
				]);

				break;
			default:
				$processedRow['data'][$column] = htmlspecialcharsEx($item[$column]);
		}
	}

	$processedRow['attrs'] = [
		'data-type' => 'user',
		'data-group-id' => $connectedUserDepartmentId,
	];

	$actions = $row['actions'];
	$processedRow['actions'] = [];

	if (in_array($componentClassName::AVAILABLE_ACTION_VIEW_PROFILE, $actions, true))
	{
		$processedRow['actions'][] = [
			'TEXT' => Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TEMPLATE_ACTION_VIEW'),
			'TITLE' => Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TEMPLATE_ACTION_VIEW_TITLE'),
			'ONCLICK' => 'BX.Socialnetwork.WorkgroupUserList.Manager.getById("' . $arResult['GRID_ID'] . '").getActionManager().viewProfile({
				pathToUser: "'. htmlspecialcharsbx($arParams['PATH_TO_USER']) . '",
				userId: ' . $userItem->getId() . '
			})',
		];
	}

	if (in_array($componentClassName::AVAILABLE_ACTION_SET_OWNER, $actions, true))
	{
		if ($arResult['GROUP']['SCRUM'] === 'Y')
		{
			$text = Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TEMPLATE_ACTION_SET_OWNER_SCRUM');
			$title = Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TEMPLATE_ACTION_SET_OWNER_SCRUM_TITLE');
		}
		elseif ($arResult['GROUP']['PROJECT'] === 'Y')
		{
			$text = Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TEMPLATE_ACTION_SET_OWNER_PROJECT');
			$title = Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TEMPLATE_ACTION_SET_OWNER_PROJECT_TITLE');
		}
		else
		{
			$text = Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TEMPLATE_ACTION_SET_OWNER');
			$title = Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TEMPLATE_ACTION_SET_OWNER_TITLE');
		}

		$processedRow['actions'][] = [
			'TEXT' => $text,
			'TITLE' => $title,
			'ONCLICK' => 'BX.Socialnetwork.WorkgroupUserList.Manager.getById("' . $arResult['GRID_ID'] . '").getActionManager().act({
				action: "' . WorkgroupUserList::AJAX_ACTION_SET_OWNER . '",
				userId: ' . $userItem->getId() . ',
			})',
		];
	}

	if (in_array($componentClassName::AVAILABLE_ACTION_SET_SCRUM_MASTER, $actions, true))
	{
		$processedRow['actions'][] = [
			'TEXT' => Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TEMPLATE_ACTION_SET_SCRUM_MASTER'),
			'TITLE' => Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TEMPLATE_ACTION_SET_SCRUM_MASTER'),
			'ONCLICK' => 'BX.Socialnetwork.WorkgroupUserList.Manager.getById("' . $arResult['GRID_ID'] . '").getActionManager().act({
				action: "' . WorkgroupUserList::AJAX_ACTION_SET_SCRUM_MASTER . '",
				userId: ' . $userItem->getId() . ',
			})',
		];
	}

	if (in_array($componentClassName::AVAILABLE_ACTION_SET_MODERATOR, $actions, true))
	{
		if ($arResult['GROUP']['SCRUM'] === 'Y')
		{
			$text = Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TEMPLATE_ACTION_SET_MODERATOR_SCRUM');
			$title = Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TEMPLATE_ACTION_SET_MODERATOR_SCRUM_TITLE');
		}
		elseif ($arResult['GROUP']['PROJECT'] === 'Y')
		{
			$text = Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TEMPLATE_ACTION_SET_MODERATOR_PROJECT');
			$title = Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TEMPLATE_ACTION_SET_MODERATOR_PROJECT_TITLE');
		}
		else
		{
			$text = Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TEMPLATE_ACTION_SET_MODERATOR');
			$title = Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TEMPLATE_ACTION_SET_MODERATOR_TITLE');
		}

		$processedRow['actions'][] = [
			'TEXT' => $text,
			'TITLE' => $title,
			'ONCLICK' => 'BX.Socialnetwork.WorkgroupUserList.Manager.getById("' . $arResult['GRID_ID'] . '").getActionManager().act({
				action: "' . WorkgroupUserList::AJAX_ACTION_SET_MODERATOR . '", 
				userId: ' . $userItem->getId() . ',
			})',
		];
	}

	if (in_array($componentClassName::AVAILABLE_ACTION_REMOVE_MODERATOR, $actions, true))
	{
		if ($arResult['GROUP']['SCRUM'] === 'Y')
		{
			$text = Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TEMPLATE_ACTION_REMOVE_MODERATOR_SCRUM');
			$title = Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TEMPLATE_ACTION_REMOVE_MODERATOR_SCRUM');
		}
		elseif ($arResult['GROUP']['PROJECT'] === 'Y')
		{
			$text = Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TEMPLATE_ACTION_REMOVE_MODERATOR_PROJECT');
			$title = Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TEMPLATE_ACTION_REMOVE_MODERATOR_PROJECT_TITLE');
		}
		else
		{
			$text = Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TEMPLATE_ACTION_REMOVE_MODERATOR');
			$title = Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TEMPLATE_ACTION_REMOVE_MODERATOR_TITLE');
		}

		$processedRow['actions'][] = [
			'TEXT' => $text,
			'TITLE' => $title,
			'ONCLICK' => 'BX.Socialnetwork.WorkgroupUserList.Manager.getById("' . $arResult['GRID_ID'] . '").getActionManager().act({
				action: "' . WorkgroupUserList::AJAX_ACTION_REMOVE_MODERATOR . '", 
				userId: ' . $userItem->getId() . ',
			})',
		];
	}

	if (in_array($componentClassName::AVAILABLE_ACTION_EXCLUDE, $actions, true))
	{
		if ($arResult['GROUP']['SCRUM'] === 'Y')
		{
			$title = Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TEMPLATE_ACTION_EXCLUDE_SCRUM_TITLE');
		}
		elseif ($arResult['GROUP']['PROJECT'] === 'Y')
		{
			$title = Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TEMPLATE_ACTION_EXCLUDE_PROJECT_TITLE');
		}
		else
		{
			$title = Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TEMPLATE_ACTION_EXCLUDE_TITLE');
		}

		$processedRow['actions'][] = [
			'TEXT' => Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TEMPLATE_ACTION_EXCLUDE'),
			'TITLE' => $title,
			'ONCLICK' => 'BX.Socialnetwork.WorkgroupUserList.Manager.getById("' . $arResult['GRID_ID'] . '").getActionManager().act({
				action: "' . WorkgroupUserList::AJAX_ACTION_EXCLUDE . '", 
				userId: ' . $userItem->getId() .',
			})',
		];
	}

	if (in_array($componentClassName::AVAILABLE_ACTION_DELETE_OUTGOING_REQUEST, $actions, true))
	{
		if ($arResult['GROUP']['SCRUM'] === 'Y')
		{
			$title = Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TEMPLATE_ACTION_DELETE_REQUEST_SCRUM_TITLE');
		}
		elseif ($arResult['GROUP']['PROJECT'] === 'Y')
		{
			$title = Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TEMPLATE_ACTION_DELETE_REQUEST_PROJECT_TITLE');
		}
		else
		{
			$title = Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TEMPLATE_ACTION_DELETE_REQUEST_TITLE');
		}

		$processedRow['actions'][] = [
			'TEXT' => Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TEMPLATE_ACTION_DELETE_REQUEST'),
			'TITLE' => $title,
			'ONCLICK' => 'BX.Socialnetwork.WorkgroupUserList.Manager.getById("' . $arResult['GRID_ID'] . '").getActionManager().act({
				action: "' . WorkgroupUserList::AJAX_ACTION_DELETE_OUTGOING_REQUEST . '", 
				userId: ' . $userItem->getId() . ',
			})',
		];
	}

	if (in_array($componentClassName::AVAILABLE_ACTION_DELETE_INCOMING_REQUEST, $actions, true))
	{
		if ($arResult['GROUP']['SCRUM'] === 'Y')
		{
			$title = Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TEMPLATE_ACTION_DELETE_INCOMING_REQUEST_SCRUM_TITLE');
		}
		elseif ($arResult['GROUP']['PROJECT'] === 'Y')
		{
			$title = Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TEMPLATE_ACTION_DELETE_INCOMING_REQUEST_PROJECT_TITLE');
		}
		else
		{
			$title = Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TEMPLATE_ACTION_DELETE_INCOMING_REQUEST_TITLE');
		}

		$processedRow['actions'][] = [
			'TEXT' => Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TEMPLATE_ACTION_DELETE_INCOMING_REQUEST'),
			'TITLE' => $title,
			'ONCLICK' => 'BX.Socialnetwork.WorkgroupUserList.Manager.getById("' . $arResult['GRID_ID'] . '").getActionManager().act({
				action: "' . WorkgroupUserList::AJAX_ACTION_DELETE_INCOMING_REQUEST . '", 
				userId: ' . $userItem->getId() . ',
			})',
		];
	}

	if (in_array($componentClassName::AVAILABLE_ACTION_PROCESS_INCOMING_REQUEST, $actions, true))
	{
		if ($arResult['GROUP']['SCRUM'] === 'Y')
		{
			$title = Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TEMPLATE_ACTION_ACCEPT_REQUEST_SCRUM_TITLE');
		}
		elseif ($arResult['GROUP']['PROJECT'] === 'Y')
		{
			$title = Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TEMPLATE_ACTION_ACCEPT_REQUEST_PROJECT_TITLE');
		}
		else
		{
			$title = Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TEMPLATE_ACTION_ACCEPT_REQUEST_TITLE');
		}

		$processedRow['actions'][] = [
			'TEXT' => Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TEMPLATE_ACTION_ACCEPT_REQUEST'),
			'TITLE' => $title,
			'ONCLICK' => 'BX.Socialnetwork.WorkgroupUserList.Manager.getById("' . $arResult['GRID_ID'] . '").getActionManager().act({
				action: "' . WorkgroupUserList::AJAX_ACTION_ACCEPT_INCOMING_REQUEST . '", 
				userId: ' . $userItem->getId() . ',
			})',
		];

		if ($arResult['GROUP']['SCRUM'] === 'Y')
		{
			$title = Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TEMPLATE_ACTION_REJECT_REQUEST_SCRUM_TITLE');
		}
		elseif ($arResult['GROUP']['PROJECT'] === 'Y')
		{
			$title = Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TEMPLATE_ACTION_REJECT_REQUEST_PROJECT_TITLE');
		}
		else
		{
			$title = Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TEMPLATE_ACTION_REJECT_REQUEST_TITLE');
		}

		$processedRow['actions'][] = [
			'TEXT' => Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TEMPLATE_ACTION_REJECT_REQUEST'),
			'TITLE' => $title,
			'ONCLICK' => 'BX.Socialnetwork.WorkgroupUserList.Manager.getById("' . $arResult['GRID_ID'] . '").getActionManager().act({
				action: "' . WorkgroupUserList::AJAX_ACTION_REJECT_INCOMING_REQUEST . '", 
				userId: ' . $userItem->getId() . ',
			})',
		];
	}

	unset($processedRow['data']['USER_FIELDS']);

	$arResult['ROWS'][] = $processedRow;
}

unset($arResult['GRID_COLUMNS']);

$arResult['TOOLBAR_BUTTONS'] = [];

if (
	$arResult['GROUP_PERMS']['UserCanInitiate']
	|| $arResult['GROUP_PERMS']['UserCanModifyGroup']
	|| \Bitrix\Socialnetwork\Helper\Workgroup::isCurrentUserModuleAdmin()
)
{
	$arResult['TOOLBAR_BUTTONS'][] = [
		'TYPE' => 'ADD',
		'TITLE' => Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TEMPLATE_BUTTON_INVITE_TITLE'),
		'LINK' => CComponentEngine::MakePathFromTemplate($arParams['PATH_TO_GROUP_INVITE'], [
			'group_id' => $arResult['GROUP']['ID'],
		]),
	];
}
