<?php

namespace Bitrix\Socialnetwork\Component;

use Bitrix\Intranet\Internals\ThemeTable;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Socialnetwork\UserToGroupTable;
use Bitrix\Intranet\Integration\Templates\Bitrix24\ThemePicker;

class WorkgroupForm extends \CBitrixComponent
{
	protected function prepareData(): array
	{
		$result = [
			'preset' => (!empty($_GET['preset']) ? $_GET['preset'] : false),
			'bVarsFromForm' => false,
			'templateEditMode' => 'N',
			'destinationContextOwner' => 'GROUP_INVITE_OWNER',
			'destinationContextModerators' => 'GROUP_INVITE_MODERATORS',
			'destinationContextUsers' => 'GROUP_INVITE',
			'isCurrentUserAdmin' => \CSocNetUser::isCurrentUserModuleAdmin(),
		];

		$this->processGroupProperties($result);
		$this->processParams($result);
		$this->processRequest($result);
		$this->getThemePickerData($result);

		return $result;
	}

	protected function processParams(array &$result = []): void
	{
		if (empty($this->arParams['TAB']))
		{
			return;
		}

		$result['TAB'] = strtolower($this->arParams['TAB']);
	}

	protected function processRequest(array &$result = []): void
	{
		$request = \Bitrix\Main\Context::getCurrent()->getRequest();

		$result['IS_IFRAME'] = ($request->get('IFRAME') === 'Y' || $this->arParams['IFRAME'] === 'Y');
		$result['IS_POPUP'] = ($request->get('POPUP') === 'Y');

		if (in_array($request->get('CALLBACK'), [ 'REFRESH', 'GROUP' ]))
		{
			$result['CALLBACK'] = $request->get('CALLBACK');
		}

		if (!empty($request->getPost('TAB')))
		{
			$result['TAB'] = $request->getPost('TAB');
		}
		elseif (!empty($request->get('tab')))
		{
			$result['TAB'] = $request->get('tab');
		}
	}

	protected function getThemePickerData(array &$result = []): void
	{
		global $USER;

		$groupId = (isset($this->arParams['GROUP_ID']) ? (int)$this->arParams['GROUP_ID'] : 0);

		if (
			SITE_TEMPLATE_ID !== 'bitrix24'
			|| !Loader::includeModule('intranet')
		)
		{
			return;
		}

		$result['showThemePicker'] = (
			$result['IS_IFRAME']
			&& (empty($result['TAB']) || $result['TAB'] === 'edit')
			&& ($this->arParams['THEME_ENTITY_TYPE'] ?? null) === 'SONET_GROUP'
		);

		$result['themePickerData'] = [];

		if (!$result['showThemePicker'])
		{
			return;
		}

		if ($groupId > 0)
		{
			$themePicker = new ThemePicker(SITE_TEMPLATE_ID, false, $USER->getId(), ThemePicker::ENTITY_TYPE_SONET_GROUP, $groupId);
			$themeId = $themePicker->getCurrentThemeId();
			$themeUserId = false;
			if ($themeId)
			{
				$res = ThemeTable::getList([
					'filter' => [
						'=ENTITY_TYPE' => $themePicker->getEntityType(),
						'ENTITY_ID' => $themePicker->getEntityId(),
						'=CONTEXT' => $themePicker->getContext(),
					],
					'select' => [ 'USER_ID' ],
				]);
				if (
					($themeFields = $res->fetch())
					&& (int)$themeFields['USER_ID'] > 0
				)
				{
					$themeUserId = (int)$themeFields['USER_ID'];
				}
			}
			$result['themePickerData'] = $themePicker->getTheme($themeId, $themeUserId);
		}
		elseif ($themePicker = new ThemePicker(SITE_TEMPLATE_ID))
		{
			$themesList = $themePicker->getPatternThemes();
			$result['themePickerData'] = $themesList[array_rand($themesList)];
		}
	}

