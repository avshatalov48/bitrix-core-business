<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Config\Option;
use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Socialnetwork\ComponentHelper;
use Bitrix\Socialnetwork\Item\Workgroup;
use Bitrix\Socialnetwork\UserToGroupTable;
use Bitrix\Socialnetwork\Helper\Path;

if (!Loader::includeModule('socialnetwork'))
{
	ShowError(Loc::getMessage('SONET_MODULE_NOT_INSTALL'));
	return false;
}

final class SocialnetworkGroup extends CBitrixComponent implements \Bitrix\Main\Engine\Contract\Controllerable, \Bitrix\Main\Errorable
{
	protected $errorCollection;

	public function configureActions(): array
	{
		return [];
	}

	public function getErrors(): array
	{
		return $this->errorCollection->toArray();
	}

	public function getErrorByCode($code): Error
	{
		return $this->errorCollection->getErrorByCode($code);
	}

	protected function printErrors(): void
	{
		foreach ($this->errorCollection as $error)
		{
			ShowError($error);
		}
	}

	public function onPrepareComponentParams($params = [])
	{
		$this->errorCollection = new ErrorCollection();

		$params['GROUP_ID'] = (int)$params['GROUP_ID'];
		$params['SET_NAV_CHAIN'] = ($params['SET_NAV_CHAIN'] === 'N' ? 'N' : 'Y');

		$this->prepareRequestVarParams($params);
		$this->prepareSearchParams($params);
		$this->preparePathsParams($params);

		$params['DATE_TIME_FORMAT'] = trim(empty($params['DATE_TIME_FORMAT']) ? CDatabase::dateFormatToPHP(CSite::getDateFormat()) : $params['DATE_TIME_FORMAT']);
		$params['SHORT_FORM'] = (($params['SHORT_FORM'] ?? '') === 'Y');
		$params['ITEMS_COUNT'] = ((int)$params['ITEMS_COUNT'] > 0 ? (int)$params['ITEMS_COUNT'] : 6);
		$params['USE_MAIN_MENU'] = (isset($params['USE_MAIN_MENU']) && $params['USE_MAIN_MENU'] === 'Y' ? 'Y' : 'N');
		$params['GROUP_PROPERTY'] = (isset($params['GROUP_PROPERTY']) && is_array($params['GROUP_PROPERTY']) ? $params['GROUP_PROPERTY'] : []);
		$params['NAME_TEMPLATE'] = ($params['NAME_TEMPLATE'] ?? CSite::getNameFormat());
		$params['SHOW_LOGIN'] = (($params['SHOW_LOGIN'] ?? null) !== 'N' ? 'Y' : 'N');

		$tooltipParams = ComponentHelper::checkTooltipComponentParams($params);
		$params['SHOW_FIELDS_TOOLTIP'] = $tooltipParams['SHOW_FIELDS_TOOLTIP'];
		$params['USER_PROPERTY_TOOLTIP'] = $tooltipParams['USER_PROPERTY_TOOLTIP'];

		$params['CAN_OWNER_EDIT_DESKTOP'] = (
			ModuleManager::isModuleInstalled('intranet')
			? (($params['CAN_OWNER_EDIT_DESKTOP'] ?? null) !== 'Y' ? 'N' : 'Y')
			: (($params['CAN_OWNER_EDIT_DESKTOP'] ?? null) !== 'N' ? 'Y' : 'N')
		);

		$params['GROUP_USE_BAN'] = ($params['GROUP_USE_BAN'] ?? null) !== 'N' ? 'Y' : 'N';

		return $params;
	}

	public function executeComponent()
	{
		$this->arResult = $this->prepareData();
		if ($this->arResult === false)
		{
			$this->arResult = [];
		}

		if (!empty($this->getErrors()))
		{
			ob_start();
			$this->printErrors();
			$this->arResult['FatalError'] = ob_get_contents();
			$this->arResult['ErrorList'] = $this->getErrors();
			ob_end_clean();
		}

		$this->includeComponentTemplate();

		return $this->arResult['Group'];
	}

	protected function listKeysSignedParameters()
	{
		return [
			'GROUP_ID',
		];
	}

	public function prepareRequestVarParams(&$componentParams): void
	{
		ComponentHelper::checkEmptyParamString($componentParams, 'USER_VAR', 'user_id');
		ComponentHelper::checkEmptyParamString($componentParams, 'GROUP_VAR', 'group_id');
		ComponentHelper::checkEmptyParamString($componentParams, 'PAGE_VAR', 'page');
	}

