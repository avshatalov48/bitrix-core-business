<?php

namespace Sale\Handlers\PaySystem;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Request;
use Bitrix\Sale;
use Bitrix\Main\Loader;
use Bitrix\Sale\PaySystem;

PaySystem\Manager::includeHandler('Bill');
Loc::loadMessages(__FILE__);

/**
 * Class BillDeHandler
 * @package Sale\Handlers\PaySystem
 */
class BillDeHandler extends BillHandler
{
	/**
	 * @param Sale\Payment $payment
	 * @param Request|null $request
	 * @return array
	 */
	protected function getPreparedParams(Sale\Payment $payment, Request $request = null)
	{
		$params = parent::getPreparedParams($payment, $request);
		$params['DATE_BILL'] = $payment->getField('DATE_BILL');

		return $params;
	}

	/**
	 * @return array
	 */
	public function getDemoParams()
	{
		$data = parent::getDemoParams();

		$data['CURRENCY'] = 'EUR';
		$data['BUYER_PERSON_COMPANY_PHONE'] = '+ 49 151 800';
		$data['SELLER_COMPANY_PHONE'] = '+ 49 151 811';
		$data['BILLDE_COMMENT1'] = Loc::getMessage('SALE_HPS_BILL_DE_COMMENT');
		$data['BILLDE_COMMENT2'] = Loc::getMessage('SALE_HPS_BILL_DE_COMMENT_ADD');
		$data['SELLER_COMPANY_BANK_CITY'] = Loc::getMessage('SALE_HPS_BILL_DE_BANK_CITY');
		$data['SELLER_COMPANY_ADDRESS'] = Loc::getMessage('SALE_HPS_BILL_DE_BANK_ADDRESS');
		$data['BUYER_PERSON_COMPANY_ADDRESS'] = Loc::getMessage('SALE_HPS_BILL_DE_BUYER_COMPANY_ADDRESS');

		foreach ($data['BASKET_ITEMS'] as $i => $item)
		{
			$data['BASKET_ITEMS'][$i]['CURRENCY'] = 'EUR';
		}

		return $data;
	}
}