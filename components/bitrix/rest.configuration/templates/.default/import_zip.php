<?php
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * Bitrix vars
 *
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponent $component
 * @var CBitrixComponentTemplate $this
 * @global CMain $APPLICATION
 * @global CUser $USER
 */
if(isset($arResult['ERROR']) && $arResult['ERROR'])
{
	ShowError($arResult['ERROR']);
	return false;
}
?>
<?php
	$APPLICATION->IncludeComponent(
		'bitrix:rest.configuration.import',
		'',
		array(
			'ZIP_ID' => $arResult['VARIABLES']['ZIP_ID'],
			'ADDITIONAL' => $arResult['VARIABLES']['ADDITIONAL_PARAMS'],
			'MODE' => 'ZIP',
			'SET_TITLE' => 'Y',
			'FROM' => $arResult['FROM'],
		)
	);
?>