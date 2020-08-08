<?php

namespace Sale\Handlers\PaySystem;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Request;
use Bitrix\Sale;
use Bitrix\Sale\PaySystem;
use Bitrix\Main\Loader;

PaySystem\Manager::includeHandler('Bill');
Loc::loadMessages(__FILE__);

/**
 * Class BillUaHandler
 * @package Sale\Handlers\PaySystem
 */
class BillUaHandler extends BillHandler
{
	/**
	 * @return array
	 */
	public function getDemoParams()
	{
		$data = parent::getDemoParams();

		$data['CURRENCY'] = 'UAH';
		$data['BUYER_PERSON_COMPANY_PHONE'] = '+380-67-103-44-52';
		$data['SELLER_COMPANY_PHONE'] = '+380-67-603-44-52';
		$data['SELLER_COMPANY_MFO'] = '300012';
		$data['SELLER_COMPANY_MFO'] = '300012';
		$data['SELLER_COMPANY_IPN'] = '12547856696';
		$data['SELLER_COMPANY_EDRPOY'] = '19017842';
		$data['SELLER_COMPANY_PDV'] = '20154519017842';
		$data['SELLER_COMPANY_SYS'] = Loc::getMessage('SALE_HPS_BILL_UA_COMPANY_SYS');;
		$data['BILLUA_COMMENT1'] = Loc::getMessage('SALE_HPS_BILL_UA_COMMENT');
		$data['BILLUA_COMMENT2'] = Loc::getMessage('SALE_HPS_BILL_UA_COMMENT_ADD');
		$data['SELLER_COMPANY_BANK_CITY'] = Loc::getMessage('SALE_HPS_BILL_UA_BANK_CITY');
		$data['SELLER_COMPANY_ADDRESS'] = Loc::getMessage('SALE_HPS_BILL_UA_BANK_ADDRESS');
		$data['BUYER_PERSON_COMPANY_ADDRESS'] = Loc::getMessage('SALE_HPS_BILL_UA_BUYER_COMPANY_ADDRESS');

		foreach ($data['BASKET_ITEMS'] as $i => $item)
		{
			$data['BASKET_ITEMS'][$i]['CURRENCY'] = 'UAH';
		}

		return $data;
	}

}