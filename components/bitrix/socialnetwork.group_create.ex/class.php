<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Loader;
use Bitrix\Intranet\Integration\Templates\Bitrix24\ThemePicker;

final class SocialnetworkGroupCreate extends \CBitrixComponent
{
	public function onPrepareComponentParams($params)
	{
		if (
			isset($params["LID"])
			&& !empty($params["LID"])
		)
		{
			$res = \Bitrix\Main\SiteTable::getList([
				'filter' => [
					'=LID' => $params["LID"],
					'=ACTIVE' => 'Y'
				],
				'select' => ['LID']
			]);
			if ($siteFields = $res->fetch())
			{
				$this->setSiteId($params["LID"]);
			}
		}

		return $params;
	}

	protected function prepareData()
	{
		$result = [];
		$this->processParams($result);
		$this->processRequest($result);
		$this->getThemePickerData($result);

		return $result;
	}

	protected function processParams(array &$result = []): void
	{
		if (!empty($this->arParams['TAB']))
		{
			$result['TAB'] = strtolower($this->arParams['TAB']);
		}
	}

	protected function processRequest(array &$result = []): void
	{
		$request = \Bitrix\Main\Context::getCurrent()->getRequest();

		$result['IS_IFRAME'] = ($request->get('IFRAME') === 'Y');
		$result['IS_POPUP'] = ($request->get('POPUP') === 'Y');

		if (in_array($request->get('CALLBACK'), [ 'REFRESH', 'GROUP' ]))
		{
			$result['CALLBACK'] = $request->get('CALLBACK');
		}

		if (!empty($request->get('tab')))
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
			&& $this->arParams['THEME_ENTITY_TYPE'] === 'SONET_GROUP'
		);
		$result['themePickerData'] = [];

		if ($result['showThemePicker'])
		{
			if ($groupId > 0)
			{
				$themePicker = new ThemePicker(SITE_TEMPLATE_ID, false, $USER->getId(), ThemePicker::ENTITY_TYPE_SONET_GROUP, $groupId);
				$themeId = $themePicker->getCurrentThemeId();
				$themeUserId = false;
				if ($themeId)
				{
					$res = \Bitrix\Intranet\Internals\ThemeTable::getList([
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
			else
			{
				if ($themePicker = new ThemePicker(SITE_TEMPLATE_ID))
				{
					$themesList = $themePicker->getPatternThemes();
					$result['themePickerData'] = $themesList[array_rand($themesList)];
				}
			}
		}
	}

	public function executeComponent()
	{
		$this->arResult = $this->prepareData();

		return $this->__includeComponent();
	}
}
