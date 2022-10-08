<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (empty($arResult["ELEMENTS_LIST"]))
	return false;

if (!function_exists("__photo_template_default_comments_ending"))
{
	function __photo_template_default_comments_ending($count)
	{
		$text = GetMessage("P_COMMENTS");
		$count = intval($count);
		$iCount = intval($count%100);

		if (!(10 < $iCount && $iCount < 20))
		{
			$count = intval($count % 10);
			if ($count == 1)
				$text = GetMessage("P_COMMENT");
			elseif ($count > 1 && $count < 5)
				$text = GetMessage("P_COMMENTS_2");
		}

		return $text;
	}
}

if (!function_exists("__photo_template_default_shows_ending"))
{
	function __photo_template_default_shows_ending($count)
	{
		$text = GetMessage("P_SHOWS");
		$count = intval($count);
		$iCount = intval($count%100);

		if (!(10 < $iCount && $iCount < 20))
		{
			$count = intval($count % 10);
			if ($count == 1)
				$text = GetMessage("P_SHOW");
			elseif ($count > 1 && $count < 5)
				$text = GetMessage("P_SHOWS_2");
		}

		return $text;
	}
}
$arParams["ID"] = (!empty($arParams["ID"]) ? $arParams["ID"] : "");
$arParams["USE_RATING"] = ($arParams["USE_RATING"] == "Y" ? "Y" : "N");
$arParams["READ_ONLY"] = (!empty($arParams["READ_ONLY"]) ? $arParams["READ_ONLY"] : "");
$arParams["DISPLAY_AS_RATING"] = $arParams["DISPLAY_AS_RATING"];
$arParams["MAX_VOTE"] = $arParams["MAX_VOTE"];
$arParams["VOTE_NAMES"] = $arParams["VOTE_NAMES"];
$arParams["URL"] = trim($arParams["URL"]);

$arParams["USE_COMMENTS"] = ($arParams["USE_COMMENTS"] == "Y" || $arParams["SHOW_COMMENTS"] == "Y" ? "Y" : "N");
$arParams["USE_RATING"] = ($arParams["USE_RATING"] == "Y" || $arParams["SHOW_RATING"] == "Y" ? "Y" : "N");

$arParams["COMMENTS_TYPE"] = (mb_strtolower($arParams["COMMENTS_TYPE"]) == "forum" ? "forum" : "blog");