	protected function processGroupProperties(array &$result = []): void
	{
		global $USER_FIELD_MANAGER;

		$result['GROUP_PROPERTIES'] = $USER_FIELD_MANAGER->getUserFields('SONET_GROUP', 0, LANGUAGE_ID);

		foreach ($result['GROUP_PROPERTIES'] as $field => $userFieldFata)
		{
			if (
				!empty($userFieldFata['EDIT_IN_LIST'])
				&& $userFieldFata['EDIT_IN_LIST'] === 'N'
				&& (
					empty($userFieldFata['MANDATORY'])
					|| $userFieldFata['MANDATORY'] !== 'Y'
				)
			)
			{
				unset($result['GROUP_PROPERTIES'][$field]);
				continue;
			}

			$result['GROUP_PROPERTIES'][$field]['EDIT_FORM_LABEL'] = (
				(string)$userFieldFata['EDIT_FORM_LABEL'] !== ''
					? $userFieldFata['EDIT_FORM_LABEL']
					: $userFieldFata['FIELD_NAME']
			);
			$result['GROUP_PROPERTIES'][$field]['EDIT_FORM_LABEL'] = htmlspecialcharsEx($result['GROUP_PROPERTIES'][$field]['EDIT_FORM_LABEL']);
			$result['GROUP_PROPERTIES'][$field]['~EDIT_FORM_LABEL'] = $result['GROUP_PROPERTIES'][$field]['EDIT_FORM_LABEL'];
		}
	}

	public static function processWorkgroupData(&$groupId, &$groupPropertiesList = [], &$groupData = [], $tab = false): void
	{
		global $USER;

		$currentUserId = (int)$USER->getId();
		$currentAdmin = \CSocNetUser::isCurrentUserModuleAdmin();
		$groupFields = \CSocNetGroup::getById($groupId);

		$canUpdate = \Bitrix\Socialnetwork\Helper\Workgroup\Access::canUpdate([
			'groupId' => $groupId,
		]);

		if (
			$groupFields
			&& (
				(
					$tab === 'edit'
					&& $canUpdate
				)
				|| (
					$tab === 'invite'
					&& (
						$currentAdmin
						|| \CSocNetGroup::canUserInitiate($currentUserId, $groupId)
					)
				)
			)
		)
		{
			$groupData['NAME'] = $groupFields['NAME'];
			$groupData['DESCRIPTION'] = $groupFields["DESCRIPTION"];
			$groupData['IMAGE_ID_DEL'] = 'N';
			$groupData['SUBJECT_ID'] = $groupFields['SUBJECT_ID'];
			$groupData['VISIBLE'] = $groupFields['VISIBLE'];
			$groupData['OPENED'] = $groupFields['OPENED'];
			$groupData['CLOSED'] = $groupFields['CLOSED'];
			$groupData['PROJECT'] = ($groupFields['PROJECT'] === 'Y' ? 'Y' : 'N');
			$groupData['PROJECT_DATE_START'] = ($groupData['PROJECT'] === 'Y' ? $groupFields['PROJECT_DATE_START'] : false);
			$groupData['PROJECT_DATE_FINISH'] = ($groupData['PROJECT'] === 'Y' ? $groupFields['PROJECT_DATE_FINISH'] : false);
			$groupData['KEYWORDS'] = $groupFields['KEYWORDS'];
			$groupData['OWNER_ID'] = $groupFields['OWNER_ID'];
			$groupData['INITIATE_PERMS'] = $groupFields['INITIATE_PERMS'];
			$groupData['SPAM_PERMS'] = $groupFields['SPAM_PERMS'];
			$groupData['IMAGE_ID'] = $groupFields['IMAGE_ID'];
			$groupData['IMAGE_ID_FILE'] = \CFile::getFileArray($groupFields['IMAGE_ID']);
			$groupData['IMAGE_ID_IMG'] = '<img src="' . ($groupData['IMAGE_ID_FILE'] != false ? $groupData['IMAGE_ID_FILE']['SRC'] : '/bitrix/images/1.gif') . '" height="60" class="sonet-group-create-popup-image" id="sonet_group_create_popup_image" border="0">';
			$groupData['MODERATOR_IDS'] = [];
			$groupData['LANDING'] = ($groupFields['LANDING'] === 'Y' ? 'Y' : 'N');
			$groupData['SCRUM_OWNER_ID'] = ($groupFields['SCRUM_OWNER_ID'] ?: null);
			$groupData['SCRUM_MASTER_ID'] = ($groupFields['SCRUM_MASTER_ID'] ?: null);
			$groupData['SCRUM_SPRINT_DURATION'] = ($groupFields['SCRUM_SPRINT_DURATION'] ?: null);
			$groupData['SCRUM_TASK_RESPONSIBLE'] = ($groupFields['SCRUM_TASK_RESPONSIBLE'] ?: null);
			$groupData['AVATAR_TYPE'] = (string)($groupFields['AVATAR_TYPE'] ?? '');

			foreach (array_keys($groupPropertiesList) as $field)
			{
				if (!isset($groupFields[$field]))
				{
					continue;
				}

				$groupPropertiesList[$field]['VALUE'] = $groupFields['~' . $field];
				$groupPropertiesList[$field]['ENTITY_VALUE_ID'] = $groupFields['ID'];
			}

			$groupData['IS_EXTRANET_GROUP'] = (
				Loader::includeModule('extranet')
				&& \CExtranet::isExtranetSocNetGroup($groupId)
					? 'Y'
					: 'N'
			);

			$res = UserToGroupTable::getList([
				'filter' => [
					'ROLE' => UserToGroupTable::ROLE_MODERATOR,
					'GROUP_ID' => $groupId,
				],
				'select' => [ 'USER_ID' ]
			]);
			while ($relation = $res->fetch())
			{
				$groupData['MODERATOR_IDS'][] = (int)$relation['USER_ID'];
			}
		}
		else
		{
			$groupData['VISIBLE'] = 'Y';
			$groupData['IS_EXTRANET_GROUP'] = 'N';
			$groupId = 0;
		}
	}

