<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (empty($arResult["ELEMENTS_LIST"])):
	return true;
elseif (!$this->__component->__parent || strpos($this->__component->__parent->__name, "photogallery") === false):
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/photogallery/templates/.default/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/photogallery/templates/.default/themes/gray/style.css');
endif;
$GLOBALS['APPLICATION']->AddHeadScript('/bitrix/js/main/utils.js');
$GLOBALS['APPLICATION']->AddHeadScript("/bitrix/components/bitrix/photogallery/templates/.default/script.js");
$GLOBALS['APPLICATION']->AddHeadScript("/bitrix/components/bitrix/photogallery.detail.list/templates/slide_show/script_player.js");
CAjax::Init();
/********************************************************************
				Input params
********************************************************************/
$arParams["ELEMENT_ID"] = intVal($arParams["ELEMENT_ID"]); // active element
$arParams["SLIDER_COUNT_CELL"] = (intVal($arParams["SLIDER_COUNT_CELL"]) <= 0 ? 4 : $arParams["SLIDER_COUNT_CELL"]);

$temp = array("STRING" => preg_replace("/[^0-9]/is", "/", $arParams["THUMBNAIL_SIZE"]));
list($temp["WIDTH"], $temp["HEIGHT"]) = explode("/", $temp["STRING"]);
$arParams["THUMBNAIL_SIZE"] = (intVal($temp["WIDTH"]) > 0 ? intVal($temp["WIDTH"]) : 200);
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
while ($first > 0 && $last >= count($keys))
{
	$last--; $first--;
}
if ($last >= count($keys))
{
	$last = (count($keys) - 1);
}
$active_element = $keys[$first];
for ($ii = $first; $ii <= $last; $ii++)
{
	$arResult["ELEMENTS_CURR"][] = $arResult["ELEMENTS_LIST_JS"][$keys[$ii]];
}

$arParams["SLIDER_COUNT_CELL"] = intVal($arParams["SLIDER_COUNT_CELL"]);
$panelHeight =
	$arParams["THUMBNAIL_SIZE"]
	+ 1*2 		// image border
	+ 1*2 + 5*2	// anchor border + padding
	+ 2*2		// td padding
	+ 5*2		// main td padding
	+ 1 + 5;	// panel border + rate
$cellWidth = $arParams["THUMBNAIL_SIZE"]
	+ 1*2 		// image border
	+ 1*2 + 5*2	// anchor border + padding
	+ 2*2 + 5;	// td padding + rate
$cellHeight = $arParams["THUMBNAIL_SIZE"]
	+ 1*2 		// image border
	+ 1*2 + 5*2	// anchor border + padding
	+ 2*2 + 5;	// td padding + rate

if (($arParams["SHOW_PAGE_NAVIGATION"] == "top" || $arParams["SHOW_PAGE_NAVIGATION"] == "both") && !empty($arResult["NAV_STRING"])):
?><div class="photo-navigation photo-navigation-top">
	<?=$arResult["NAV_STRING"]?>
</div><?
endif;

?><div class="photo-photos photo-photos-slider"><?
if ($arParams["SHOW_DESCRIPTION"] != "N")
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

?>
<table class="photo-navigation" cellpadding="0" cellspacing="0" border="0" style="height:<?=$panelHeight?>px;">
	<tr>
		<td width="20px">
			<table class="photo-left" cellpadding="0" cellspacing="0" border="0" style="height:<?=$panelHeight?>px;">
				<tr style="height:<?=($panelHeight - 28)?>px;">
					<td	class="photo-left-top-disabled" id="photo_prev_<?=$package_id?>" onclick="if(window['player_slider_<?=$package_id?>']){window['player_slider_<?=$package_id?>'].step('prev');}">
						<div class="empty"></div></td></tr>
				<tr style="height:28px;">
					<td class="photo-left-bottom-disabled" id="photo_first_<?=$package_id?>" onclick="if(window['player_slider_<?=$package_id?>']){window['player_slider_<?=$package_id?>'].step('first');}">
						<div class="empty"></div></td></tr>
			</table>
		</td>
		<td id="slider_window_<?=$package_id?>" class="slider_window" style="width:<?=($cellWidth*$arParams["SLIDER_COUNT_CELL"] + 10)?>px;"><?
			?><table id="table_photo_photos_<?=$package_id?>" class="table_photo_photos" border="0" cellpadding="0" cellspacing="0" style="height:<?=($panelHeight-10)?>px;"><tbody><tr><?
	$i_cnt = 1;
	foreach ($arResult["ELEMENTS_CURR"] as $res):
		?><td id="td_<?=$package_id?>_<?=$i_cnt?>" style="width:<?=$cellWidth?>px;"><?
			?><a href="<?=htmlspecialcharsbx($res["url"])?>" title="<?=$res["title"]?>" <?
				?>style="width:<?=($res["width"] + 12)?>px;"<?
				?><?=($res["id"] == $arParams["ELEMENT_ID"] ? " class='active'" : "")?>><?
				?><img src="<?=$res["src"]?>" <?
					?>width="<?=$res["width"]?>" height="<?=$res["height"]?>" <?
					?>alt="<?=$res["alt"]?>" title="<?=$res["title"]?>" <?
					?>border="0" /><?
			?></a><?
		?></td><?
		$i_cnt++;
	endforeach;
	?></tr></tbody></table></td>
		<td width="20px">
			<table class="photo-right" cellpadding="0" cellspacing="0" border="0" style="height:<?=$panelHeight?>px;">
				<tr style="height:<?=($panelHeight - 28)?>px;">
					<td	class="photo-right-top-disabled" id="photo_next_<?=$package_id?>" onclick="if(window['player_slider_<?=$package_id?>']){window['player_slider_<?=$package_id?>'].step('next');}">
						<div class="empty"></div></td></tr>
				<tr style="height:28px;">
					<td class="photo-right-bottom-disabled" id="photo_last_<?=$package_id?>" onclick="if(window['player_slider_<?=$package_id?>']){window['player_slider_<?=$package_id?>'].step('last');}">
						<div class="empty"></div></td></tr>
			</table>
		</td>
	</tr>
