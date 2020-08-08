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
 * Class BillFrHandler
 * @package Sale\Handlers\PaySystem
 */
class BillFrHandler extends BillHandler
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
		$data['BUYER_PERSON_COMPANY_PHONE'] = '+33 1 45 62 02 01';
		$data['SELLER_COMPANY_PHONE'] = '+33 1 45 62 02 11';
		$data['BILLFR_COMMENT1'] = Loc::getMessage('SALE_HPS_BILL_FR_COMMENT');
		$data['BILLFR_COMMENT2'] = Loc::getMessage('SALE_HPS_BILL_FR_COMMENT_ADD');
		$data['SELLER_COMPANY_BANK_CITY'] = Loc::getMessage('SALE_HPS_BILL_FR_BANK_CITY');
		$data['SELLER_COMPANY_ADDRESS'] = Loc::getMessage('SALE_HPS_BILL_FR_BANK_ADDRESS');
		$data['BUYER_PERSON_COMPANY_ADDRESS'] = Loc::getMessage('SALE_HPS_BILL_FR_BUYER_COMPANY_ADDRESS');

		foreach ($data['BASKET_ITEMS'] as $i => $item)
		{
			$data['BASKET_ITEMS'][$i]['CURRENCY'] = 'EUR';
		}

		return $data;
	}

}