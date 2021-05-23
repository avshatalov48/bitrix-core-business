<?php

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
	die();

class CatalogProductCardFeedbackComponent extends CBitrixComponent
{
	protected static function getFeedbackFormInfo($region): ?array
	{
		return ['id' => 269, 'lang' => 'ru', 'sec' => 'mqerov'];
	}

	protected function checkModules(): bool
	{
		if (!Loader::includeModule('catalog'))
		{
			ShowError(Loc::getMessage('CATALOG_PRODUCTCARD_FEEDBACK_MODULE_ERROR'));

			return false;
		}

		return true;
	}

	private function getUser(): ?\CUser
	{
		global $USER;

		return isset($USER) && $USER instanceof \CUser ? $USER : null;
	}

	private function getUserName(): string
	{
		$user = $this->getUser();

		if ($user)
		{
			return $user->getFullName() ?: $user->getLogin();
		}

		return '';
	}

	private function getUserEmail(): string
	{
		$user = $this->getUser();

		if ($user)
		{
			return $user->getEmail();
		}

		return '';
	}

	public function executeComponent()
	{
		if ($this->checkModules())
		{
			$this->arResult = static::getFeedbackFormInfo(LANGUAGE_ID);
			$this->arResult['type'] = 'slider_inline';
			$this->arResult['fields']['values']['CONTACT_EMAIL'] = $this->getUserEmail();
			$this->arResult['presets'] = [
				'from_domain' => defined('BX24_HOST_NAME') ? BX24_HOST_NAME : '',
				'b24_plan' => Loader::includeModule('bitrix24') ? \CBitrix24::getLicenseType() : '',
				'b24_zone' => Loader::includeModule('bitrix24') ? \CBitrix24::getPortalZone() : '',
				'c_name' => $this->getUserName(),
			];

			$this->includeComponentTemplate();
		}
	}
}