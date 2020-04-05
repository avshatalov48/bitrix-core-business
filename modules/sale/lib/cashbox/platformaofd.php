<?php

namespace Bitrix\Sale\Cashbox;

use Bitrix\Main\Localization;
use Bitrix\Main;

Localization\Loc::loadMessages(__FILE__);

/**
 * Class PlatformaOfd
 * @package Bitrix\Sale\Cashbox
 */
class PlatformaOfd extends Ofd
{
	const ACTIVE_URL = 'https://lk.platformaofd.ru/web/noauth/cheque?';

	/**
	 * @return string
	 */
	protected function getUrl()
	{
		return static::ACTIVE_URL;
	}

	/**
	 * @return array
	 */
	protected function getLinkParamsMap()
	{
		return array(
			'fn' => Check::PARAM_FN_NUMBER,
			'fp' => Check::PARAM_FISCAL_DOC_ATTR,
			'i' => Check::PARAM_FISCAL_DOC_NUMBER,
		);
	}

	/**
	 * @throws Main\NotImplementedException
	 * @return string
	 */
	public static function getName()
	{
		return Localization\Loc::getMessage('SALE_CASHBOX_PLATFORMA_OFD_NAME');
	}

}