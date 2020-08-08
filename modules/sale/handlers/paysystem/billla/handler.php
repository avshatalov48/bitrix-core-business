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
 * Class BillLaHandler
 * @package Sale\Handlers\PaySystem
 */
class BillLaHandler extends BillHandler
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
		$data['BUYER_PERSON_COMPANY_PHONE'] = '+34 5799278';
		$data['SELLER_COMPANY_PHONE'] = '+34 5799218';
		$data['BILLLA_COMMENT1'] = Loc::getMessage('SALE_HPS_BILL_LA_COMMENT');
		$data['BILLLA_COMMENT2'] = Loc::getMessage('SALE_HPS_BILL_LA_COMMENT_ADD');
		$data['SELLER_COMPANY_BANK_CITY'] = Loc::getMessage('SALE_HPS_BILL_LA_BANK_CITY');
		$data['SELLER_COMPANY_ADDRESS'] = Loc::getMessage('SALE_HPS_BILL_LA_BANK_ADDRESS');
		$data['BUYER_PERSON_COMPANY_ADDRESS'] = Loc::getMessage('SALE_HPS_BILL_LA_BUYER_COMPANY_ADDRESS');

		foreach ($data['BASKET_ITEMS'] as $i => $item)
		{
			$data['BASKET_ITEMS'][$i]['CURRENCY'] = 'EUR';
		}

		return $data;
	}

}