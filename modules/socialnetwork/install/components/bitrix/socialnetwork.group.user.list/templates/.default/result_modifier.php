<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

use Bitrix\Main\Localization\Loc;
use Bitrix\Socialnetwork\Component\WorkgroupUserList;
use Bitrix\Socialnetwork\UserToGroupTable;

Loc::loadMessages(__FILE__);

$gridColumns = $arResult['GRID_COLUMNS'];

$connectedDepartmentsIdList = [];
if (
	\Bitrix\Main\ModuleManager::isModuleInstalled('intranet')
	&& \Bitrix\Main\Filter\UserDataProvider::getExtranetAvailability()
)
{
	$connectedDepartmentsIdList = array_map(static function($item) {
		return (int)$item['ID'];
	}, $arResult['CONNECTED_DEPARTMENTS_LIST']);
}

$lastConnectedDepartmentId = 0;

$gridData = $arResult['ROWS'];
$arResult['ROWS'] = [];

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

			$arResult['ROWS'][] = [
				'id' => 'connected_dept' . $connectedUserDepartmentId,
				'has_child' => true,
				'parent_id' => 0,
				'custom' => '<div class="sonet-group-user-grid-wrapper"><a href="' .
					htmlspecialcharsbx($connectedDepartmentData['URL']) .
					'" class="">' .
					htmlspecialcharsEx($connectedDepartmentData['NAME']) .
					'</a>&nbsp;<span class="sonet-group-user-grid-action" onclick="BX.Socialnetwork.WorkgroupUserList.Manager.getById().getActionManager().disconnectDepartment({
						id: ' . $connectedUserDepartmentId . '
					})">x</span></div>',
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
				$processedRow['data'][$column] = WorkgroupUserList::getDepartmentValue([
					'FIELDS' => $userItem,
					'PATH' => ($arResult['EXTRANET_SITE'] !== 'Y' ? $arParams['PATH_TO_DEPARTMENT'] : ''),
				]);
				break;
			case 'FULL_NAME':
				$processedRow['data'][$column] = WorkgroupUserList::getNameFormattedValue([
					'FIELDS' => [
						'ID' => $userItem->getId(),
						'NAME' => $userItem->getName(),
						'LAST_NAME' => $userItem->getLastName(),
						'SECOND_NAME' => $userItem->getSecondName(),
						'LOGIN' => $userItem->getLogin(),
					],
					'PATH' => $arParams['PATH_TO_USER'],
				]);
				break;
			case 'PHOTO':
				$processedRow['data'][$column] = WorkgroupUserList::getPhotoValue([
					'FIELDS' => [
						'ID' => $userItem->getId(),
						'PERSONAL_PHOTO' => $userItem->getPersonalPhoto(),
						'PERSONAL_GENDER' => $userItem->getPersonalGender(),
					],
					'PATH' => $arParams['PATH_TO_USER'],
				]);
				break;
			case 'ROLE':
				$role = $item->getRole();
				if (in_array($role, UserToGroupTable::getRolesMember(), true))
				{
					$processedRow['data'][$column] = Bitrix\Main\Localization\Loc::getMessage(
						'SOCIALNETWORK_GROUP_USER_LIST_TEMPLATE_ROLE_' . $item->getRole()
						. ($arResult['GROUP']['PROJECT'] === 'Y' ? '_PROJECT' : '')
						. ($item->getAutoMember() ? '_AUTO' : '')
					);
				}
				elseif ($role === UserToGroupTable::ROLE_REQUEST)
				{
					$processedRow['data'][$column] = Bitrix\Main\Localization\Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TEMPLATE_ROLE_REQUEST_' . $item->getInitiatedByType());
				}
				elseif ($role === UserToGroupTable::ROLE_BAN)
				{
					$processedRow['data'][$column] = Bitrix\Main\Localization\Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TEMPLATE_ROLE_BAN');
				}

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

	if (in_array('view_profile', $actions, true))
	{
		$processedRow['actions'][] = [
			'TEXT' => Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TEMPLATE_ACTION_VIEW'),
			'TITLE' => Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TEMPLATE_ACTION_VIEW_TITLE'),
			'ONCLICK' => 'BX.Socialnetwork.WorkgroupUserList.Manager.getById().getActionManager().viewProfile({
				pathToUser: "'. htmlspecialcharsbx($arParams['PATH_TO_USER']) . '",
				userId: ' . $userItem->getId() . '
			})',
			'DEFAULT' => true
		];
	}

	if (in_array('set_owner', $actions, true))
	{
		$processedRow['actions'][] = [
			'TEXT' => (
				$arResult['GROUP']['PROJECT'] === 'Y'
					? Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TEMPLATE_ACTION_SET_OWNER_PROJECT')
					: Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TEMPLATE_ACTION_SET_OWNER')
			),
			'TITLE' => (
				$arResult['GROUP']['PROJECT'] === 'Y'
					? Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TEMPLATE_ACTION_SET_OWNER_PROJECT_TITLE')
					: Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TEMPLATE_ACTION_SET_OWNER_TITLE')
			),
			'ONCLICK' => 'BX.Socialnetwork.WorkgroupUserList.Manager.getById().getActionManager().act("' . WorkgroupUserList::AJAX_ACTION_SET_OWNER . '", ' . $userItem->getId() .')',
		];
	}

	if (in_array('set_moderator', $actions, true))
	{
		$processedRow['actions'][] = [
			'TEXT' => (
				$arResult['GROUP']['PROJECT'] === 'Y'
					? Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TEMPLATE_ACTION_SET_MODERATOR_PROJECT')
					: Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TEMPLATE_ACTION_SET_MODERATOR')
			),
			'TITLE' => (
				$arResult['GROUP']['PROJECT'] === 'Y'
					? Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TEMPLATE_ACTION_SET_MODERATOR_PROJECT_TITLE')
					: Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TEMPLATE_ACTION_SET_MODERATOR_TITLE')
			),
			'ONCLICK' => 'BX.Socialnetwork.WorkgroupUserList.Manager.getById().getActionManager().act("' . WorkgroupUserList::AJAX_ACTION_SET_MODERATOR . '", ' . $userItem->getId() .')',
		];
	}

	if (in_array('remove_moderator', $actions, true))
	{
		$processedRow['actions'][] = [
			'TEXT' => (
				$arResult['GROUP']['PROJECT'] === 'Y'
					? Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TEMPLATE_ACTION_REMOVE_MODERATOR_PROJECT')
					: Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TEMPLATE_ACTION_REMOVE_MODERATOR')
			),
			'TITLE' => (
				$arResult['GROUP']['PROJECT'] === 'Y'
					? Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TEMPLATE_ACTION_REMOVE_MODERATOR_PROJECT_TITLE')
					: Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TEMPLATE_ACTION_REMOVE_MODERATOR_TITLE')
			),
			'ONCLICK' => 'BX.Socialnetwork.WorkgroupUserList.Manager.getById().getActionManager().act("' . WorkgroupUserList::AJAX_ACTION_REMOVE_MODERATOR . '", ' . $userItem->getId() .')',
		];
	}

	if (in_array('exclude', $actions, true))
	{
		$processedRow['actions'][] = [
			'TEXT' => Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TEMPLATE_ACTION_EXCLUDE'),
			'TITLE' => (
			$arResult['GROUP']['PROJECT'] === 'Y'
				? Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TEMPLATE_ACTION_EXCLUDE_PROJECT_TITLE')
				: Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TEMPLATE_ACTION_EXCLUDE_TITLE')
			),
			'ONCLICK' => 'BX.Socialnetwork.WorkgroupUserList.Manager.getById().getActionManager().act("' . WorkgroupUserList::AJAX_ACTION_EXCLUDE . '", ' . $userItem->getId() .')',
		];
	}

	if (in_array('delete_outgoing_request', $actions, true))
	{
		$processedRow['actions'][] = [
			'TEXT' => Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TEMPLATE_ACTION_DELETE_REQUEST'),
			'TITLE' => (
				$arResult['GROUP']['PROJECT'] === 'Y'
					? Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TEMPLATE_ACTION_DELETE_REQUEST_PROJECT_TITLE')
				: Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TEMPLATE_ACTION_DELETE_REQUEST_TITLE')
			),
			'ONCLICK' => 'BX.Socialnetwork.WorkgroupUserList.Manager.getById().getActionManager().act("' . WorkgroupUserList::AJAX_ACTION_DELETE_OUTGOING_REQUEST . '", ' . $userItem->getId() .')',
		];
	}

	unset($processedRow['data']['USER_FIELDS']);

	$arResult['ROWS'][] = $processedRow;
}

unset($arResult['GRID_COLUMNS']);

$arResult['TOOLBAR_MENU'] = [];

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
