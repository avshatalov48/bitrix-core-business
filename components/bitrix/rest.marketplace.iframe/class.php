<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * Bitrix vars
 *
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponent $this
 * @global CMain $APPLICATION
 * @global CUser $USER
 */

class RestMarketplaceIframeWrapperComponent extends \CBitrixComponent
{
	public function executeComponent()
	{
		global $APPLICATION;
		$APPLICATION->RestartBuffer();

		if(preg_match("/^bitrix:rest\.marketplace\./", $this->arParams['COMPONENT_NAME']))
		{
			if(!isset($this->arParams['COMPONENT_PARAMS']) || !is_array($this->arParams['COMPONENT_PARAMS']))
			{
				$this->arParams['COMPONENT_PARAMS'] = array();
			}

			$this->arParams['COMPONENT_PARAMS']['IFRAME'] = true;

			$this->includeComponentTemplate();
		}

		require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_after.php');
		exit;
	}
}