</table>
</div>
<?
if (count($arResult["ELEMENTS_CURR"]) < $count_elements)
{
?>
<script>
function to_init_<?=$package_id?>()
{
	var is_loaded = window.bPhotoMainLoad === true && bPhotoPlayerLoad === true && window.jsAjax && window.jsUtils;

	if (!is_loaded)
		return setTimeout(to_init_<?=$package_id?>, 100);

	var SliderCopy = new BPCSlider(
		<?=CUtil::PhpToJSObject(array_values($arResult['ELEMENTS_LIST_JS']))?>, // array of elements
		<?=$active_element?>, // active element
		<?=intVal($count_elements)?>, // count elements
		<?=$number_element?>,
		'');

	SliderCopy.windowsize = <?=intVal($arParams["SLIDER_COUNT_CELL"])?>;
	SliderCopy.ShowItem = function(item_id, number)
	{
		var res = this.oSource.Data[item_id];
		BX('td_<?=$package_id?>_' + number).innerHTML = '<a href="' + res['url'] + '" ' +  <?
		if ($arParams["ELEMENT_ID"] > 0):
			?>(res["id"] + '' == '<?=$arParams["ELEMENT_ID"]?>' ? ' class="active"' : '') + <?
		endif;?>
		'><img src="' + res['src'] + '" width="' + res['width'] + '" height="' + res['height'] + '" /></a>';

		var event_names = {
			onclick : '',
			onmouseover : '',
			onmouseout : '',
			onmousedown : '',
			onmouseup : ''
		};
		var arrID = {
			'photo_prev_<?= $package_id?>' : 'prev',
			'photo_first_<?=$package_id?>' : 'prev',
			'photo_last_<?=$package_id?>' : 'next',
			'photo_next_<?= $package_id?>' : 'next'
		};

		for (var id in arrID)
		{
			var node = BX(id);
			if (!node.__className)
			{
				node.__className = node.className.replace('-disabled', '');
				for (event_name in event_names)
				{
					if (event_name == 'onmouseover' || event_name == 'onmouseup')
						node['__' + event_name] = function(){this.className = this.__className + ' ' + this.__className + '-over'};
					else if (event_name == 'onmouseout')
						node['__' + event_name] = function(){this.className = this.__className};
					else if (event_name == 'onmousedown')
						node['__' + event_name] = function(){this.className = this.__className + ' ' + this.__className + '-active'};
					else
						node['__' + event_name] = node[event_name];
				}
			}

			if (this.active == 1 && arrID[id] == 'prev' ||
				((this.active + this.windowsize-1) >= <?=intVal($count_elements)?>) && arrID[id] == 'next')
			{
				node.className = node.__className + "-disabled";
				for (event_name in event_names)
					node[event_name] = function() {return false;};
			}
			else
			{
				node.className = node.__className;
				for (event_name in event_names)
					node[event_name] = node['__' + event_name];
			}
		}
	};

	SliderCopy.oSource.events['OnBeforeSendData'] = function()
	{
		arguments[0][1]['package_id'] = '<?=$package_id?>';
		return arguments[0][1];
	};
	SliderCopy.ShowSlider();

	var player_slider = new BPCPlayer(SliderCopy);

	var oWaitWindow = false;
	player_slider.events['OnWaitItem'] = function()
	{
		if (!oWaitWindow)
			oWaitWindow = BX.showWait(BX('table_photo_photos_<?=$package_id?>'));
	}
	player_slider.events['OnShowItem'] = function()
	{
		if (oWaitWindow)
		{
			BX.closeWait(false, oWaitWindow);
			oWaitWindow = false;
		}
	}

	window.player_slider_<?=$package_id?> = player_slider;
}

if (window.attachEvent)
	window.attachEvent("onload", to_init_<?=$package_id?>);
else if (window.addEventListener)
	window.addEventListener("load", to_init_<?=$package_id?>, false);
else
	setTimeout(to_init_<?=$package_id?>, 100);
</script>
<?
}
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