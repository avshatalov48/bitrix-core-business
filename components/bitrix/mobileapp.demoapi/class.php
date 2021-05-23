<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

class MobileDemoApiComponent extends \CBitrixComponent
{
	public function onPrepareComponentParams($arParams)
	{
		$this->arResult = array(
			"folder" => $arParams["APP_DIR"],
			"page" => $arParams["DEMO_PAGE_ID"]

		);

		return $arParams;
	}

	public function executeComponent()
	{
		Bitrix\Main\Loader::includeModule("mobileapp");
		$this->IncludeComponentTemplate();
	}
}