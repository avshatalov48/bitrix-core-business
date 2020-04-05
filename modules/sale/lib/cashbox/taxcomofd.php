<?php

namespace Bitrix\Sale\Cashbox;

use Bitrix\Main\Localization;
use Bitrix\Main;

Localization\Loc::loadMessages(__FILE__);

/**
 * Class TaxcomOfd
 * @package Bitrix\Sale\Cashbox
 */
class TaxcomOfd extends Ofd
{
	const ACTIVE_URL = 'https://receipt.taxcom.ru/v01/show?';

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
		return Localization\Loc::getMessage('SALE_CASHBOX_TAXCOM_OFD_NAME');
	}

	/**
	 * @return array
	 */
	protected function getLinkParamsMap()
	{
		return array(
			'fp' => Check::PARAM_FISCAL_DOC_ATTR,
			's' => Check::PARAM_DOC_SUM
		);
	}

	/**
	 * @param $data
	 * @return string
	 */
	public function generateCheckLink($data)
	{
		$url = parent::generateCheckLink($data);
		$url .= '&sf=False&sfn=False';

		return $url;
	}

}