	public function prepareSearchParams(&$componentParams): void
	{
		ComponentHelper::checkEmptyParamInteger($componentParams, 'SEARCH_TAGS_PAGE_ELEMENTS', 100);
		ComponentHelper::checkEmptyParamInteger($componentParams, 'SEARCH_TAGS_PERIOD', 0);
		ComponentHelper::checkEmptyParamInteger($componentParams, 'SEARCH_TAGS_FONT_MAX', 50);
		ComponentHelper::checkEmptyParamInteger($componentParams, 'SEARCH_TAGS_FONT_MIN', 10);

		ComponentHelper::checkEmptyParamString($componentParams, 'SEARCH_TAGS_COLOR_NEW', '3E74E6');
		ComponentHelper::checkEmptyParamString($componentParams, 'SEARCH_TAGS_COLOR_OLD', 'C0C0C0');
	}

	private function preparePathsParams(&$params): void
	{
		global $APPLICATION;

		ComponentHelper::checkEmptyParamString($params, 'PATH_TO_USER', htmlspecialcharsbx($APPLICATION->getCurPage() . '?' . $params['PAGE_VAR'] . '=user&' . $params['USER_VAR'] . '=#user_id#'));
		ComponentHelper::checkEmptyParamString($params, 'PATH_TO_GROUP', htmlspecialcharsbx($APPLICATION->getCurPage() . '?' . $params['PAGE_VAR'] . '=group&' . $params['GROUP_VAR'] . '=#group_id#'));
		ComponentHelper::checkEmptyParamString($params, 'PATH_TO_GROUP_EDIT', htmlspecialcharsbx($APPLICATION->getCurPage() . '?' . $params['PAGE_VAR'] . '=group_edit&' . $params['GROUP_VAR'] . '=#group_id#'));

		ComponentHelper::checkEmptyParamString($params, 'PATH_TO_GROUP_INVITE', '');
		if (empty($params['PATH_TO_GROUP_INVITE']))
		{
			$parent = $this->getParent();
			if (is_object($parent) && $parent->__name <> '')
			{
				$params['PATH_TO_GROUP_INVITE'] = $parent->arResult['PATH_TO_GROUP_INVITE'];
			}
		}

		$groupUsers = ($params['group_users'] ?? '');

		ComponentHelper::checkEmptyParamString($params, 'PATH_TO_GROUP_CREATE', htmlspecialcharsbx($APPLICATION->getCurPage() . '?' . $params['PAGE_VAR'] . '=group_create&' . $params['USER_VAR'] . '=#user_id#'));
		ComponentHelper::checkEmptyParamString($params, 'PATH_TO_GROUP_REQUEST_SEARCH', htmlspecialcharsbx($APPLICATION->getCurPage() . '?' . $params['PAGE_VAR'] . '=group_request_search&' . $params['GROUP_VAR'] . '=#group_id#'));
		ComponentHelper::checkEmptyParamString($params, 'PATH_TO_USER_REQUEST_GROUP', htmlspecialcharsbx($APPLICATION->getCurPage() . '?' . $params['PAGE_VAR'] . '=user_request_group&' . $params['USER_VAR'] . '=#user_id#&' . $params['GROUP_VAR'] . '=#group_id#'));
		ComponentHelper::checkEmptyParamString($params, 'PATH_TO_GROUP_REQUESTS', htmlspecialcharsbx($APPLICATION->getCurPage() . '?' . $params['PAGE_VAR'] . '=group_requests&' . $params['GROUP_VAR'] . '=#group_id#'));
		ComponentHelper::checkEmptyParamString($params, 'PATH_TO_GROUP_REQUESTS_OUT', htmlspecialcharsbx($APPLICATION->getCurPage() . '?' . $params['PAGE_VAR'] . '=group_requests_out&' . $params['GROUP_VAR'] . '=#group_id#'));
		ComponentHelper::checkEmptyParamString($params, 'PATH_TO_GROUP_MODS', htmlspecialcharsbx($APPLICATION->getCurPage() . '?' . $params['PAGE_VAR'] . '=group_mods&' . $params['GROUP_VAR'] . '=#group_id#'));
		ComponentHelper::checkEmptyParamString($params, 'PATH_TO_GROUP_USERS', htmlspecialcharsbx($APPLICATION->getCurPage() . '?' . $params['PAGE_VAR'] . '=group_mods&' . $groupUsers . '=#group_id#'));
		ComponentHelper::checkEmptyParamString($params, 'PATH_TO_USER_LEAVE_GROUP', htmlspecialcharsbx($APPLICATION->getCurPage() . '?' . $params['PAGE_VAR'] . '=user_leave_group&' . $groupUsers . '=#group_id#'));
		ComponentHelper::checkEmptyParamString($params, 'PATH_TO_GROUP_FEATURES', htmlspecialcharsbx($APPLICATION->getCurPage() . '?' . $params['PAGE_VAR'] . '=group_features&' . $groupUsers . '=#group_id#'));
		ComponentHelper::checkEmptyParamString($params, 'PATH_TO_GROUP_SUBSCRIBE', htmlspecialcharsbx($APPLICATION->getCurPage() . '?' . $params['PAGE_VAR'] . '=group_subscribe&' . $groupUsers . '=#group_id#'));
		ComponentHelper::checkEmptyParamString($params, 'PATH_TO_GROUP_DELETE', htmlspecialcharsbx($APPLICATION->getCurPage() . '?' . $params['PAGE_VAR'] . '=group_delete&' . $groupUsers . '=#group_id#'));
		ComponentHelper::checkEmptyParamString($params, 'PATH_TO_GROUP_BAN', htmlspecialcharsbx($APPLICATION->getCurPage() . '?' . $params['PAGE_VAR'] . '=group_ban&' . $groupUsers . '=#group_id#'));
		ComponentHelper::checkEmptyParamString($params, 'PATH_TO_MESSAGE_TO_GROUP', htmlspecialcharsbx($APPLICATION->getCurPage() . '?' . $params['PAGE_VAR'] . '=message_to_group&' . $groupUsers . '=#group_id#'));
		ComponentHelper::checkEmptyParamString($params, 'PATH_TO_SEARCH', htmlspecialcharsbx($APPLICATION->getCurPage() . '?' . $params['PAGE_VAR'] . '=search'));
		ComponentHelper::checkEmptyParamString($params, 'PATH_TO_GROUP_LOG', htmlspecialcharsbx($APPLICATION->getCurPage() . '?' . $params['PAGE_VAR'] . '=group-log&' . $groupUsers . '=#group_id#'));
		ComponentHelper::checkEmptyParamString($params, 'PATH_TO_CONPANY_DEPARTMENT', Path::get('department_path_template'));

		ComponentHelper::checkEmptyParamString($params, 'PATH_TO_USER_LOG', (ModuleManager::isModuleInstalled('intranet') ? SITE_DIR . 'company/personal/log/' : SITE_DIR . '/club/log/'));
		$params['~PATH_TO_USER_LOG'] = $params['PATH_TO_USER_LOG'];

		ComponentHelper::checkEmptyParamString($params, 'PATH_TO_POST', (ModuleManager::isModuleInstalled('intranet') ? SITE_DIR . 'company/personal/user/#user_id#/blog/#post_id#/' : SITE_DIR . 'club/personal/user/#user_id#/blog/#post_id#/'));
		$params['~PATH_TO_POST'] = $params['PATH_TO_POST'];
	}

