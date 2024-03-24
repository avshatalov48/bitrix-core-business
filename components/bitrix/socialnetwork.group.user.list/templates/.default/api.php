<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Web\Uri;
use Bitrix\Socialnetwork\Component\WorkgroupUserList;
use Bitrix\Socialnetwork\UserToGroupTable;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class WorkgroupUserListTemplate
{
	public static function getNameFormattedValue(array $params = []): string
	{
		static $nameTemplate = null;

		$result = '';

		$userFields = ($params['FIELDS'] ?? []);

		$path = ($params['PATH'] ?? '');

		if (empty($userFields))
		{
			return $result;
		}

		if ($nameTemplate === null)
		{
			$nameTemplate = CSite::getNameFormat();
		}

		$result = '<span class="sonet-group-user-grid-username-cont">' .
				'<span class="sonet-group-user-grid-username-name">' . CUser::formatName($nameTemplate, $userFields, true) . '</span>' .
				'<span class="sonet-group-user-grid-username-position">' . htmlspecialcharsbx($userFields['WORK_POSITION']) . '</span>' .
			'</span>';

		$result = self::getPhotoValue($params) . $result;

		$classList = [ 'sonet-group-user-grid-username' ];
		$classList[] = (
			(string)$userFields['WORK_POSITION'] !== ''
				? '--two-strings'
				: '--one-string'
		);

		if ($path !== '')
		{
			$result = '<a class="' . implode(' ', $classList) . '" href="'.htmlspecialcharsbx(str_replace([ '#ID#', '#USER_ID#', '#user_id#' ], $userFields['ID'], $path)).'">' . $result . '</a>';
		}
		else
		{
			$result = '<span class="' . implode(' ', $classList) . '">' . $result . '</span>';
		}

		return $result;
	}

	public static function getPhotoValue(array $params = []): string
	{
		$result = '<div class="sonet-group-user-grid-avatar ui-icon ui-icon-common-user"><i></i></div>';

		$userFields = ($params['FIELDS'] ?? []);

		if (empty($userFields))
		{
			return $result;
		}

		$personalPhoto = $userFields['PERSONAL_PHOTO'];
		if (empty($personalPhoto))
		{
			switch ($userFields['PERSONAL_GENDER'])
			{
				case 'M':
					$suffix = 'male';
					break;
				case 'F':
					$suffix = 'female';
					break;
				default:
					$suffix = 'unknown';
			}
			$personalPhoto = Option::get('socialnetwork', 'default_user_picture_' . $suffix, false, SITE_ID);
		}

		if (empty($personalPhoto))
		{
			return $result;
		}

		$file = CFile::getFileArray($personalPhoto);
		if (!empty($file))
		{
			$fileResized = CFile::resizeImageGet(
				$file,
				[
					'width' => 100,
					'height' => 100,
				]
			);

			$result = "<div class=\"sonet-group-user-grid-avatar ui-icon ui-icon-common-user\"><i style=\"background-image: url('" . Uri::urnEncode($fileResized['src']) . "'); background-size: cover\"></i></div>";
		}

		return $result;
	}

	public static function getDepartmentValue(array $params = []): string
	{
		return (
		Loader::includeModule('intranet')
			? \Bitrix\Intranet\Component\UserList::getDepartmentValue($params)
			: ''
		);
	}

	public static function getRoleValue(array $params = []): string
	{
		$result = '';

		$fields = ($params['FIELDS'] ?? []);
		$groupFields = ($params['GROUP'] ?? []);

		if ($groupFields['SCRUM'] === 'Y')
		{
			$suffix = '_SCRUM2';
		}
		elseif ($groupFields['PROJECT'] === 'Y')
		{
			$suffix = '_PROJECT';
		}
		else
		{
			$suffix = '';
		}

		if (empty($fields))
		{
			return $result;
		}

		$role = $fields['ROLE'];

		if (in_array($role, UserToGroupTable::getRolesMember(), true))
		{
			$isScrumMaster = (int)$fields['ID'] === (int)$groupFields['SCRUM_MASTER_ID'];
			$parts = [];

			if (!(
				$isScrumMaster
				&& $role === UserToGroupTable::ROLE_MODERATOR
				&& $fields['AUTO_MEMBER'] === false
			))
			{
				$parts = [
					Loc::getMessage(
						'SOCIALNETWORK_GROUP_USER_LIST_TEMPLATE_ROLE_' . $role
						. $suffix
						. ($fields['AUTO_MEMBER'] ? '_AUTO' : '')
					),
				];
			}

			if ($isScrumMaster)
			{
				$parts[] = Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TEMPLATE_ROLE_SCRUM_MASTER');
			}

			$result = implode(', ', $parts);
		}
		elseif ($role === UserToGroupTable::ROLE_REQUEST)
		{
			$type = $fields['INITIALIZED_BY_TYPE'];
			$result = Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TEMPLATE_ROLE_REQUEST_' . $type);
			$classList = [
				'ui-label',
				'sonet-group-user-grid-request'
			];

			if ($type === UserToGroupTable::INITIATED_BY_GROUP)
			{
				$classList[] = '--request-green';
			}
			elseif ($type === UserToGroupTable::INITIATED_BY_USER)
			{
				$classList[] = '--request-red';
			}

			$result = '<span class="' . implode(' ', $classList) . '"><span class="ui-label-inner">' . $result . '</span></span>';

			$dateCreate = $fields['DATE_CREATE'];
			if (!empty($dateCreate))
			{
				$result .= '<span class="sonet-group-user-grid-request-date">' . $dateCreate . '</span>';
			}
		}
		elseif ($role === UserToGroupTable::ROLE_BAN)
		{
			$result = Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TEMPLATE_ROLE_BAN');
		}

		return '<span class="sonet-group-user-grid-request-cont">' . $result . '</span>';
	}

	public static function getActionsValue(array $params = []): string
	{
		$result = '';

		$actions = ($params['ACTIONS'] ?? []);
		$fields = ($params['FIELDS'] ?? []);

		if (empty($fields))
		{
			return $result;
		}

		$actionsList = [];

		if (in_array(WorkgroupUserList::AVAILABLE_ACTION_PROCESS_INCOMING_REQUEST, $actions, true))
		{
			$actionsList[] = '<span class="ui-btn ui-btn-sm ui-btn-success ui-btn-round ui-btn-icon-done" data-bx-action="' . WorkgroupUserList::AJAX_ACTION_ACCEPT_INCOMING_REQUEST. '" data-bx-user-id="' . $fields['ID'] . '">' . Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TEMPLATE_ROW_ACTION_ACCEPT_INCOMING_REQUEST') . '</span>';
			$actionsList[] = '<span class="ui-btn ui-btn-sm ui-btn-light-border ui-btn-round ui-btn-icon-cancel sonet-group-user-grid-action__btn-cancel" data-bx-action="' . WorkgroupUserList::AJAX_ACTION_REJECT_INCOMING_REQUEST. '" data-bx-user-id="' . $fields['ID'] . '"  title="' . Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TEMPLATE_ROW_ACTION_REJECT_INCOMING_REQUEST') . '" ></span>';
		}

		if (in_array(WorkgroupUserList::AVAILABLE_ACTION_REINVITE, $actions, true))
		{
			$actionsList[] = '<span class="sonet-group-user-grid-action" data-bx-action="' . WorkgroupUserList::AJAX_ACTION_REINVITE. '" data-bx-user-id="' . $fields['ID'] . '">' . Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TEMPLATE_ROW_ACTION_REINVITE') . '</span>';
		}

		return (
			!empty($actionsList)
				? '<span class="sonet-group-user-grid-action-cont">' . implode('', $actionsList) . '</span>'
				: ''
		);
	}

}

