<?php

namespace Sale\Handlers\PaySystem;

use Bitrix\Main\Localization\Loc;
use Bitrix\Sale;
use Bitrix\Sale\PaySystem;
use Bitrix\Main\Loader;

PaySystem\Manager::includeHandler('Bill');
Loc::loadMessages(__FILE__);

/**
 * Class BillEnHandler
 * @package Sale\Handlers\PaySystem
 */
class BillEnHandler extends BillHandler
{
	/**
	 * @return array
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\ObjectException
	 */
	public function getDemoParams()
	{
		$data = parent::getDemoParams();

		$data['CURRENCY'] = 'USD';
		$data['BUYER_PERSON_COMPANY_PHONE'] = '+1 234 567-89-00';
		$data['SELLER_COMPANY_PHONE'] = '+1 234 567-89-11';
		$data['BILLEN_COMMENT1'] = Loc::getMessage('SALE_HPS_BILL_EN_COMMENT');
		$data['BILLEN_COMMENT2'] = Loc::getMessage('SALE_HPS_BILL_EN_COMMENT_ADD');

		foreach ($data['BASKET_ITEMS'] as $i => $item)
		{
			$data['BASKET_ITEMS'][$i]['CURRENCY'] = 'USD';
		}

		return $data;
	}

}