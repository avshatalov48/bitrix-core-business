<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Landing\Manager;
use \Bitrix\Main\Application;
use \Bitrix\Main\Config\Option;
use \Bitrix\Main\Loader;

class LandingBlocksTariffsComponent extends \CBitrixComponent
{
	/**
	 * Base executable method.
	 * @return void
	 */
	public function executeComponent(): void
	{
		if (!\Bitrix\Main\Loader::includeModule('landing'))
		{
			return;
		}

		$zone = $this->getZone();
		if ($zone === 'by')
		{
			$currencyCode = 'BYR';
		}
		if ($zone === 'kz')
		{
			$currencyCode = 'KZT';
		}
		if ($zone === 'ua')
		{
			$currencyCode = 'UAH';
		}
		if (!isset($currencyCode))
		{
			$currencyCode = 'RUR';
		}

		if (Manager::isB24())
		{
			$partnerId = (int)Option::get('bitrix24', 'partner_id', 0);
		}
		else
		{
			$partnerId = (int)COption::GetOptionString("main", "~PARAM_PARTNER_ID");
		}

		$this->arParams['OPTION'] = [
			'productTypeCode' => 'CLOUD',
			'locationAreaId' => $zone,
			'languageId' => LANGUAGE_ID,
			'currencyCode' => $currencyCode,
			'catalogForNewCustomer' => false,
			'partnerId' => $partnerId,
			'replace' => [
				'order' => [
					'url' => [
						'BASIC' => '#someId',
						'STD' => '#someId2',
						'PRO' => '/somefolder/someurl.php',
					],
				],
			],
		];

		$this->IncludeComponentTemplate();
	}

	/**
	 * Get site zone excluding 'user_lang'
	 * @return string
	 */
	protected function getZone(): string
	{
		if (Loader::includeModule('bitrix24'))
		{
			$zone = \CBitrix24::getPortalZone();
		}
		if (!isset($zone) || !$zone)
		{
			$zone = Application::getInstance()->getContext()->getLanguage();
		}

		return $zone;
	}
}