	private function prepareData()
	{
		global $USER, $APPLICATION;

		$result = [];

		$result['IS_IFRAME'] = (
			\Bitrix\Main\Context::getCurrent()->getRequest()->get('IFRAME') === 'Y'
			|| ($this->arParams['IFRAME'] ?? null) === 'Y'
		);

		$groupFields = CSocNetGroup::getById($this->arParams['GROUP_ID']);
		if (
			!$groupFields
			|| !is_array($groupFields)
			|| $groupFields['ACTIVE'] !== 'Y'
		)
		{
			$this->errorCollection[] = new Error(Loc::getMessage('SONET_P_USER_NO_GROUP'));
			return false;
		}

		$result['bExtranet'] = (Loader::includeModule('extranet') && CExtranet::IsExtranetSite());
		$groupFields['IS_EXTRANET_GROUP'] = (
			Loader::includeModule('extranet')
			&& \CExtranet::isExtranetSocNetGroup($groupFields['ID'])
				? 'Y'
				: 'N'
		);

		$groupSitesList = [];
		$res = CSocNetGroup::getSite($groupFields['ID']);
		while ($groupSiteFields = $res->fetch())
		{
			$groupSitesList[] = $groupSiteFields['LID'];
		}

		if (!in_array(SITE_ID, $groupSitesList, true))
		{
			$this->errorCollection[] = new Error(Loc::getMessage('SONET_P_USER_NO_GROUP'));
			return false;
		}

		$result['Subjects'] = [];
		$res = CSocNetGroupSubject::getList(
			[ 'SORT' => 'ASC', 'NAME' => 'ASC' ],
			[ 'SITE_ID' => SITE_ID ],
			false,
			false,
			[ 'ID', 'NAME' ]
		);
		while ($subjectFields = $res->getNext())
		{
			$result['Subjects'][$subjectFields['ID']] = $subjectFields['NAME'];
		}

		if ($groupFields['NUMBER_OF_MODERATORS'] >= 1)
		{
			$groupFields['NUMBER_OF_MODERATORS']--;
		}

		$result['Group'] = $groupFields;

		$result['HideArchiveLinks'] = (
			$result['Group']['CLOSED'] === 'Y'
			&& Option::get('socialnetwork', 'work_with_closed_groups', 'N') !== 'Y'
		);

		$result['CurrentUserPerms'] = \Bitrix\Socialnetwork\Helper\Workgroup::getPermissions([
			'groupId' => $groupFields['ID'],
		]);

		$result['bSubscribed'] = (
			in_array($result['CurrentUserPerms']['UserRole'], UserToGroupTable::getRolesMember(), true)
			&& CSocNetSubscription::isUserSubscribed($USER->getId(), 'SG'.$this->arParams['GROUP_ID'])
		);

		$result['bUserCanRequestGroup'] = null;
		if (
			$result['Group']['VISIBLE'] === 'Y'
			&& !$result['bExtranet']
			&& !$result['HideArchiveLinks']
			&& (
				!$result['CurrentUserPerms']['UserRole']
				|| ($result['CurrentUserPerms']['UserRole'] === UserToGroupTable::ROLE_REQUEST && $result['CurrentUserPerms']['InitiatedByType'] === UserToGroupTable::INITIATED_BY_GROUP)
			)
		)
		{
			$result['bUserCanRequestGroup'] = true;
			$result['bDescriptionOpen'] = true;
		}
		elseif ($USER->isAuthorized())
		{
			$userOptions = CUserOptions::getOption('socialnetwork', 'sonet_group_description', []);
			if (isset($userOptions['state']))
			{
				$result['bDescriptionOpen'] = ($userOptions['state'] === 'Y');
			}
		}
		else
		{
			$result['bDescriptionOpen'] = true;
		}

		$result['bShowRequestSentMessage'] = null;
		//display flag to show information when the group request is sent
		if (
			$result['CurrentUserPerms']['UserRole'] === UserToGroupTable::ROLE_REQUEST
			&& $result['Group']['VISIBLE'] === 'Y' && !$result['HideArchiveLinks'])
		{
			$result['bShowRequestSentMessage'] = (
				$result['CurrentUserPerms']['InitiatedByType'] === UserToGroupTable::INITIATED_BY_GROUP
					? 'G'
					: 'U'
			);
		}

		if (!$result['CurrentUserPerms'] || !$result['CurrentUserPerms']['UserCanViewGroup'])
		{
			$this->errorCollection[] = new Error(Loc::getMessage('SONET_C5_NO_PERMS'));
			return false;
		}

		$this->setPaths($result);

		$group = Workgroup::getById($result['Group']['ID']);
		$result['isScrumProject'] = $group && $group->isScrumProject();

		$result['PageTitle'] = '';
		if ($this->arParams['SET_TITLE'] === 'Y')
		{
			$result['PageTitle'] = $result['Group']['NAME'];
			$APPLICATION->setTitle($result['PageTitle']);
		}

		if (!$this->arParams['SHORT_FORM'] && $this->arParams['SET_NAV_CHAIN'] !== 'N')
		{
			$APPLICATION->addChainItem($result['Group']['NAME']);
		}
		$this->setGroupAvatar($result);
		$this->setGroupProperties($result);

		if (!$this->arParams['SHORT_FORM'])
		{
			$this->setGroupOwner($result);
			$this->setGroupModerators($result);
			$this->setGroupMembers($result);
			$this->setScrumMaster($result);
			$this->setDepartments($result);
			$this->setFeatures($result);
		}

		return $result;
	}