if ($_REQUEST["return_array"] == "Y")
{
	$APPLICATION->RestartBuffer();

	foreach ($arResult["ELEMENTS_LIST_JS"]	as $key => $arElement)
	{
		if ($arParams["USE_RATING"] == "Y")
		{
			ob_start();
			?><?$GLOBALS["APPLICATION"]->IncludeComponent(
				"bitrix:iblock.vote",
				"ajax",
				Array(
					"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
					"IBLOCK_ID" => $arParams["IBLOCK_ID"],
					"ELEMENT_ID" => $key,
					"READ_ONLY" => $arParams["READ_ONLY"],
					"MAX_VOTE" => $arParams["MAX_VOTE"],
					"VOTE_NAMES" => $arParams["VOTE_NAMES"],
					"DISPLAY_AS_RATING" => $arParams["DISPLAY_AS_RATING"],
					"CACHE_TYPE" => $arParams["CACHE_TYPE"],
					"CACHE_TIME" => $arParams["CACHE_TIME"]
				),
				($this->__component->__parent ? $this->__component->__parent : $component),
				array("HIDE_ICONS" => "Y")
			);?><?
			$text = ob_get_clean();
			$arResult["ELEMENTS_LIST_JS"][$key]["rating"] = preg_replace(
				array("/\<script([^>]+)\>/is", "/\<\/script([^>]*)\>/is", "/(?<=\001)([^\002]+)(?=\002)/is", "/[\n\t\001\002]/", "/\s\s/"),
				array("\001", "\002", "", "", " "),
				$text);
		}
		$arResult["ELEMENTS_LIST_JS"][$key]["comments"] = '';
		if ($arParams["USE_COMMENTS"] == "Y")
		{
			$arResult["ELEMENTS_LIST_JS"][$key]["comments"] = intval($arParams["COMMENTS_TYPE"] != "blog" ?
					$arResult["ELEMENTS_LIST"][$key]["PROPERTIES"]["FORUM_MESSAGE_CNT"]["VALUE"] :
					$arResult["ELEMENTS_LIST"][$key]["PROPERTIES"]["BLOG_COMMENTS_CNT"]["VALUE"]);
			if ($arResult["ELEMENTS_LIST_JS"][$key]["comments"] > 0)
			{
				$arResult["ELEMENTS_LIST_JS"][$key]["comments"] = $arResult["ELEMENTS_LIST_JS"][$key]["comments"]." ".__photo_template_default_comments_ending($arResult["ELEMENTS_LIST_JS"][$key]["comments"]);
			}
			else
			{
				$arResult["ELEMENTS_LIST_JS"][$key]["comments"] = GetMessage("P_TO_COMMENT");
			}
		}
		if ($arResult["ELEMENTS_LIST_JS"][$key]["shows"] > 0)
			$arResult["ELEMENTS_LIST_JS"][$key]["shows"] = $arResult["ELEMENTS_LIST_JS"][$key]["shows"]." ".__photo_template_default_shows_ending($arResult["ELEMENTS_LIST_JS"][$key]["shows"]);
		$arResult["ELEMENTS_LIST_JS"][$key]["width"] = $arResult["ELEMENTS_LIST"][$key]["REAL_PICTURE"]["WIDTH"];
		$arResult["ELEMENTS_LIST_JS"][$key]["height"] = $arResult["ELEMENTS_LIST"][$key]["REAL_PICTURE"]["HEIGHT"];
		$arResult["ELEMENTS_LIST_JS"][$key]["src"] = $arResult["ELEMENTS_LIST"][$key]["REAL_PICTURE"]["SRC"];
	}

	$res = array(
		"elements" => array_values($arResult["ELEMENTS_LIST_JS"]),
		"start_number" => 1,
		"elements_count" => count($arResult['ELEMENTS_LIST_JS']),
		"status" => "end");
	if ($arResult["NAV_RESULT"]->bNavStart)
	{
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
		$res["start_number"] = $number_element;
		$res["elements_count"] = $arResult["NAV_RESULT"]->NavRecordCount;
		$res["status"] = "inprogress";

		if (!empty($_REQUEST["current"]))
		{
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
		}
	}
	$GLOBALS["APPLICATION"]->RestartBuffer();
		?><?=CUtil::PhpToJSObject($res)?><?
	die();
}

$GLOBALS['APPLICATION']->AddHeadScript('/bitrix/js/main/utils.js');
$GLOBALS['APPLICATION']->AddHeadScript("/bitrix/components/bitrix/photogallery/templates/.default/script.js");
$GLOBALS['APPLICATION']->AddHeadScript("/bitrix/components/bitrix/photogallery.detail.list/templates/slide_show/script_player.js");
$GLOBALS['APPLICATION']->AddHeadScript("/bitrix/components/bitrix/photogallery.detail.list/templates/slider_big/script_slider.js");
$GLOBALS['APPLICATION']->AddHeadScript("/bitrix/components/bitrix/photogallery.detail.list/templates/slider_big/script_effects.js");
$GLOBALS['APPLICATION']->AddHeadScript("/bitrix/components/bitrix/photogallery.detail.list/templates/slider_big/script_cursor.js");
if ($arParams["USE_RATING"] == "Y"):
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/iblock.vote/ajax/style.css');
	if ($this->__component->__parent && is_file($_SERVER['DOCUMENT_ROOT'].$this->__component->__parent->__template->__folder."/bitrix/iblock.vote/ajax/script.js"))
	{
		$GLOBALS['APPLICATION']->AddHeadScript($this->__component->__parent->__template->__folder."/bitrix/iblock.vote/ajax/script.js");
	}
	else
	{
		$GLOBALS['APPLICATION']->AddHeadScript("/bitrix/components/bitrix/iblock.vote/ajax/script.js");
	}
