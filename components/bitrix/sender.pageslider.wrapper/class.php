<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);


class SenderPageSliderWrapperComponent extends \CBitrixComponent
{
	public function executeComponent()
	{
		/** @var CMain $APPLICATION */
		global $APPLICATION;
		$APPLICATION->RestartBuffer();

		if (!isset($this->arParams['POPUP_COMPONENT_PARAMS']) || !is_array($this->arParams['POPUP_COMPONENT_PARAMS']))
		{
			$this->arParams['POPUP_COMPONENT_PARAMS'] = array();
		}
		$this->arParams['POPUP_COMPONENT_PARAMS']['IFRAME'] = true;

		$this->includeComponentTemplate();

		require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_after.php');
		exit;
	}
}