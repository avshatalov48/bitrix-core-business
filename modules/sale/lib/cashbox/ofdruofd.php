<?php

namespace Bitrix\Sale\Cashbox;

use Bitrix\Main\Localization;
use Bitrix\Main;
use Bitrix\Sale\Result;

Localization\Loc::loadMessages(__FILE__);

/**
 * Class OfdruOfd
 * @package Bitrix\Sale\Cashbox
 */
class OfdruOfd extends Ofd
{
	const ACTIVE_URL = 'https://ofd.ru/rec/';

	/**
	 * @return string
	 */
	protected function getUrl()
	{
		return static::ACTIVE_URL;
	}

	/**
	 * @throws Main\NotImplementedException
	 * @return string
	 */
	public static function getName()
	{
		return Localization\Loc::getMessage('SALE_CASHBOX_OFDRU_OFD_NAME');
	}

	/**
	 * @param $data
	 * @return string
	 */
	public function generateCheckLink($data)
	{
		$url = $this->getUrl();
		$url .= $this->getValueFromSettings('SELLER', 'INN').'/';
		$url .= $data[Check::PARAM_REG_NUMBER_KKT].'/';
		$url .= $data[Check::PARAM_FN_NUMBER].'/';
		$url .= $data[Check::PARAM_FISCAL_DOC_NUMBER].'/';
		$url .= $data[Check::PARAM_FISCAL_DOC_ATTR];

		return $url;
	}

	/**
	 * @return array
	 */
	public static function getSettings()
	{
		$settings = parent::getSettings();

		$settings['SELLER'] = array(
			'LABEL' => Localization\Loc::getMessage('SALE_CASHBOX_OFDRU_SETTINGS_SELLER_INFO'),
			'ITEMS' => array(
				'INN' => array(
					'TYPE' => 'STRING',
					'LABEL' => Localization\Loc::getMessage('SALE_CASHBOX_OFDRU_SELLER_INN'),
					'VALUE' => ''
				)
			)
		);

		return $settings;
	}

	/**
	 * @param $settings
	 * @return Result
	 */
	public static function validateSettings($settings)
	{
		$result = new Result();

		if (empty($settings['SELLER']['INN']))
		{
			$result->addError(new Main\Error(Localization\Loc::getMessage('SALE_CASHBOX_OFD_VALIDATE_E_INN')));
		}

		return $result;
	}

}