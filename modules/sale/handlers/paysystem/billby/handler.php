<?php

namespace Sale\Handlers\PaySystem;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale;
use Bitrix\Sale\PaySystem;

PaySystem\Manager::includeHandler('Bill');
Loc::loadMessages(__FILE__);

/**
 * Class BillByHandler
 * @package Sale\Handlers\PaySystem
 */
class BillByHandler extends BillHandler
{
	/**
	 * @return array
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\ObjectException
	 */
	public function getDemoParams()
	{
		$data = parent::getDemoParams();

		$data['CURRENCY'] = 'BYN';
		$data['BUYER_PERSON_COMPANY_PHONE'] = '+375-17-298-44-52';
		$data['SELLER_COMPANY_PHONE'] = '+375-17-298-44-52';
		$data['SELLER_COMPANY_BANK_CITY'] = Loc::getMessage('SALE_HPS_BILL_BY_BANK_CITY');
		$data['SELLER_COMPANY_ADDRESS'] = Loc::getMessage('SALE_HPS_BILL_BY_BANK_ADDRESS');
		$data['BUYER_PERSON_COMPANY_ADDRESS'] = Loc::getMessage('SALE_HPS_BILL_BY_BUYER_COMPANY_ADDRESS');

		foreach ($data['BASKET_ITEMS'] as $i => $item)
		{
			$data['BASKET_ITEMS'][$i]['CURRENCY'] = 'BYN';
		}

		return $data;
	}

}