<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

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
		$options = [
			'productTypeCode' => 'CLOUD',
			'locationAreaId' => $zone,
			'languageId' => LANGUAGE_ID,
			'currencyCode' => $currencyCode,
			'catalogForNewCustomer' => false,
		];
		$this->params['OPTION'] = $options;
	}
}