endif;
CAjax::Init();

$arSlideParams = array('speed' => 4, 'effects' => 'N');
if ($GLOBALS['USER']->IsAuthorized())
{
	$GLOBALS['APPLICATION']->AddHeadScript('/bitrix/js/main/admin_tools.js');
	$arSlideParams = CUserOptions::GetOption('photogallery', 'slide', array('speed' => 4, 'effects' => 'N'));
}
$arSlideParams = (is_array($arSlideParams) ? $arSlideParams : array());
$arSlideParams['speed'] = ($arSlideParams['speed'] > 0 ? $arSlideParams['speed'] : 4);
$arSlideParams['width'] = 440;
$arSlideParams['height'] = 400;

ob_start();
?>
<div id="bx_slider" style="position:absolute;width:100%; display:none;">
	<div id="bx_slider_container_outer" style="height:<?=$arSlideParams['height']?>px;width:<?=$arSlideParams['width']?>px;">
		<div id="bx_slider_container_header" style="height:20px;width:100%;background-color:white;visibility:hidden;overflow:hidden;">
			<div style="padding: 0 10px 0 10px;">
				<a href="#" id="bx_slider_nav_stop" <?
					?>style="float:right;" <?
					?>onclick="if(player){player.stop();PhotoMenu.PopupHide();} return false;" <?
					?>title="<?=GetMessage("P_CLOSE_TITLE")?>"><span></span></a>
				<div class="bxp-data-pagen"><div><?=GetMessage("P_PHOTOS")?><span id="element_number"></span><?=GetMessage("P_OF")?><span id="element_count"></span></div></div>
			</div>
		</div>
		<div id="bx_slider_container">
			<div id="bx_slider_content_item"></div>
			<div id="bx_slider_nav" style="margin-top:20px;">
				<a href="#" id="bx_slider_nav_prev" hidefocus="true" onclick="if(player){player.step('prev');}return false;" style="display:none;"></a>
				<a href="#" id="bx_slider_nav_next" hidefocus="true" onclick="if(player){player.step('next');}return false;" style="display:none;"></a>
			</div>
			<div id="bx_slider_content_loading">
				<a href="#" id="bx_slider_content_loading_link"><span></span></a>
			</div>
		</div>
	</div>
	<div id="bx_slider_datacontainer_outer" class="bxp-data" style="width:<?=$arSlideParams['width']?>px;">
		<div class="bxp-data-inner">
			<div id="bx_slider_datacontainer" style="display:none;" class="bxp-data-container">
				<div class="bxp-table">
					<table cellpadding="0" cellspasing="0" border="0" class="bxp-table">
						<tr valign="top">
							<td class="bxp-td-player">
								<div class="bxp-mixer-container">
									<div class="bxp-mixer-container-inner">
										<table cellpadding="0" border="0" cellspacing="0" class="bxp-mixer-container-table">
											<tr>
												<td class="bxp-mixer-container-player">
													<a href="#" id="bx_slider_nav_play" <?
														?>onclick="if(player){if (player.params['status'] == 'paused') <?
															?>{player.play(); this.title='<?=GetMessage("P_PAUSE_TITLE")?>';} <?
															?>else {player.stop(); this.title='<?=GetMessage("P_PLAY_TITLE")?>';}} return false;" <?
														?>title="<?=GetMessage("P_PLAY_TITLE")?>"><span></span></a>
												</td>
												<td class="bxp-mixer-container-speed">
													<div id="bx_slider_speed_panel" title="<?=GetMessage("P_SPEED_TITLE")?>">
														<div id="bx_slider_mixers">
															<table cellpadding="0" cellspasing="0" border="0" align="center">
																<tr>
																	<td><a id="bx_slider_mixers_minus"><span></span></a></td>
																	<td><div id="bx_slider_mixers_border">
																		<a id="bx_slider_mixers_cursor" href="#" style="left:<?=intval($arSlideParams['speed'] * 100 /5)?>%;">
																			<span></span>
																		</a></div></td>
																	<td><a id="bx_slider_mixers_plus"><span></span></a></td>
																</tr>
															</table>
														</div>
														<div id="bx_slider_speed_title"><?=GetMessage("P_SPEED")?>:&nbsp;<span id="bx_slider_speed"><?=$arSlideParams['speed']?></span>&nbsp;<?=GetMessage("P_SEK")?></div>
														<div class="empty-clear" style="clear:both;"></div>
													</div>
												</td>
											</tr>
										</table>
									</div>
								</div>
							</td>
						</tr>
					</table>
				</div>
				<div id="bx_caption"></div>
				<div id="bx_caption_additional"></div>
			</div>
			<div class="empty-clear" style="clear:both;"></div>
		</div>
	</div>
