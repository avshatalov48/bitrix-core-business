<?
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
		
		return $params;
	}

	public function executeComponent()
	{
		$this->includeComponentTemplate();
	}
}