<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<div class="item_list_component">
	<ul id="section_items">
<?
if ($_REQUEST["ajax_get_page"] == "Y")
{
	$APPLICATION->RestartBuffer();
}
?>
<?foreach($arResult["ITEMS"] as $cell=>$arElement):?>
	<li id="<?=$this->GetEditAreaId($arElement['ID']);?>" onclick="app.openNewPage('<?=$arElement["DETAIL_PAGE_URL"]?>')">
		<?
		$this->AddEditAction($arElement['ID'], $arElement['EDIT_LINK'], CIBlock::GetArrayByID($arParams["IBLOCK_ID"], "ELEMENT_EDIT"));
		$this->AddDeleteAction($arElement['ID'], $arElement['DELETE_LINK'], CIBlock::GetArrayByID($arParams["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BCS_ELEMENT_DELETE_CONFIRM')));

		$sticker = "";
		if (array_key_exists("PROPERTIES", $arElement) && is_array($arElement["PROPERTIES"]))
		{
			foreach (Array("SPECIALOFFER", "NEWPRODUCT", "SALELEADER") as $propertyCode)
				if (array_key_exists($propertyCode, $arElement["PROPERTIES"]) && intval($arElement["PROPERTIES"][$propertyCode]["PROPERTY_VALUE_ID"]) > 0)
				{
					$sticker = toLower($arElement["PROPERTIES"][$propertyCode]["NAME"]);
					break;
				}
		}
		?>
		<table>
			<tr>
				<td>
				<?if(is_array($arElement["PREVIEW_PICTURE"])):?>
					<a href="<?=$arElement["DETAIL_PAGE_URL"]?>" class="item_list_img"><span><img src="<?=$arElement["PREVIEW_PICTURE"]["SRC"]?>" alt="<?=$arElement["NAME"]?>" title="<?=$arElement["NAME"]?>" /></span></a>
				<?elseif(is_array($arElement["DETAIL_PICTURE"])):?>
					<a href="<?=$arElement["DETAIL_PAGE_URL"]?>" class="item_list_img"><span><img src="<?=$arElement["DETAIL_PICTURE"]["SRC"]?>"  alt="<?=$arElement["NAME"]?>" title="<?=$arElement["NAME"]?>" /></span></a>
				<?endif?>
				</td>
				<td class="">

					<span class="item_list_title_lable"><?=$sticker?></span>
					<div class="item_list_title">
						<a href="<?=$arElement["DETAIL_PAGE_URL"]?>"><?=$arElement["NAME"]?><?if ($sticker):?><?endif?></a>
					</div>

					<?if (is_array($arElement["DISPLAY_PROPERTIES"])):?>
					<div class="item_item_description_text">
						<ul>
							<?foreach($arElement["DISPLAY_PROPERTIES"] as $pid=>$arProperty):?>
							<li><?=$arProperty["NAME"]?>:
								<?if(is_array($arProperty["DISPLAY_VALUE"]))
									echo implode(" / ", $arProperty["DISPLAY_VALUE"]);
								else
									echo $arProperty["DISPLAY_VALUE"];?>
							</li>
							<?endforeach?>
						</ul>
					</div>
					<?endif?>
					<?if(!is_array($arElement["OFFERS"]) || empty($arElement["OFFERS"])):?>
						<?foreach($arElement["PRICES"] as $code=>$arPrice):?>
							<?if($arPrice["CAN_ACCESS"]):?>
								<?//=$arResult["PRICES"][$code]["TITLE"];?>
								<?if($arPrice["DISCOUNT_VALUE"] < $arPrice["VALUE"]):?>
									<div class="itemlist_price_container oldprice">
										<span class="item_price"><?=$arPrice["PRINT_DISCOUNT_VALUE"]?></span>
										<span class="item_price_old whsnw"><?=$arPrice["PRINT_VALUE"]?></span>
									</div>
								<?else:?>
									<div class="itemlist_price_container">
										<span class="item_price"><?=$arPrice["PRINT_VALUE"]?></span>
									</div>
								<?endif;?>
							<?endif;?>
						<?endforeach;?>
					<?endif?>
				</td>
			</tr>
		</table>
	</li>
<?endforeach;?>
<?
if ($_REQUEST["ajax_get_page"] == "Y")
{
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
	die();
}
else
{
?>
	</ul>
</div>
<?
$ajaxPath = CHTTP::urlAddParams(htmlspecialcharsback(POST_FORM_ACTION_URI), array("ajax_get_page" => "Y", "PAGEN_1" => "#page#"));
?>
<script type="text/javascript">
	app.setPageTitle({"title" : "<?=CUtil::JSEscape(htmlspecialcharsback($arResult["NAME"]))?>"});

	window.pagenNum = 1;
	window.onscroll = function ()
	{
		var preloadCoefficient = 2;

		var clientHeight = document.documentElement.clientHeight ? document.documentElement.clientHeight : document.body.clientHeight;
		var documentHeight = document.documentElement.scrollHeight ? document.documentElement.scrollHeight : document.body.scrollHeight;
		var scrollTop = window.pageYOffset ? window.pageYOffset : (document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop);

		if((documentHeight - clientHeight*(1+preloadCoefficient)) <= scrollTop)
		{
			getBottomItems();
		}
	}

	function getBottomItems()
	{
		if (!('<?=$arResult["NAV_STRING"]?>' > <?=$arParams["PAGE_ELEMENT_COUNT"]?>*window.pagenNum))
			return;

		window.pagenNum++;

		var path = "<?=CUtil::JSEscape($ajaxPath)?>";
		path = path.replace("#page#", window.pagenNum);

		BX.ajax({
			timeout:   30,
			method:   'POST',
			url: path,
			processData: false,
			onsuccess: function(sectionHTML){
				var sectionDomObjCont = BX("new_items_container");

				if(!sectionDomObjCont)
				{
					sectionDomObjCont= document.createElement("DIV");
					sectionDomObjCont.id = "new_items_container";
					sectionDomObjCont.style.display = "none";
				}
				sectionDomObjCont.innerHTML = sectionHTML;

				var sectionsObj = BX.findChildren(sectionDomObjCont, {tagName : "li"}, false);

				for (var i in sectionsObj)
				{
					BX("section_items").appendChild(sectionsObj[i]);
				}
			},
			onfailure: function(){
			}
		});
	};

</script>
<?
}
?>

