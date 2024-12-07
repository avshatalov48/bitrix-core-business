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

echo '<div class="brandblock-list" id="'.$blockID.'">';

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
		$popupContext = '<span class="brandblock-popup" id="'.$popupID.'">'.
			'<span class="brandblock-popup-arrow"></span>'.
			'<span class="brandblock-popup-text">'.$arBB['FULL_DESCRIPTION'].'</span>'.
			'</span>';
	}

	$brandInner = "";

	switch ($arBB['TYPE'])
	{
		case 'ONLY_PIC':
			$brandInner = '<span class="brandblock-image-container"><img class="brandblock-image" src="'.$arBB['PICT']['SRC'].'"></span>';
			break;

		default:
			if ($arBB['PICT'])
				$brandInner = '<span class="brandblock-image-container"><img class="brandblock-image" src="'.$arBB['PICT']['SRC'].'"></span>';

			if ($arBB['DESCRIPTION'] !== false)
				$shortDescr = '<span class="brandblock-text">'.htmlspecialcharsbx($arBB['DESCRIPTION']).'</span>';
			break;
	}
	if ($usePopup)
		$tagAttrs .= ' data-popup="'.$popupID.'"';

	?>
		<?
		if ($useLink)
		{
			?><a class="brandblock-item" href="<?=htmlspecialcharsbx($arBB['LINK']); ?>" <?=$tagAttrs; ?> target="_blank">
				<?=$brandInner.$shortDescr.$popupContext; ?>
			</a><?
		}
		else
		{
			?>
			<div class="brandblock-item" id="<?=$brandID;?>" <? echo ($usePopup ? ' data-popup="'.$popupID.'"' : ''); ?> <?=$tagAttrs; ?>>
				<?=$brandInner.$shortDescr.$popupContext; ?>
			</div><?
		}


	if ($usePopup)
		$handlerIDS[] = $brandID;
}
?>
	</div>
<?
if (!empty($handlerIDS))
{
	$jsParams = array(
		'blockID' => $blockID
	);
?>
	<script>
		var <? echo $strObName; ?> = new JCIblockBrands(<? echo CUtil::PhpToJSObject($jsParams); ?>);
	</script>
<?
}