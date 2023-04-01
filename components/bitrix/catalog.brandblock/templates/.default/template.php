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
$this->setFrameMode(true);

if (empty($arResult["BRAND_BLOCKS"]))
	return;
$strRand = $this->randString();
$strObName = 'obIblockBrand_'.$strRand;
$blockID = 'bx_IblockBrand_'.$strRand;
$mouseEvents = 'onmouseover="'.$strObName.'.itemOver(this);" onmouseout="'.$strObName.'.itemOut(this)"';


if ($arParams['SINGLE_COMPONENT'] == "Y")
	echo '<div class="bx_item_detail_inc_two_'.count($arResult['BRAND_BLOCKS']).' general" id="'.$blockID.'">';
else
	echo '<div class="bx_item_detail_inc_two" id="'.$blockID.'">';

$handlerIDS = array();

foreach ($arResult["BRAND_BLOCKS"] as $blockId => $arBB)
{
	$brandID = 'brand_'.$arResult['ID'].'_'.$blockId.'_'.$strRand;
	$popupID = $brandID.'_popup';

	$tagAttrs = '';
	$popupContext = '';
	$shortDescr = '';
	$useLink = $arBB['LINK'] !== false;
	$usePopup = $arBB['FULL_DESCRIPTION'] !== false;
	if ($usePopup)
	{
		if (preg_match('/<a[^>]+>[^<]+<\/a>/', $arBB['FULL_DESCRIPTION']) == 1)
			$useLink = false;
		$popupContext = '<span class="bx_popup" id="'.$popupID.'">'.
			'<span class="arrow"></span>'.
			'<span class="text">'.$arBB['FULL_DESCRIPTION'].'</span>'.
			'</span>';
	}

	switch ($arBB['TYPE'])
	{
		case 'ONLY_PIC':
			$tagAttrs = 'id="'.$brandID.'_vidget" class="brandblock-block"'.
				' style="background-image:url(\''.$arBB['PICT']['SRC'].'\');"';
			break;
		default:
			$tagAttrs = 'id="'.$brandID.'_vidget"'.(
				empty($arBB['PICT'])
				? ' class="brandblock-block"'
				: ' class="brandblock-block icon" style="background-image:url(\''.$arBB['PICT']['SRC'].'\');"'
			);
			if ($arBB['DESCRIPTION'] !== false)
				$shortDescr = '<span class="brandblock-text">'.htmlspecialcharsbx($arBB['DESCRIPTION']).'</span>';
			break;
	}
	if ($usePopup)
		$tagAttrs .= ' data-popup="'.$popupID.'"';

	?><div id="<?=$brandID;?>" class="brandblock-container"<? echo ($usePopup ? ' data-popup="'.$popupID.'"' : ''); ?>>
		<div class="brandblock-wrap"><?
		if ($useLink)
		{
			?><a href="<?=htmlspecialcharsbx($arBB['LINK']); ?>" <?=$tagAttrs; ?> target="_blank"><?=$popupContext.$shortDescr; ?></a><?
		}
		else
		{
			?><span <?=$tagAttrs; ?>><?=$popupContext.$shortDescr; ?></span><?
		}
		?></div>
	</div><?

	if ($usePopup)
		$handlerIDS[] = $brandID;
}
?>
	</div>
	<div style="clear: both;"></div>
<?
if (!empty($handlerIDS))
{
	$jsParams = array(
		'blockID' => $blockID
	);
?>
	<script type="text/javascript">
		var <? echo $strObName; ?> = new JCIblockBrands(<? echo CUtil::PhpToJSObject($jsParams); ?>);
	</script>
<?
}