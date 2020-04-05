<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();

/*sample data
$arParams = array(
	'ITEMS' => array(
		'1' =>'first ',
		'2' =>'second ',
		'3' =>'third'
		),
	'SELECTED' => '1',
	'JS_CALLBACK_FUNC' => 'onTopButtonClick'
);
*/

if(!isset($arParams['ITEMS']) || empty($arParams['ITEMS']) || !is_array($arParams['ITEMS']))
	return;

$arResult['JS_CALLBACK_FUNC'] = $arParams['JS_CALLBACK_FUNC'] ? $arParams['JS_CALLBACK_FUNC'] : 'false';

if(isset($arParams['SELECTED']))
{
	$arResult['SELECTED'] = $arParams['SELECTED'];
}
else
{
	reset($arParams['ITEMS']);
	$arResult['SELECTED'] = key($arParams['ITEMS']);
}

if(isset($arParams['GET_JS']) && $arParams['GET_JS'] == 'Y')
	$arResult['GET_JS'] = true;
else
	$arResult['GET_JS'] = false;

$this->IncludeComponentTemplate();
?>
