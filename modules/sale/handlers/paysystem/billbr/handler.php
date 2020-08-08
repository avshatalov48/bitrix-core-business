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
 * Class BillBrHandler
 * @package Sale\Handlers\PaySystem
 */
class BillBrHandler extends BillHandler
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

		$data['CURRENCY'] = 'BRL';
		$data['BUYER_PERSON_COMPANY_PHONE'] = '011 55 11 9 1234 1111';
		$data['SELLER_COMPANY_PHONE'] = '011 55 11 9 1234 0000';
		$data['BILLBR_COMMENT1'] = Loc::getMessage('SALE_HPS_BILL_BR_COMMENT');
		$data['BILLBR_COMMENT2'] = Loc::getMessage('SALE_HPS_BILL_BR_COMMENT_ADD');
		$data['SELLER_COMPANY_BANK_CITY'] = Loc::getMessage('SALE_HPS_BILL_BR_BANK_CITY');
		$data['SELLER_COMPANY_ADDRESS'] = Loc::getMessage('SALE_HPS_BILL_BR_BANK_ADDRESS');
		$data['BUYER_PERSON_COMPANY_ADDRESS'] = Loc::getMessage('SALE_HPS_BILL_BR_BUYER_COMPANY_ADDRESS');

		foreach ($data['BASKET_ITEMS'] as $i => $item)
		{
			$data['BASKET_ITEMS'][$i]['CURRENCY'] = 'BRL';
		}
		return $data;
	}

}