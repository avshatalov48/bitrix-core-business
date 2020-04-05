<?php

namespace Bitrix\Sale\Cashbox;

use Bitrix\Main\Localization;
use Bitrix\Main;

Localization\Loc::loadMessages(__FILE__);

/**
 * Class TenzorOfd
 * @package Bitrix\Sale\Cashbox
 */
class TenzorOfd extends Ofd
{
	const ACTIVE_URL = 'https://ofd.sbis.ru/rec/';

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
		return Localization\Loc::getMessage('SALE_CASHBOX_TENZOR_OFD_NAME');
	}

	/**
	 * @param $data
	 * @return string
	 */
	public function generateCheckLink($data)
	{
		if (!isset($data[Check::PARAM_REG_NUMBER_KKT]))
			return '';

		$url = $this->getUrl();

		$dateObj = Main\Type\DateTime::createFromTimestamp($data[Check::PARAM_DOC_TIME]);
		$date = $dateObj->format('dmy');
		$url .= $data[Check::PARAM_REG_NUMBER_KKT].'/'.$date.'/'.$data[Check::PARAM_FISCAL_DOC_ATTR];

		return $url;
	}

}