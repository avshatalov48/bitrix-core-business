<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */

$itemCount = count($arResult);
$needReload = (isset($_REQUEST["compare_list_reload"]) && $_REQUEST["compare_list_reload"] == "Y");
$idCompareCount = 'compareList'.$this->randString();
$obCompare = 'ob'.$idCompareCount;
$mainClass = 'bx_catalog-compare-list';
if ($arParams['POSITION_FIXED'] == 'Y')
{
	$mainClass .= ' fix '.($arParams['POSITION'][0] == 'bottom' ? 'bottom' : 'top').' '.($arParams['POSITION'][1] == 'right' ? 'right' : 'left');
}
$style = ($itemCount == 0 ? ' style="display: none;"' : '');
?><div id="<?=$idCompareCount; ?>" class="<?=$mainClass; ?> "<?=$style; ?>><?
unset($style, $mainClass);
if ($needReload)
{
	$APPLICATION->RestartBuffer();
}
$frame = $this->createFrame($idCompareCount)->begin('');
if ($itemCount > 0)
{
	?><div class="bx_catalog_compare_count">
	<p><?=GetMessage('CP_BCCL_TPL_MESS_COMPARE_COUNT'); ?>&nbsp;<span data-block="count"><?=$itemCount; ?></span></p>
	<p class="compare-redirect"><a href="<?=$arParams["COMPARE_URL"]; ?>"><?=GetMessage('CP_BCCL_TPL_MESS_COMPARE_PAGE'); ?></a></p>
	</div>
	<div class="bx_catalog_compare_form">
	<table class="compare-items" data-block="item-list">
	<thead><tr><td align="center" colspan="2"><?=GetMessage("CATALOG_COMPARE_ELEMENTS")?></td></tr></thead>
	<tbody><?
	foreach($arResult as $arElement)
	{
		?><tr data-block="item-row" data-row-id="row<?=$arElement['PARENT_ID']; ?>">
			<td><a href="<?=$arElement["DETAIL_PAGE_URL"]?>"><?=$arElement["NAME"]?></a></td>
			<td><noindex><a href="javascript:void(0);" data-id="<?=$arElement['PARENT_ID']; ?>" rel="nofollow"><?=GetMessage("CATALOG_DELETE")?></a></noindex></td>
		</tr><?
	}
	?>
	</tbody>
	</table>
	</div><?
}
$frame->end();
if ($needReload)
{
	die();
}
$currentPath = CHTTP::urlDeleteParams(
	$APPLICATION->GetCurPageParam(),
	array(
		$arParams['PRODUCT_ID_VARIABLE'],
		$arParams['ACTION_VARIABLE'],
		'ajax_action'
	),
	array("delete_system_params" => true)
);

$jsParams = array(
	'VISUAL' => array(
		'ID' => $idCompareCount,
	),
	'AJAX' => array(
		'url' => $currentPath,
		'params' => array(
			'ajax_action' => 'Y'
		),
		'reload' => array(
			'compare_list_reload' => 'Y'
		),
		'templates' => array(
			'delete' => (mb_strpos($currentPath, '?') === false ? '?' : '&').$arParams['ACTION_VARIABLE'].'=DELETE_FROM_COMPARE_LIST&'.$arParams['PRODUCT_ID_VARIABLE'].'='
		)
	),
	'POSITION' => array(
		'fixed' => $arParams['POSITION_FIXED'] == 'Y',
		'align' => array(
			'vertical' => $arParams['POSITION'][0],
			'horizontal' => $arParams['POSITION'][1]
		)
	)
);
?></div>
<script>
var <?=$obCompare; ?> = new JCCatalogCompareList(<? echo CUtil::PhpToJSObject($jsParams, false, true); ?>)
</script>