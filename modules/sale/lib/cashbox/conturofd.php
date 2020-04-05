<?php

namespace Bitrix\Sale\Cashbox;

use Bitrix\Main\Localization;
use Bitrix\Main;
use Bitrix\Sale\Result;

Localization\Loc::loadMessages(__FILE__);

/**
 * Class ConturOfd
 * @package Bitrix\Sale\Cashbox
 */
class ConturOfd extends Ofd
{
	const ACTIVE_URL = 'https://cash.kontur.ru/CashReceipt/View';

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
		return Localization\Loc::getMessage('SALE_CASHBOX_CONTUR_OFD_NAME');
	}

	/**
	 * @param $data
	 * @return string
	 */
	public function generateCheckLink($data)
	{
		$url = $this->getUrl();
		$url .= '/FN/'.$data[Check::PARAM_FN_NUMBER];
		$url .= '/FD/'.$data[Check::PARAM_FISCAL_DOC_NUMBER];
		$url .= '/FP/'.$data[Check::PARAM_FISCAL_DOC_ATTR];

		return $url;
	}
}