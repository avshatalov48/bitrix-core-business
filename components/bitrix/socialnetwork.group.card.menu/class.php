<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Loader;
use Bitrix\Socialnetwork\ComponentHelper;

final class SocialnetworkWorkgroupCardMenu extends CBitrixComponent
{
	public function onPrepareComponentParams($params): array
	{
		$params['GROUP_ID'] = (int)($params['GROUP_ID'] ?? 0);

		return $params;
	}

	public function executeComponent()
	{
		if ($this->arParams['GROUP_ID'] <= 0)
		{
			return;
		}

		Loader::includeModule('socialnetwork');

		$groupFields = \Bitrix\Socialnetwork\Item\Workgroup::getById($this->arParams['GROUP_ID'])->getFields();
		if (empty($groupFields))
		{
			return;
		}

		$this->arResult['IS_PROJECT'] = (isset($groupFields['PROJECT']) && $groupFields['PROJECT'] === 'Y');
		$this->arResult['IS_OPENED'] = (isset($groupFields['IS_OPENED']) && $groupFields['IS_OPENED'] === 'Y');
		$this->arResult['GROUP_NAME'] = ($groupFields['NAME'] ?? '');

		$request = \Bitrix\Main\Context::getCurrent()->getRequest();
		$this->arResult['IS_IFRAME'] = ($request->get('IFRAME') === 'Y');
		$this->arResult['TAB'] = (string)($this->arParams['TAB'] ?? 'card');

		$this->arResult['PERMISSIONS'] = \Bitrix\Socialnetwork\Helper\Workgroup::getPermissions([
			'groupId' => $this->arParams['GROUP_ID'],
		]);

		$this->arResult['HideArchiveLinks'] = (
			$groupFields['CLOSED'] === 'Y'
			&& \Bitrix\Main\Config\Option::get('socialnetwork', 'work_with_closed_groups', 'N') !== 'Y'
		);

		$this->arResult['canPickTheme'] = (
			$this->arResult['IS_IFRAME']
			&& Loader::includeModule('intranet')
			&& \Bitrix\Intranet\Integration\Templates\Bitrix24\ThemePicker::isAvailable()
			&& $this->arResult['PERMISSIONS']['UserCanModifyGroup']
			&& !$this->arResult['HideArchiveLinks']
		);

		$this->arResult['URLS'] = [
			'card' => $this->processUrl($this->arParams['URLS']['CARD']),
			'edit' => $this->processUrl($this->arParams['URLS']['EDIT']),
			'copy' => $this->processUrl($this->arParams['URLS']['COPY']),
			'delete' => $this->processUrl($this->arParams['URLS']['DELETE']),
			'leave' => $this->processUrl($this->arParams['URLS']['LEAVE']),
			'join' => $this->processUrl($this->arParams['URLS']['JOIN']),
			'members-list' => $this->processUrl($this->arParams['URLS']['MEMBERS']),
			'requests-out' => $this->processUrl($this->arParams['URLS']['REQUESTS_OUT']),
			'requests-in' => $this->processUrl($this->arParams['URLS']['REQUESTS_IN']),
			'features' => $this->processUrl($this->arParams['URLS']['FEATURES']),
		];

		$this->includeComponentTemplate();
	}

	private function processUrl($url): string
	{
		return CHTTP::urlAddParams(
			str_replace('#group_id#', $this->arParams['GROUP_ID'], $url),
			[ 'IFRAME' => 'Y' ]
		);
	}
}
