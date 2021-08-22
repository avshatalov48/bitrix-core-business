<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Landing\Manager;
use \Bitrix\Main\Config\Option;

class Tariff24Block extends \Bitrix\Landing\LandingBlock
{
	/**
	 * Method, which will be called once time.
	 * @param array Params array.
	 * @return void
	 */
	public function init(array $params = []): void
	{
		$zone = Bitrix\Landing\Manager::getZone();
		$currencyCode = 'RUR';
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
		if (Manager::isB24())
		{
			$partnerId = (int)Option::get('bitrix24', 'partner_id', 0);
		}
		else
		{
			$partnerId = (int)COption::GetOptionString("main", "~PARAM_PARTNER_ID");
		}

		$options = [
			'productTypeCode' => 'CLOUD',
			'locationAreaId' => $zone,
			'languageId' => LANGUAGE_ID,
			'currencyCode' => $currencyCode,
			'catalogForNewCustomer' => false,
			'partnerId' => $partnerId,
		];
		$this->params['OPTION'] = $options;
	}
}