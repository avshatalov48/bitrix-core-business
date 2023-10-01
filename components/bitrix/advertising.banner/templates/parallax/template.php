<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?if (count($arResult['BANNERS']) > 0):?>

<?
	$this->addExternalCss("/bitrix/themes/.default/banner.css");
	$arParams['WIDTH'] = intval($arResult['SIZE']['WIDTH']);
	$arParams['HEIGHT'] = intval($arResult['SIZE']['HEIGHT']);

	$frame = $this->createFrame()->begin("");
?>

<?if (!isset($arParams['PREVIEW']) || $arParams['PREVIEW'] != 'Y'):?>
	<?foreach($arResult["BANNERS"] as $k => $banner):?>
		<?=$banner?>
	<?endforeach;?>
<?else:?>
	<div id='tPreview' style="display:none;margin:auto;">
		<img style='width:100%' src="/bitrix/themes/.default/icons/advertising/placeholder.png">
	</div>
	<script>
		(function(){
			if (<?=$arParams['WIDTH']?> == 0)
			{
				BX('tPreview').style.width = top.cWidth/2 + 'px';
				BX('tPreview').style.height = top.cWidth/3.55 + 'px';
			}
			else if(top.cWidth/2 > <?=$arParams['WIDTH']?>)
			{
				BX('tPreview').style.width = '<?=$arParams['WIDTH']?>px';
				BX('tPreview').style.height = '<?=$arParams['HEIGHT']?>px';
			}
			else
			{
				BX('tPreview').style.width = top.cWidth/2 + 'px';
				BX('tPreview').style.height = top.cWidth/3.55 + 'px';
			}
			document.body.style.backgroundColor = 'transparent';
			BX('tPreview').style.display = '';
		})();
	</script>
<?endif;?>

<?$frame->end();?>

<?endif;?>