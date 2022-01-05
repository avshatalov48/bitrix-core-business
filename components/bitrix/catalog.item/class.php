<?

use \Bitrix\Sale\Internals\FacebookConversion;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

class CatalogItemComponent extends CBitrixComponent
{
	public function onPrepareComponentParams($params)
	{
		if (!empty($params['RESULT']))
		{
			$this->arResult = $params['RESULT'];
			unset($params['RESULT']);
		}

		if (!empty($params['PARAMS']))
		{
			$params += $params['PARAMS'];
			unset($params['PARAMS']);
		}

		if (isset($params['CUSTOM_SITE_ID']))
		{
			$this->setSiteId($params['CUSTOM_SITE_ID']);
		}

		return $params;
	}

	public function executeComponent()
	{
		$this->arResult['IS_FACEBOOK_CONVERSION_CUSTOMIZE_PRODUCT_EVENT_ENABLED'] =
			\Bitrix\Main\Loader::includeModule('sale')
			&& FacebookConversion::isEventEnabled('CustomizeProduct')
		;
		$this->includeComponentTemplate();
	}
}