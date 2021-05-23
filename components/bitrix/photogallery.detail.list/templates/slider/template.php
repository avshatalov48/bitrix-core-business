<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (empty($arResult["ELEMENTS_LIST"])):
	return true;
elseif (!$this->__component->__parent || mb_strpos($this->__component->__parent->__name, "photogallery") === false):
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/photogallery/templates/.default/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/photogallery/templates/.default/themes/gray/style.css');
endif;
$GLOBALS['APPLICATION']->AddHeadScript('/bitrix/js/main/utils.js');
$GLOBALS['APPLICATION']->AddHeadScript("/bitrix/components/bitrix/photogallery/templates/.default/script.js");
$GLOBALS['APPLICATION']->AddHeadScript("/bitrix/components/bitrix/photogallery.detail.list/templates/slide_show/script_player.js");
CAjax::Init();
CUtil::InitJSCore(array('window'));
/********************************************************************
				Input params
********************************************************************/
$arParams["ELEMENT_ID"] = intval($arParams["ELEMENT_ID"]); // active element
$arParams["SLIDER_COUNT_CELL"] = (intval($arParams["SLIDER_COUNT_CELL"]) <= 0 ? 4 : $arParams["SLIDER_COUNT_CELL"]);

$temp = array("STRING" => preg_replace("/[^0-9]/is", "/", $arParams["THUMBNAIL_SIZE"]));
list($temp["WIDTH"], $temp["HEIGHT"]) = explode("/", $temp["STRING"]);
$arParams["THUMBNAIL_SIZE"] = (intval($temp["WIDTH"]) > 0 ? intval($temp["WIDTH"]) : 200);
if ($arParams["PICTURES_SIGHT"] != "standart" && $arParams["PICTURES"][$arParams["PICTURES_SIGHT"]]["size"] > 0)
	$arParams["THUMBNAIL_SIZE"] = $arParams["PICTURES"][$arParams["PICTURES_SIGHT"]]["size"];
$arParams["THUMBNAIL_SIZE"] = ($arParams["THUMBNAIL_SIZE"] > 0 ? $arParams["THUMBNAIL_SIZE"] : 200);
/********************************************************************
				/Input params
********************************************************************/
$package_id = md5(serialize(array("slider", $arParams["FILTER"], $arParams["SORTING"], $arParams["PICTURES_SIGHT"])));
$number_element = 1;
$count_elements = count($arResult["ELEMENTS_LIST_JS"]);
$active_element = 0;
if ($arParams["SELECT_SURROUNDING"] === "Y")
{
	$tmp = reset($arResult["ELEMENTS_LIST_JS"]);
	$number_element = $arResult["ELEMENTS_LIST"][$tmp["id"]]["RANK"];
	$count_elements = $arResult["ELEMENTS_CNT"];
}

if ($arResult["NAV_RESULT"]->bNavStart)
{
	if ($arParams["PAGE_ELEMENTS"] < $arParams["SLIDER_COUNT_CELL"])
		$arParams["SLIDER_COUNT_CELL"] = $arParams["PAGE_ELEMENTS"];

	$count_elements = $arResult["NAV_RESULT"]->NavRecordCount;
	$number_element = ($arResult["NAV_RESULT"]->NavPageNomer - 1) * $arResult["NAV_RESULT"]->NavPageSize + 1;
	if ($arResult["NAV_RESULT"]->bDescPageNumbering)
	{
		$number_element = 1;
		if ($arResult["NAV_RESULT"]->NavPageNomer < $arResult["NAV_RESULT"]->NavPageCount)
		{
			$number_element += $arResult["NAV_RESULT"]->NavRecordCount % $arResult["NAV_RESULT"]->NavPageSize + $arResult["NAV_RESULT"]->NavPageSize;
			$number_element += ($arResult["NAV_RESULT"]->NavPageSize * ($arResult["NAV_RESULT"]->NavPageCount - $arResult["NAV_RESULT"]->NavPageNomer - 1)) ;

		}
	}

	if ($_REQUEST["package_id"] == $package_id && !empty($_REQUEST["current"]))
	{
		$res = array(
			"elements" => array_values($arResult["ELEMENTS_LIST_JS"]),
			"start_number" => $number_element,
			"status" => "inprogress");

		if ($arResult["NAV_RESULT"]->bDescPageNumbering)
		{
			if ($arResult["NAV_RESULT"]->NavPageNomer == 1)
				$res["status"] = "end";
			elseif ($arResult["NAV_RESULT"]->NavPageNomer == $arResult["NAV_RESULT"]->NavPageCount)
				$res["status"] = "start";
		}
		else
		{
			if ($arResult["NAV_RESULT"]->NavPageNomer == $arResult["NAV_RESULT"]->NavPageCount)
				$res["status"] = "end";
			elseif ($arResult["NAV_RESULT"]->NavPageNomer == 1)
				$res["status"] = "start";
		}
		$res["from_slider"] = "Y";
		$APPLICATION->RestartBuffer();
		?><?=CUtil::PhpToJSObject($res)?><?
		die();
	}
}
/************** Default images setlist *****************************/
$arResult["ELEMENTS_CURR"] = array();
$keys = array_keys($arResult["ELEMENTS_LIST_JS"]);
$first = (in_array($arParams["ELEMENT_ID"], $keys) ? array_search($arParams["ELEMENT_ID"], $keys) : 0);
$last = $first + $arParams["SLIDER_COUNT_CELL"] - 1;

