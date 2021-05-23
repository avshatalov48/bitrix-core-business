<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

class CSaleLocationMap extends CBitrixComponent
{
	protected function checkParams($params)
	{
		if(!isset($params['EXTERNAL_LOCATION_CLASS']))
			throw new \Bitrix\Main\ArgumentNullException('EXTERNAL_LOCATION_CLASS');
	}

	public function onPrepareComponentParams($params)
	{
		if(!isset($params['START_BUTTON']))
			$params['START_BUTTON'] = Loc::getMessage('SALE_LOCATION_MAP_BUTTON');

		return $params;
	}

	public function executeComponent()
	{
		try
		{
			$this->checkParams($this->arParams);
		}
		catch(\Exception $e)
		{
			ShowError($e->getMessage());
			return;
		}

		if(!CModule::IncludeModule('sale'))
		{
			ShowError("Module sale not installed!");
			return;
		}

		\Bitrix\Sale\Delivery\Services\Manager::getHandlersList();

		$res = \Bitrix\Sale\Location\LocationTable::getList(array(
			'runtime' => array(new \Bitrix\Main\Entity\ExpressionField('CNT', 'COUNT(1)')),
			'select' => array('CNT')
		));

		if($loc = $res->fetch())
			$this->arResult['BITRIX_LOCATIONS_COUNT'] = $loc['CNT'];

		/** @var \Bitrix\Sale\Delivery\ExternalLocationMap $locationClass */
		$locationClass = $this->arParams['EXTERNAL_LOCATION_CLASS'];

		$res = \Bitrix\Sale\Location\ExternalTable::getList(array(
			'filter' => array('=SERVICE_ID' => $locationClass::getExternalServiceId()),
			'runtime' => array(new \Bitrix\Main\Entity\ExpressionField('CNT', 'COUNT(1)')),
			'select' => array('CNT')
		));

		if($loc = $res->fetch())
			$this->arResult['SERVICE_LOCATIONS_COUNT'] = $loc['CNT'];

		CJSCore::Init('core', 'ajax');
		$this->includeComponentTemplate();
	}
}