	private function setPaths(&$result): void
	{
		global $USER;

		$result['Urls']['Edit'] = CComponentEngine::makePathFromTemplate($this->arParams['PATH_TO_GROUP_EDIT'], [ 'group_id' => $result['Group']['ID'] ]);
		$result['Urls']['Invite'] = CComponentEngine::makePathFromTemplate($this->arParams['PATH_TO_GROUP_INVITE'], [ 'group_id' => $result['Group']['ID'] ]);
		$result['Urls']['View'] = CComponentEngine::makePathFromTemplate($this->arParams['PATH_TO_GROUP'], [ 'group_id' => $result['Group']['ID'] ]);
		$result['Urls']['UserRequestGroup'] = CComponentEngine::makePathFromTemplate($this->arParams['PATH_TO_USER_REQUEST_GROUP'], [ 'group_id' => $result['Group']['ID'], 'user_id' => $USER->GetID() ]);
		$result['Urls']['GroupRequestSearch'] = CComponentEngine::makePathFromTemplate($this->arParams['PATH_TO_GROUP_REQUEST_SEARCH'], [ 'group_id' => $result['Group']['ID'] ]);
		$result['Urls']['GroupRequests'] = CComponentEngine::makePathFromTemplate($this->arParams['PATH_TO_GROUP_REQUESTS'], [ 'group_id' => $result['Group']['ID'] ]);
		$result['Urls']['GroupRequestsOut'] = CComponentEngine::makePathFromTemplate($this->arParams['PATH_TO_GROUP_REQUESTS_OUT'], [ 'group_id' => $result['Group']['ID'] ]);
		$result['Urls']['GroupMods'] = CComponentEngine::makePathFromTemplate($this->arParams['PATH_TO_GROUP_MODS'], [ 'group_id' => $result['Group']['ID'] ]);
		$result['Urls']['GroupUsers'] = CComponentEngine::makePathFromTemplate($this->arParams['PATH_TO_GROUP_USERS'], [ 'group_id' => $result['Group']['ID'] ]);
		$result['Urls']['UserLeaveGroup'] = CComponentEngine::makePathFromTemplate($this->arParams['PATH_TO_USER_LEAVE_GROUP'], [ 'group_id' => $result['Group']['ID'] ]);
		$result['Urls']['GroupDelete'] = CComponentEngine::makePathFromTemplate($this->arParams['PATH_TO_GROUP_DELETE'], [ 'group_id' => $result['Group']['ID'] ]);
		$result['Urls']['Features'] = CComponentEngine::makePathFromTemplate($this->arParams['PATH_TO_GROUP_FEATURES'], [ 'group_id' => $result['Group']['ID'] ]);
		$result['Urls']['GroupBan'] = CComponentEngine::makePathFromTemplate($this->arParams['PATH_TO_GROUP_BAN'], [ 'group_id' => $result['Group']['ID'] ]);
		$result['Urls']['UserSearch'] = CComponentEngine::makePathFromTemplate($this->arParams['PATH_TO_SEARCH'], []);
		$result['Urls']['Subscribe'] = CComponentEngine::makePathFromTemplate($this->arParams['PATH_TO_GROUP_SUBSCRIBE'], [ 'group_id' => $result['Group']['ID'] ]);
		$result['Urls']['MessageToGroup'] = CComponentEngine::makePathFromTemplate($this->arParams['PATH_TO_MESSAGE_TO_GROUP'], [ 'group_id' => $result['Group']['ID'] ]);
		$result['Urls']['GroupLog'] = CComponentEngine::makePathFromTemplate($this->arParams['PATH_TO_GROUP_LOG'], [ 'group_id' => $result['Group']['ID'] ]);
		$result['Urls']['Copy'] = CComponentEngine::makePathFromTemplate($this->arParams['PATH_TO_GROUP_COPY'] ?? '', [ 'group_id' => $result['Group']['ID'] ]);
	}

