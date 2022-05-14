<?php

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
	die();

class CatalogProductCardFeedbackComponent extends CBitrixComponent
{
	protected static function getFeedbackFormInfo($region): ?array
	{
		$forms = [
			'ru' => ['id' => 269, 'lang' => 'ru', 'sec' => 'mqerov'],
			'en' => ['id' => 347, 'lang' => 'en', 'sec' => 'lxfji8'],
			'es' => ['id' => 349, 'lang' => 'es', 'sec' => 'gdf9i1'],
			'de' => ['id' => 355, 'lang' => 'de', 'sec' => 'x8k56n'],
			'ua' => ['id' => 357, 'lang' => 'ua', 'sec' => '2z19xl'],
			'com.br' => ['id' => 353, 'lang' => 'com.br', 'sec' => '5cleqn'],
		];
		
		// links
		$forms['by'] = $forms['kz'] = $forms['ru'];
		
		return $forms[$region] ?? $forms['en'];
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