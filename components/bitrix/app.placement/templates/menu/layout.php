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
 * @var CBitrixComponentTemplate $this
 * @global CMain $APPLICATION
 */

$c = \Bitrix\Main\Text\Converter::getHtmlConverter();

$appId = 0;
$appTitle = '';

foreach($arResult['APPLICATION_LIST'] as $app)
{
	if($app['ID'] == $arResult['APPLICATION_CURRENT'])
	{
		$appTitle = strlen($app['TITLE']) > 0 ? $app['TITLE'] : $app['APP_NAME'];
		$appId = $app['APP_ID'];
		break;
	}
}

if($appId > 0)
{
	$appParam = is_array($arParams['PARAM']) ? $arParams['PARAM'] : array();
	$appParam['FRAME_HEIGHT'] = '100%';

	$APPLICATION->IncludeComponent(
		'bitrix:app.layout',
		'',
		array(
			'ID' => $appId,
			'PLACEMENT' => $arResult['PLACEMENT'],
			'PLACEMENT_ID' => $arResult['APPLICATION_CURRENT'],
			"PLACEMENT_OPTIONS" => $arResult['PLACEMENT_OPTIONS'],
			'PARAM' => $appParam,
		),
		null,
		array('HIDE_ICONS' => 'Y')
	);
?>
<script>
	BX.rest.AppLayout.getPlacement('<?=$arResult['PLACEMENT']?>').applicationPopup.setTitleBar('<?=\CUtil::JSEscape($appTitle)?>');
</script>
<?php
}