	private function setGroupAvatar(&$result): void
	{
		$imageSize = 300;
		if ($this->arParams['SHORT_FORM'])
		{
			$imageSize = 100;
		}

		if ((int)$result['Group']['IMAGE_ID'] <= 0)
		{
			$result['Group']['IMAGE_ID'] = Option::get('socialnetwork', 'default_group_picture', false, SITE_ID);
		}

		$imageFields = CSocNetTools::initImage($result['Group']['IMAGE_ID'], $imageSize, '/bitrix/images/socialnetwork/nopic_group_100.gif', 100, '', false);

		$result['Group']['IMAGE_ID_FILE'] = $imageFields['FILE'];
		$result['Group']['IMAGE_ID_IMG'] = $imageFields['IMG'];
	}

	private function setGroupProperties(&$result): void
	{
		global $USER_FIELD_MANAGER;

		$result['GroupProperties'] = [
			'SHOW' => 'N',
			'DATA' => []
		];

		if (empty($this->arParams['GROUP_PROPERTY']))
		{
			return;
		}

		$userFieldsList = $USER_FIELD_MANAGER->getUserFields('SONET_GROUP', $result['Group']['ID'], LANGUAGE_ID);
		foreach ($userFieldsList as $fieldName => $userField)
		{
			if (!in_array($fieldName, $this->arParams['GROUP_PROPERTY'], true))
			{
				continue;
			}

			$userField['EDIT_FORM_LABEL'] = (
				(string)$userField['EDIT_FORM_LABEL'] !== ''
					? $userField['EDIT_FORM_LABEL']
					: $userField['FIELD_NAME']
			);
			$userField['EDIT_FORM_LABEL'] = htmlspecialcharsEx($userField['EDIT_FORM_LABEL']);
			$userField['~EDIT_FORM_LABEL'] = $userField['EDIT_FORM_LABEL'];
			$userField['PROPERTY_VALUE_LINK'] = '';

			$result['GroupProperties']['DATA'][$fieldName] = $userField;
		}

		if (!empty($result['GroupProperties']['DATA']))
		{
			$result['GroupProperties']['SHOW'] = 'Y';
		}
	}