	public static function processWorkgroupFeatures($groupId, &$featuresList): void
	{
		$result = [];

		if ((int)$groupId > 0)
		{
			$res = \CSocNetFeatures::getList(
				[],
				[
					'ENTITY_ID' => $groupId,
					'ENTITY_TYPE' => SONET_ENTITY_GROUP,
				]
			);
			while ($featureFields = $res->GetNext())
			{
				$result[$featureFields['FEATURE']] = $featureFields;
			}
		}

		$allowedFeaturesList = \CSocNetAllowed::getAllowedFeatures();

		$sampleKeysList = [
			'tasks' => 1,
			'calendar' => 2,
			'files' => 3,
			'chat' => 4,
			'forum' => 5,
			'microblog' => 6,
			'blog' => 7,
			'photo' => 8,
			'group_lists' => 9,
			'wiki' => 10,
			'content_search' => 11,
			'marketplace' => 12,
		];

		uksort($allowedFeaturesList, static function($a, $b) use ($sampleKeysList) {

			$valA = ($sampleKeysList[$a] ?? 100);
			$valB = ($sampleKeysList[$b] ?? 100);

			if ($valA > $valB)
			{
				return 1;
			}

			if ($valA < $valB)
			{
				return -1;
			}

			return 0;
		});

		foreach ($allowedFeaturesList as $feature => $featureData)
		{
			if (
				!is_array($featureData['allowed'])
				|| !in_array(SONET_ENTITY_GROUP, $featureData['allowed'], true)
			)
			{
				continue;
			}

			if ((int)$groupId === 0)
			{
				$result[$feature]['ACTIVE'] = (
					$feature === 'chat'
						? \CUserOptions::getOption('socialnetwork', 'default_chat_create_default', 'Y')
						: Option::get('socialnetwork', 'default_' . $feature . '_create_default', 'Y', SITE_ID)
				);
			}

			$featuresList[$feature] = [
				'FeatureName' => (
					isset($result[$feature])
						? ($result[$feature]['FEATURE_NAME'] ?? '')
						: false
				),
				'Active' => (!isset($result[$feature]) || $result[$feature]['ACTIVE'] === 'Y')
			];
		}
	}

}