</div>
<? $arTemplate = ob_get_clean(); ?>
<script>
var oPhotoObjects = {
	min_slider_width: <?=$arSlideParams['width']?>,
	min_slider_height: <?=$arSlideParams['height']?>
};

window.__photo_params = {
	'user_id' : <?=intval($GLOBALS['USER']->GetID())?>,
	'speed' : <?=$arSlideParams['speed']?>,
	'effects' : <?=($arSlideParams['effects'] == "Y" ? "true" : "false");?>,
	'template' : ('<div class="photo-title"><a href="#url#">#title#</a></div>' + <?
	if ($arParams["USE_RATING"] == "Y"):
		?> '<div class="photo-rating">#rating#</div>' + <?
	endif;
		?>'<table cellpadding="0" border="0" cellspacing="0"><tr>' + <?
	if ($arParams["USE_COMMENTS"] == "Y"):
		?>'<td class="td-slider-first"><div class="photo-comments"><a href="#url#">#comments#</a></div></td>' + <?
	endif;
		?>'<td class="<?=($arParams["USE_COMMENTS"] == "Y" ? "td-slider-last" : "td-slider-single")?>"><div class="photo-shows">#shows#</div></td></tr></table>'),
	'template_additional' : '<div class="photo-description">#description#</div>'
	};

function __photo_to_init_slider()
{
	var res = document.getElementsByTagName('a');
	for (var ii = 0; ii < res.length; ii++)
	{
		if (!res[ii].id || !res[ii].id.match(/photo\_(\d+)/gi))
			continue;

		res[ii].onclick = function(){ setTimeout(new Function("photo_init_big_slider(" + this.id.replace('photo_', '') + ");"), 10); return false; }
		res[ii].ondbclick = function(){ jsUtils.Redirect([], this.href); }
		var div = document.createElement('div');
		div.style.position = "absolute";
		div.style.display = "none";
		div.className = "photo-photo-item-popup";
		div.id = res[ii]["id"] + '__id';
		div.title = '<?=CUtil::JSEscape(GetMessage("P_DETAIL_INFO"))?>';

		div.onshow = new Function(
			"this.style.visibility = 'hidden'; " +
			"this.style.display = 'block'; " +
			"var width = parseInt(this.offsetWidth); " +
			"var height = parseInt(this.offsetHeight); " +
			" if (width > 0 && height > 0) " +
			" { " +
				" this.style.top = (this.parentNode.offsetHeight - height) + 'px'; " +
				" this.style.left = (this.parentNode.offsetWidth - width) + 'px'; " +
			" } " +
			" this.style.visibility = 'visible'; " +
			" this.onshow = function() {this.style.display = 'block';} ");


		div.onmouseout = function()
		{
			this.bxMouseOver = 'N';
			var __this = this;
			setTimeout(
				function()
				{
					if (__this.nextSibling && __this.nextSibling.bxMouseOver != "Y")
					{
						__this.style.display = 'none';
					}
				},
				100);
		}
		div.onmouseover = function()
		{
			this.bxMouseOver = 'Y';
		}

		eval("div.onclick = function(e){jsUtils.PreventDefault(e); jsUtils.Redirect([], '" + res[ii].href + "');};");
		res[ii].parentNode.insertBefore(div, res[ii]);
		res[ii].onmouseover = function()
		{
			this.previousSibling.onshow();
			this.bxMouseOver = 'Y';
		};
		res[ii].onmouseout = function()
		{
			this.bxMouseOver = 'N';
			var __this = this;
			setTimeout(
				function()
				{
					if (__this.previousSibling && __this.previousSibling.bxMouseOver != "Y")
					{
						__this.previousSibling.style.display = 'none';
					}
				},
				100);
		}
	}
}