	private function setGroupOwner(&$result): void
	{
		$result['Owner'] = false;

		$res = CSocNetUserToGroup::getList(
			[ 'ROLE' => 'ASC' ],
			[
				'GROUP_ID' => $result['Group']['ID'],
				'<=ROLE' => UserToGroupTable::ROLE_OWNER,
				'USER_ACTIVE' => 'Y'
			],
			false,
			[ 'nTopCount' => 1 ],
			[ 'ID', 'USER_ID', 'ROLE', 'USER_NAME', 'USER_LAST_NAME', 'USER_SECOND_NAME', 'USER_LOGIN', 'USER_PERSONAL_PHOTO', 'USER_PERSONAL_GENDER', 'USER_WORK_POSITION' ]
		);
		if (!$res)
		{
			return;
		}
		while ($ownerFields = $res->getNext())
		{
			$result['Owner'] = $this->getUserFields($ownerFields);
		}
	}

	private function setGroupModerators(&$result): void
	{
		$result['Moderators'] = false;

		$res = CSocNetUserToGroup::getList(
			[ 'ROLE' => 'ASC' ],
			[
				'GROUP_ID' => $result['Group']['ID'],
				'=ROLE' => UserToGroupTable::ROLE_MODERATOR,
				'USER_ACTIVE' => 'Y'
			],
			false,
			[ 'nTopCount' => $this->arParams['ITEMS_COUNT'] ],
			[ 'ID', 'USER_ID', 'ROLE', 'USER_NAME', 'USER_LAST_NAME', 'USER_SECOND_NAME', 'USER_LOGIN', 'USER_PERSONAL_PHOTO', 'USER_PERSONAL_GENDER', 'USER_WORK_POSITION' ]
		);
		if (!$res)
		{
			return;
		}

		$result['Moderators'] = [];

		$result['Moderators']['List'] = false;
		while ($moderatorFields = $res->getNext())
		{
			if ($result['Moderators']['List'] === false)
			{
				$result['Moderators']['List'] = [];
			}

			$result['Moderators']['List'][] = $this->getUserFields($moderatorFields);
		}
	}

	private function setGroupMembers(&$result): void
	{
		$result['Members'] = false;

		$res = CSocNetUserToGroup::getList(
			[ 'RAND' => 'ASC' ],
			[
				'GROUP_ID' => $result['Group']['ID'],
				'<=ROLE' => SONET_ROLES_USER,
				'USER_ACTIVE' => 'Y'
			],
			false,
			[ 'nTopCount' => $this->arParams['ITEMS_COUNT'] ],
			[ 'ID', 'USER_ID', 'ROLE', 'USER_NAME', 'USER_LAST_NAME', 'USER_SECOND_NAME', 'USER_LOGIN', 'USER_PERSONAL_PHOTO', 'USER_PERSONAL_GENDER', 'USER_WORK_POSITION' ]
		);
		if (!$res)
		{
			return;
		}

		$result['Members'] = [];
		$result['Members']['List'] = false;

		while ($memberFields = $res->getNext())
		{
			if ($result['Members']['List'] === false)
			{
				$result['Members']['List'] = [];
			}

			$result['Members']['List'][] = $this->getUserFields($memberFields);
		}
	}

	private function setScrumMaster(&$result): void
	{
		$result['ScrumMaster'] = [];

		$scrumMasterId = (int)$result['Group']['SCRUM_MASTER_ID'];
		if ($scrumMasterId <= 0)
		{
			return;
		}

		$res = \Bitrix\Main\UserTable::getList([
			'filter' => [
				'=ID' => $scrumMasterId,
			],
			'select' => [
				'USER_ID' => 'ID',
				'USER_NAME' => 'NAME',
				'USER_LAST_NAME' => 'LAST_NAME',
				'USER_SECOND_NAME' => 'SECOND_NAME',
				'USER_LOGIN' => 'LOGIN',
				'USER_PERSONAL_PHOTO' => 'PERSONAL_PHOTO',
				'USER_PERSONAL_GENDER' => 'PERSONAL_GENDER',
				'USER_WORK_POSITION' => 'WORK_POSITION',
			],
		]);
		$scrumMasterFields = $res->fetch();
		if (empty($scrumMasterFields))
		{
			return;
		}

		$result['ScrumMaster'] = $this->getUserFields($scrumMasterFields);
	}

