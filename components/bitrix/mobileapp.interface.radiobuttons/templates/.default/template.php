<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
$APPLICATION->SetAdditionalCSS(CUtil::GetAdditionalFileURL('/bitrix/js/mobileapp/interface.css'));
?>

<div <?=!isset($arParams["NOWRAP"]) ? 'class="order_status_infoblock "' : ''?>id="<?=$arResult["DOM_CONTAINER_ID"]?>">
<?
$idSalt = rand();
$arIds = array();
?>
<?if($arResult["TITLE"]):?>
	<div class="order_acceptpay_infoblock_title"><?=$arResult["TITLE"]?></div>
<?endif;?>
	<ul>
		<?foreach ($arParams["ITEMS"] as $id => $text):?>
			<?$arIds[] = $fullId = $id."_".$idSalt;?>
			<li>
				<div id="div_<?=$fullId?>" class="order_status_li_container<?=($id == $arResult["SELECTED"] ? ' checked' : '')?>">
					<table>
						<tr>
							<td>
								<span class="inputradio">
									<input type="radio" id="<?=$fullId?>" name="<?=$arResult["RADIO_NAME"]?>"<?=($id == $arResult["SELECTED"] ? ' checked' : '')?> value="<?=$id?>">
								</span>
							</td>
							<td><label for="<?=$fullId?>"><span><?=$text?></span></label></td>
						</tr>
					</table>
				</div>
			</li>
		<?endforeach;?>
	</ul>
</div>

<script type="text/javascript">
	BX.onCustomEvent("onMobileAppNeedJSFile", [{ url: "<?=$templateFolder.'/script.js'?>"}]);
</script>

<script type="text/javascript">
BX.ready(function(){

	radioButtonsControl_<?=$arResult["DOM_CONTAINER_ID"]?> = new __MARadioButtonsControl({
		containerId: "<?=$arResult["DOM_CONTAINER_ID"]?>",
	});

	<?if(isset($arParams["JS_EVENT_GET_SELECTED"])):?>
		BX.addCustomEvent('<?=$arParams["JS_EVENT_GET_SELECTED"]?>',
			function (){ radioButtonsControl_<?=$arResult["DOM_CONTAINER_ID"]?>.getSelected(<?=$arResult["JS_RESULT_HANDLER"]?>);});
	<?endif;?>

	<?foreach ($arIds as $id):?>
		radioButtonsControl_<?=$arResult["DOM_CONTAINER_ID"]?>.makeFastButton("<?=$id?>");
	<?endforeach;?>

	radioButtonsControl_<?=$arResult["DOM_CONTAINER_ID"]?>.init();
});
</script>
