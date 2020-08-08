<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
$APPLICATION->SetAdditionalCSS(CUtil::GetAdditionalFileURL('/bitrix/js/mobileapp/interface.css'));
$idSalt = rand();
$arIds = array();
?>

<div id="<?=$arResult["DOM_CONTAINER_ID"]?>">
	<?foreach ($arParams["ITEMS"] as $value => $params):
		$id = '';
		$bChecked = false;
		$name = '';

		if(is_array($params))
		{
			if(isset($params["ID"]))
				$id =  $params["ID"];

			if(isset($params["VALUE"]))
				$value =  $params["VALUE"];

			if(isset($params["NAME"]))
				$name = $params["NAME"];

			$bChecked = (isset($params["CHECKED"]) && $params["CHECKED"] == true) ? true : false;
			$text =	isset($params["TITLE"]) ? $params["TITLE"] : '';
		}
		elseif(is_string($params))
			$text = $params;
		else
			continue;

		if(!$bChecked && isset($arResult["CHECKED"]))
			$bChecked = in_array($value, $arResult["CHECKED"]) || $bChecked ? true : false;

		if(trim($name) == '')
			$name = $arResult["NAME"];

		$checked = $bChecked ? " checked" : "";

		if($id === '')
			$id = preg_replace('#[^\d\w]#', '', $value."_".$idSalt);

		$arIds[] = $id;
		?>
		<ul>
			<li>
				<div id="div_<?=$id?>" class="order_acceptpay_li_container<?=$checked?>">
					<table>
						<tr>
							<td>
								<span class="inputcheckbox">
									<input type="checkbox" id="<?=$id?>" name="<?=$name?>"<?=$checked?><?=$value <> '' ? ' value="'.$value.'"' : ''?>>
								</span>
							</td>
							<td><label for="<?=$id?>"><span><?=$text?></span></label></td>
						</tr>
					</table>
				</div>
			</li>
		</ul>
	<?endforeach;?>
</div>

<script type="text/javascript">
	BX.onCustomEvent("onMobileAppNeedJSFile", [{ url: "<?=$templateFolder.'/script.js'?>"}]);
</script>

<script type="text/javascript">
	checkboxControl_<?=$arResult["DOM_CONTAINER_ID"]?> = new __MACheckBoxControl({
		containerId: "<?=$arResult["DOM_CONTAINER_ID"]?>",
		resultCallback: <?=(isset($arParams["JS_RESULT_HANDLER"]) ? '"'.$arParams["JS_RESULT_HANDLER"].'"' : 'false')?>,
		ownIds: <?=CUtil::PhpToJsObject($arIds)?>
	});

	<?if(isset($arParams["JS_EVENT_TAKE_CHECKBOXES_VALUES"])):?>
		BX.addCustomEvent('<?=$arParams["JS_EVENT_TAKE_CHECKBOXES_VALUES"]?>',
							function (){ checkboxControl_<?=$arResult["DOM_CONTAINER_ID"]?>.getChecked();});
	<?endif;?>

	<?foreach ($arIds as $idItem):?>
		checkboxControl_<?=$arResult["DOM_CONTAINER_ID"]?>.makeFastButton("<?=$idItem?>");
	<?endforeach;?>

	BX.addCustomEvent('onMappEditEltItemClick', function (params){

		for (var i = checkboxControl_<?=$arResult["DOM_CONTAINER_ID"]?>.ownIds.length - 1; i >= 0; i--)
		{
			if(checkboxControl_<?=$arResult["DOM_CONTAINER_ID"]?>.ownIds[i] == params.id)
			{
				checkboxControl_<?=$arResult["DOM_CONTAINER_ID"]?>.onCheckBoxClick(params.id);
				break;
			}
		}
	});

</script>