	private function setDepartments(&$result): void
	{
		if (
			empty($result['Group']['UF_SG_DEPT'])
			|| !is_array($result['Group']['UF_SG_DEPT'])
			|| !Loader::includeModule('intranet')
		)
		{
			return;
		}

		$departmentsData = CIntranetUtils::getDepartmentsData($result['Group']['UF_SG_DEPT']);
		if (!empty($departmentsData))
		{
			$result['GroupDepartments'] = [];
			foreach ($departmentsData as $departmentId => $departmentName)
			{
				$result['GroupDepartments'][] = [
					'ID' => $departmentId,
					'NAME' => $departmentName,
					'URL' => str_replace('#ID#', $departmentId, $this->arParams['PATH_TO_CONPANY_DEPARTMENT']),
				];
			}
		}
	}

	private function getUserFields($userFields): array
	{
		global $USER;

		$profileUrl = CComponentEngine::makePathFromTemplate($this->arParams['PATH_TO_USER'], [ 'user_id' => $userFields['USER_ID'] ]);
		$canViewProfile = CSocNetUserPerms::canPerformOperation($USER->getId(), $userFields['USER_ID'], 'viewprofile', CSocNetUser::isCurrentUserModuleAdmin());
		$imageFields = $this->getUserAvatarFields($userFields, $profileUrl, $canViewProfile);
		$extranetUserIdList = self::getExtranetUserIdList([
			'groupId' => $this->arParams['GROUP_ID'],
		]);

		$result = [
			'ID' => $userFields['ID'] ?? 0,
			'USER_ID' => $userFields['USER_ID'],
			'USER_NAME' => $userFields['USER_NAME'],
			'USER_LAST_NAME' => $userFields['USER_LAST_NAME'],
			'USER_SECOND_NAME' => $userFields['USER_SECOND_NAME'],
			'USER_WORK_POSITION' => $userFields['USER_WORK_POSITION'],
			'USER_LOGIN' => $userFields['USER_LOGIN'],
			'USER_PERSONAL_PHOTO' => $userFields['USER_PERSONAL_PHOTO'],
			'USER_PERSONAL_PHOTO_FILE' => $imageFields['FILE'],
			'USER_PERSONAL_PHOTO_IMG' => $imageFields['IMG'],
			'USER_PROFILE_URL' => $profileUrl,
			'SHOW_PROFILE_LINK' => $canViewProfile,
			'USER_IS_EXTRANET' => (
				!empty($extranetUserIdList)
				&& in_array((int)$userFields['USER_ID'], $extranetUserIdList, true)
					? 'Y'
					: 'N'
			),
		];

		if ((int)$userFields['USER_PERSONAL_PHOTO'] > 0)
		{
			$resizedImageFields = \CFile::resizeImageGet(
				(int)$userFields['USER_PERSONAL_PHOTO'],
				[
					'width' => 100,
					'height' => 100,
				],
				BX_RESIZE_IMAGE_EXACT
			);
		}
		else
		{
			$resizedImageFields = ['src' => ''];
		}

		if (!is_array($result['USER_PERSONAL_PHOTO_FILE']))
		{
			$result['USER_PERSONAL_PHOTO_FILE'] = [];
		}
		$result['USER_PERSONAL_PHOTO_FILE']['SRC_RESIZED'] = $resizedImageFields['src'];
		$result['NAME_FORMATTED'] = \CUser::formatName(
			$this->arParams['NAME_TEMPLATE'],
			[
				'NAME' => htmlspecialcharsBack($result['USER_NAME']),
				'LAST_NAME' => htmlspecialcharsBack($result['USER_LAST_NAME']),
				'SECOND_NAME' => htmlspecialcharsBack($result['USER_SECOND_NAME']),
				'LOGIN' => htmlspecialcharsBack($result['USER_LOGIN']),
			],
			true
		);

		return $result;
	}