__photo_to_init_slider();
function photo_init_big_slider(id)
{
	var div = document.getElementById('bx_slider');
	if (!div)
	{
		var res = document.body.appendChild(document.createElement("DIV"));
		res.innerHTML = '<?=CUtil::JSEscape(preg_replace("/\t|\n/is", "", $arTemplate))?>';
		div = document.getElementById('bx_slider');
		div.style.zIndex = 11000;
	}

	var res = GetImageWindowSize();

	PhotoMenu.PopupShow(
		div,
		{
			'left' : '0',
			'top' : (res['top'] + parseInt((res['height'] - <?=$arSlideParams['height']?>)/2))
		},
		false,
		false,
		{
			'AfterHide' : function() {
				window.location.hash = 'gallery';
				if (window.player)
				{
					window.player.stop();
					// remove events
					jsUtils.removeEvent(document, "keypress", __checkKeyPress);
				}
			}
		}
	);
	var res = false;
	if (window['__photo_result'] && window['__photo_result']['elements'] && id > 0)
	{
		for (var ii = 0; ii < window['__photo_result']['elements'].length; ii++)
		{
			if (window['__photo_result']['elements'][ii]['id'] == id)
			{
				res = true;
				break;
			}
		}
	}
	if (window.__photo_result && res)
	{
		__show_slider(id, '<?=CUtil::JSEscape($arParams["URL"])?>', window.__photo_result);
	}
	else
	{
		var url = '<?=CUtil::JSEscape($arParams["URL"])?>';
		if (url.length <= 0) { url = window.location.href; }
		url = url.replace('show_array=Y', '').replace(/ELEMENT\_ID\=(\d+)/gi, '').replace(/\#(.*)/gi, '');
		var TID = jsAjax.InitThread();
		eval("jsAjax.AddAction(TID, function(data){try{eval('window.__photo_result=' + data + ';');__show_slider(" + id + ", '<?=CUtil::JSEscape($arParams["URL"])?>', window.__photo_result);}catch(e) {PhotoMenu.PopupHide();}});");
		jsAjax.Send(TID, url, {'return_array' : 'Y', 'direction' : 'current', 'ELEMENT_ID' : id, 'current' : {'id' : id}});
	}
	return false;
}

if (window.location.hash.substr(0, 6) == '#photo')
{
	var __photo_tmp_interval = setInterval(function()
	{
		try {
			if (bPhotoMainLoad === true && bPhotoSliderLoad == true && bPhotoPlayerLoad == true && bPhotoEffectsLoad === true && bPhotoCursorLoad === true && jsAjax && jsUtils)
			{
				photo_init_big_slider(window.location.hash.substr(6));
				clearInterval(__photo_tmp_interval);
			}
		} catch (e) { }
	}, 500);
}
</script>