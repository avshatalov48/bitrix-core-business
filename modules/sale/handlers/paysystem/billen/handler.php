<?php

namespace Sale\Handlers\PaySystem;

use Bitrix\Main\Localization\Loc;
use Bitrix\Sale;
use Bitrix\Sale\PaySystem;
use Bitrix\Main\Loader;

Loader::registerAutoLoadClasses('sale', array(PaySystem\Manager::getClassNameFromPath('Bill') => 'handlers/paysystem/bill/handler.php'));
Loc::loadMessages(__FILE__);

class BillEnHandler extends BillHandler
{
	/**
	 * @return array
	 */
	public function getDemoParams()
	{
		$data = parent::getDemoParams();
		$data['BILLEN_COMMENT1'] = Loc::getMessage('SALE_HPS_BILL_EN_COMMENT');
		$data['BILLEN_COMMENT2'] = Loc::getMessage('SALE_HPS_BILL_EN_COMMENT_ADD');

		return $data;
	}

}