	private function getUserAvatarFields($userFields, $profileUrl, $canViewProfile): array
	{
		if ((int)$this->arParams['THUMBNAIL_LIST_SIZE'] > 0)
		{
			if ((int)$userFields['USER_PERSONAL_PHOTO'] <= 0)
			{
				switch ($userFields['USER_PERSONAL_GENDER'])
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
				$userFields['USER_PERSONAL_PHOTO'] = Option::get('socialnetwork', 'default_user_picture_' . $suffix, false, SITE_ID);
			}
			$imageFields = CSocNetTools::initImage($userFields['USER_PERSONAL_PHOTO'], $this->arParams['THUMBNAIL_LIST_SIZE'], '/bitrix/images/socialnetwork/nopic_30x30.gif', 30, $profileUrl, $canViewProfile);
		}
		else
		{
			$imageFields = CSocNetTools::initImage($userFields['USER_PERSONAL_PHOTO'], 50, '/bitrix/images/socialnetwork/nopic_user_50.gif', 50, $profileUrl, $canViewProfile);
		}

		return $imageFields;
	}

	private function setFeatures(&$result): void
	{
		global $USER;

		$result['ActiveFeatures'] = CSocNetFeatures::getActiveFeaturesNames(SONET_ENTITY_GROUP, $result['Group']['ID']);

		//Blog
		$result['BLOG'] = [
			'SHOW' => false,
			'TITLE' => Loc::getMessage('SONET_C6_BLOG_T'),
		];
		if (
			array_key_exists('blog', $result['ActiveFeatures'])
			&& Loader::includeModule('blog')
			&& (
				CSocNetFeaturesPerms::CanPerformOperation($USER->getID(), SONET_ENTITY_GROUP, $result['Group']['ID'], 'blog', 'view_post', CSocNetUser::isCurrentUserModuleAdmin())
				|| CMain::GetGroupRight('forum') >= 'W'
			)
		)
		{
			$result['BLOG']['SHOW'] = true;
			if ((string)$result['ActiveFeatures']['blog'] !== '')
			{
				$result['BLOG']['TITLE'] = $result['ActiveFeatures']['blog'];
			}
		}

		$result['forum'] = [
			'SHOW' => false,
			'TITLE' => Loc::getMessage('SONET_C6_FORUM_T'),
		];
		if (
			array_key_exists('forum', $result['ActiveFeatures'])
			&& Loader::includeModule('forum')
			&& (
				CSocNetFeaturesPerms::CanPerformOperation($USER->GetID(), SONET_ENTITY_GROUP, $result['Group']['ID'], 'forum', 'view', CSocNetUser::isCurrentUserModuleAdmin())
				|| CMain::getGroupRight('forum') >= 'W'
			)
		)
		{
			$result['forum']['SHOW'] = true;
			if ((string)$result['ActiveFeatures']['forum'] !== '')
			{
				$result['forum']['TITLE'] = $result['ActiveFeatures']['forum'];
			}
		}

		$result['tasks'] = [
			'SHOW' => false,
			'TITLE' => Loc::getMessage('SONET_C6_TASKS_T'),
		];
		if (
			array_key_exists('tasks', $result['ActiveFeatures'])
			&& Loader::includeModule('intranet')
			&& (
				CSocNetFeaturesPerms::CanPerformOperation($USER->getId(), SONET_ENTITY_GROUP, $result['Group']['ID'], 'tasks', 'view', CSocNetUser::isCurrentUserModuleAdmin())
				|| CMain::getGroupRight('intranet') >= 'W'
			)
		)
		{
			$result['tasks']['SHOW'] = true;
			if ((string)$result['ActiveFeatures']['tasks'] !== '')
			{
				$result['tasks']['TITLE'] = $result['ActiveFeatures']['tasks'];
			}
		}
	}

	private static function getExtranetUserIdList(array $params = []): array
	{
		static $cache = [];

		$groupId = (int)($params['groupId'] ?? 0);
		if ($groupId <= 0)
		{
			return [];
		}

		if (!isset($cache[$groupId]))
		{
			$cache[$groupId] = [];

			if (Loader::includeModule('extranet'))
			{
				$userIdList = [];
				$res = UserToGroupTable::getList([
					'filter' => [
						'GROUP_ID' => $groupId,
						'@ROLE' => UserToGroupTable::getRolesMember(),
					],
					'select' => [ 'USER_ID' ],
				]);
				while ($relationFields = $res->fetch())
				{
					$userIdList[] = (int)$relationFields['USER_ID'];
				}

				if (!empty($userIdList))
				{
					$res = CUser::getList(
						'ID',
						'asc',
						[
							'ID' => implode('|', $userIdList),
							'GROUPS_ID' => [ CExtranet::getExtranetUserGroupId() ],
							'UF_DEPARTMENT' => false,
						],
						[
							'FIELDS' => [ 'ID' ],
						]
					);
					while ($userFields = $res->fetch())
					{
						$cache[$groupId][] = (int)$userFields['ID'];
					}
				}
			}
		}

		return $cache[$groupId];
	}

}
