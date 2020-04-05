<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use \Sale\Handlers\Delivery\Additional\RusPost\Reliability\Service;
use \Bitrix\Main\Localization\Loc;

class CSaleDeliveryRusPostReliablity extends CBitrixComponent
{
	private function checkParams($params)
	{
		if(!isset($params["RELIABILITY"]))
			throw new \Bitrix\Main\ArgumentNullException('params["RELIABILITY"]');

		if(!in_array($params["RELIABILITY"], [Service::RELIABLE, Service::FRAUD, Service::UNKNOWN]))
			throw new \Bitrix\Main\ArgumentOutOfRangeException('params["RELIABILITY"]');

		return true;
	}

	public function executeComponent()
	{
		global $APPLICATION;

		if (!\Bitrix\Main\Loader::includeModule('sale'))
		{
			ShowError('Can\'t include module "sale"');
			return;
		}

		try
		{
			$this->checkParams($this->arParams);
		}
		catch(\Exception $e)
		{
			ShowError($e->getMessage());
			return;
		}

		$this->arResult['RELIABILITY'] = $this->arParams['RELIABILITY'];
		$this->includeComponentTemplate();
	}
}