if (($arParams["SHOW_PAGE_NAVIGATION"] == "top" || $arParams["SHOW_PAGE_NAVIGATION"] == "both") && !empty($arResult["NAV_STRING"])):
?><div class="photo-navigation photo-navigation-top">
	<?=$arResult["NAV_STRING"]?>
</div><?
endif;

?><div class="photo-photos photo-photos-slider"><?
if ($arParams["SHOW_DESCRIPTION"] != "N" && false)
{
?>
	<div class="photo-info-box photo-info-box-photos">
		<div class="photo-info-box-inner">
			<?=GetMessage("P_ALL_PHOTO")?>
		</div>
	</div>
	<br />
<?
}

$i_cnt = 1; $i_reserve = 20;
$i_leftward = 0; $b_founded_active = false;
ob_start();
foreach ($arResult["ELEMENTS_LIST_JS"] as $key => $res):
?>
			<div class="photo-slider-item <?=($res["id"] == $arParams["ELEMENT_ID"] ? " photo-slider-item-active" : "")?>" id="item_<?=$res["id"]?>">
				<table class="photo-slider-thumb" cellpadding="0">
					<tr>
						<td>
						<?if ($res["id"] == $arParams["ELEMENT_ID"]):?>
							<div class="image">
								<img border="0" width="<?=$res["width"]?>" height="<?=$res["height"]?>" alt="<?=$res["alt"]?>" <?
									?>src="<?=$res["src"]?>" title="<?=$res["title"]?>" />
							</div>
						<?else:?>
							<a href="<?=htmlspecialcharsbx($res["url"])?>">
								<img border="0" width="<?=$res["width"]?>" height="<?=$res["height"]?>" alt="<?=$res["alt"]?>" <?
									?>src="<?=$res["src"]?>" title="<?=$res["title"]?>" />
							</a>
						<?endif;?>
						</td>
					</tr>
				</table>
			</div>
<?
	$i_cnt++;
	$b_founded_active = ($b_founded_active || $res["id"] == $arParams["ELEMENT_ID"]);
	if (!$b_founded_active)
		$i_leftward += ($res["width"] + $i_reserve);
endforeach;
if (!$b_founded_active)
	$i_leftward = 0;
$str = ob_get_clean();
?>
<div class="photo-slider">
	<div class="photo-slider-inner">
		<div class="photo-slider-container">
			<span id="prev_<?=$package_id?>" class="<?=(true ? "photo-prev-enabled" : "photo-prev-disabled")?>"></span>
			<div class="photo-slider-data" id="slider_window_<?=$package_id?>"><?
				?><div class="photo-slider-data-list" <?=($i_leftward > 0 ? 'style="left: -'.$i_leftward.'px;"' : '')?>>
					<?=$str?>
				</div>
			</div>
			<span id="next_<?=$package_id?>" class="<?=(true ? "photo-next-enabled" : "photo-next-disabled")?>"></span>
		</div>
	</div>
</div>
</div>

<style>
.photo-slider-container, .photo-slider-container .photo-slider-data-list{height:<?= ($arParams["THUMBNAIL_SIZE"] + 20)?>px;}
.photo-slider-container .photo-slider-item .photo-slider-thumb {height:<?=($arParams["THUMBNAIL_SIZE"] + 20)?>px;}
</style>

<script>
function __photo_init_slider<?=$package_id?>()
{
	if (window['BPCStretchSlider'] && window['BX'])
	{
		var __slider = new BPCStretchSlider(
			<?=CUtil::PhpToJSObject(array_values($arResult["ELEMENTS_LIST_JS"]))?>,
			<?=intval($number_element)?>,
			<?=intval($count_elements)?>,
			<?=$arParams["ELEMENT_ID"]?>);
		__slider.pack_id = '<?= $package_id?>';
		__slider.CreateSlider();
		return true;
	}
	setTimeout("__photo_init_slider<?=$package_id?>();", 70);
}

// TODO: BX.ready
if (window.attachEvent)
	window.attachEvent("onload", __photo_init_slider<?=$package_id?>);
else if (window.addEventListener)
	window.addEventListener("load", __photo_init_slider<?=$package_id?>, false);
else
	setTimeout(__photo_init_slider<?=$package_id?>, 100);
</script>
<?

if (($arParams["SHOW_PAGE_NAVIGATION"] == "bottom" || $arParams["SHOW_PAGE_NAVIGATION"] == "both") && !empty($arResult["NAV_STRING"])):
?>
<div class="photo-navigation photo-navigation-bottom">
	<?=$arResult["NAV_STRING"]?>
</div>
<?
endif;
if ($arParams["INCLUDE_SLIDER"] == "Y"):
	$this->__component->setTemplateName("slider_big");
	$this->__component->IncludeComponentTemplate();
endif;
?>