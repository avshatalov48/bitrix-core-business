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
$mainClass = 'catalog-compare-list';

if ($arParams['POSITION_FIXED'] == 'Y')
{
	$mainClass .= ' fixed '.($arParams['POSITION'][0] == 'bottom' ? 'bottom' : 'top').' '.($arParams['POSITION'][1] == 'right' ? 'right' : 'left');
}

$style = ($itemCount == 0 ? ' style="display: none;"' : '');

?><div id="<?=$idCompareCount; ?>" class="<?=$mainClass; ?> "<?=$style;?>><?
unset($style, $mainClass);

if ($needReload)
{
	$APPLICATION->RestartBuffer();
}

$frame = $this->createFrame($idCompareCount)->begin('');

if ($itemCount > 0)
{
	?>
	<div class="catalog-compare-count mb-2">
		<?=GetMessage('CP_BCCL_TPL_MESS_COMPARE_COUNT'); ?>&nbsp;<span data-block="count"><?=$itemCount; ?></span>
		<br />
		<a href="<?=$arParams["COMPARE_URL"]; ?>"><?=GetMessage('CP_BCCL_TPL_MESS_COMPARE_PAGE'); ?></a>
	</div>

	<div class="catalog-compare-form">
		<table class="table table-sm table-striped table-borderless mb-0" data-block="item-list">
			<thead>
				<tr>
					<th  scope="col" class="text-center" colspan="2"><strong><?=GetMessage("CATALOG_COMPARE_ELEMENTS")?></strong></th>
				</tr>
			</thead>
			<tbody><?
				foreach($arResult as $arElement)
				{
					?><tr data-block="item-row" data-row-id="row<?=$arElement['PARENT_ID']; ?>">
						<td class="text-left align-middle">
							<a href="<?=$arElement["DETAIL_PAGE_URL"]?>"><?=$arElement["NAME"]?></a>
						</td>
						<td class="text-right align-middle">
							<a class="text-muted" href="javascript:void(0);" data-id="<?=$arElement['PARENT_ID']; ?>" rel="nofollow"><?=GetMessage("CATALOG_DELETE")?></a>
						</td>
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
?>
	<script>
		var <?=$obCompare; ?> = new JCCatalogCompareList(<? echo CUtil::PhpToJSObject($jsParams, false, true); ?>)
	</script>
</div>