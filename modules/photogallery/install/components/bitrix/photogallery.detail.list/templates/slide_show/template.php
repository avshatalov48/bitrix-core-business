<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (empty($arResult["ELEMENTS_LIST"])):
	ShowNote(GetMessage("P_ELEMENTS_LIST_IS_EMPTY"));
	return false;
endif;

$GLOBALS['APPLICATION']->RestartBuffer();

$number_element = 1;
$count_elements = count($arResult['ELEMENT_FOR_JS']);

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
	$count_elements = $arResult["NAV_RESULT"]->NavRecordCount;
	if (!empty($_REQUEST["current"]))
	{
		$res = array(
			"elements" => $arResult["ELEMENT_FOR_JS"],
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
		?><?=CUtil::PhpToJSObject($res)?><?
		die();
	}
}

$arParams["ELEMENT_ID"] = intVal($arParams["ELEMENT_ID"]);

?><html><head>
<link href="/bitrix/components/bitrix/photogallery.detail.list/templates/slide_show/style.css" type="text/css" rel="stylesheet" />
<link href="/bitrix/templates/.default/ajax/ajax.css" type="text/css" rel="stylesheet" />
<script type="text/javascript" src="/bitrix/components/bitrix/photogallery.detail.list/templates/slide_show/script_player.js"></script>
<script type="text/javascript" src="/bitrix/components/bitrix/photogallery/templates/.default/script.js"></script>
<script type="text/javascript" src="/bitrix/js/main/ajax.js"></script>
<script type="text/javascript" src="/bitrix/js/main/core/core.js"></script>
<script language="JavaScript" type="text/javascript">
function SetBackGround(div)
{
		if (!div){return false;}
		document.body.style.backgroundColor = div.style.backgroundColor;
}
</script>
<title><?
if (!empty($arResult["SECTION"])):
	?><?=$arResult["SECTION"]["NAME"]?><?
else:
	?><?=GetMessage("P_TITLE")?><?
endif;
?></title>
</head>
<body class="photo-slide-show">
<?
$GLOBALS['APPLICATION']->AddHeadScript('/bitrix/js/main/utils.js');
?>
<div class="image-upload" id="image-upload"><?=GetMessage("P_LOADING")?></div>
<table width="100%" height="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td align=right width="0">
			<table align=center cellpadding=0 cellspacing=2 border=0>
				<tr><td><div style="width:18px; height:18px; background-color:#FFFFFF;" onmouseover="SetBackGround(this);"></div></td></tr>
				<tr><td><div style="width:18px; height:18px; background-color:#E5E5E5;" onmouseover="SetBackGround(this);"></div></td></tr>
				<tr><td><div style="width:18px; height:18px; background-color:#CCCCCC;" onmouseover="SetBackGround(this);"></div></td></tr>
				<tr><td><div style="width:18px; height:18px; background-color:#B3B3B3;" onmouseover="SetBackGround(this);"></div></td></tr>
				<tr><td><div style="width:18px; height:18px; background-color:#999999;" onmouseover="SetBackGround(this);"></div></td></tr>
				<tr><td><div style="width:18px; height:18px; background-color:#808080;" onmouseover="SetBackGround(this);"></div></td></tr>
				<tr><td><div style="width:18px; height:18px; background-color:#666666;" onmouseover="SetBackGround(this);"></div></td></tr>
				<tr><td><div style="width:18px; height:18px; background-color:#4D4D4D;" onmouseover="SetBackGround(this);"></div></td></tr>
				<tr><td><div style="width:18px; height:18px; background-color:#333333;" onmouseover="SetBackGround(this);"></div></td></tr>
				<tr><td><div style="width:18px; height:18px; background-color:#1A1A1A;" onmouseover="SetBackGround(this);"></div></td></tr>
				<tr><td><div style="width:18px; height:18px; background-color:#000000;" onmouseover="SetBackGround(this);"></div></td></tr>
			</table>
		</td>
		<td align="center" width="100%" valign="center" style="padding-top:5px;">
			<div id="bx_slider_content_item_1"></div>
		</td>
	</tr>
</table>

<div id="control_container">
	<div id="navigator_container" style="display:none;">
		<table cellpadding="0" cellspacing="0" border="0" width="100%" class="photo-player-panel">
			<tr valign="middle">
				<td align="left" width="40%"><div id="title"><?=GetMessage("P_NAME")?></div></td>
				<td align="center" width="20%">
					<table cellpadding="0" cellspacing="0" border="0" class="photo-player-panel-buttons">
						<tr>
							<td><div class="photo-player-button" id="prev" onclick="if(player){player.step('prev');}"></div></td>
							<td><div class="photo-player-button" id="play" onclick="if(player){player.PlayStop();}"></div></td>
							<td><div class="photo-player-button" id="next" onclick="if(player){player.step('next');}"></div></td>
						</tr>
					</table>
				</td>
				<td align="center" width="15%"><span id="counter">1</span> <?=GetMessage("P_OF")?> <?=$count_elements?> </td>
				<td align="center" width="15%">
					<table cellpadding="2" cellspacing="0" border="0" class="inner">
						<tr>
							<td><div class="photo-player-button" id="time_minus" onclick="if(player && player.params['period'] > 1){player.params['period']--;<?
								?>BX('time_container').innerHTML = player.params['period'] + '';}"></div></td>
							<td><div id="time_container">2</div></td>
							<td><?=GetMessage("P_SEK")?></td>
							<td><div class="photo-player-button" id="time_plus" onclick="if(player){player.params['period']++;<?
								?>BX('time_container').innerHTML = player.params['period'] + '';}"></div></td>
						</tr>
					</table></td>
				<td width="10%" valign="top" align="right"><div id="stop" class="photo-player-button" <?
					?> onclick="if(player){player.stop();<?
					if ($arParams["BACK_URL"] == ''):
						?>jsUtils.Redirect([], window.SlideSlider.oSource.Data[window.SlideSlider.active]['url']);<?
					endif;
					?>} <?
					if ($arParams["BACK_URL"] != ''):
						?>jsUtils.Redirect([], '<?= CUtil::JSEscape(htmlspecialcharsbx($arParams["BACK_URL"]))?>');<?
					endif;
					?>"></div></td>
			</tr>
		</table>
	</div>
</div>

<script>
window.params = {'x' : 0, 'y' : 0};
function GetWindowSize()
{
	var innerWidth, innerHeight;

	if (self.innerHeight) // all except Explorer
	{
		innerWidth = self.innerWidth;
		innerHeight = self.innerHeight;
	}
	else if (document.documentElement && document.documentElement.clientHeight) // Explorer 6 Strict Mode
	{
		innerWidth = document.documentElement.clientWidth;
		innerHeight = document.documentElement.clientHeight;
	}
	else if (document.body) // other Explorers
	{
		innerWidth = document.body.clientWidth;
		innerHeight = document.body.clientHeight;
	}

	var scrollLeft, scrollTop;
	if (self.pageYOffset) // all except Explorer
	{
		scrollLeft = self.pageXOffset;
		scrollTop = self.pageYOffset;
	}
	else if (document.documentElement && document.documentElement.scrollTop) // Explorer 6 Strict
	{
		scrollLeft = document.documentElement.scrollLeft;
		scrollTop = document.documentElement.scrollTop;
	}
	else if (document.body) // all other Explorers
	{
		scrollLeft = document.body.scrollLeft;
		scrollTop = document.body.scrollTop;
	}

	var scrollWidth, scrollHeight;

	if ( (document.compatMode && document.compatMode == "CSS1Compat"))
	{
		scrollWidth = document.documentElement.scrollWidth;
		scrollHeight = document.documentElement.scrollHeight;
	}
	else
	{
		if (document.body.scrollHeight > document.body.offsetHeight)
			scrollHeight = document.body.scrollHeight;
		else
			scrollHeight = document.body.offsetHeight;

		if (document.body.scrollWidth > document.body.offsetWidth ||
			(document.compatMode && document.compatMode == "BackCompat") ||
			(document.documentElement && !document.documentElement.clientWidth)
		)
			scrollWidth = document.body.scrollWidth;
		else
			scrollWidth = document.body.offsetWidth;
	}

	return  {"innerWidth" : innerWidth, "innerHeight" : innerHeight, "scrollLeft" : scrollLeft, "scrollTop" : scrollTop, "scrollWidth" : scrollWidth, "scrollHeight" : scrollHeight};
}

function Show()
{
	div = BX('navigator_container');
	div.style.left = parseInt(document.body.scrollLeft + document.body.clientWidth/2 - 600/2) + "px";
	div.style.display = "block";
	var windowSize = GetWindowSize();

	if (params['x'] != windowSize.innerWidth || params['y'] != windowSize.innerHeight)
	{
		params['x'] = windowSize.innerWidth; params['y'] = windowSize.innerHeight;
		if (window.SlideSlider && window.SlideSlider != null)
		{
			window.SlideSlider.item_params = {'width' : (windowSize.innerWidth - 40), 'height' : (windowSize.innerHeight - 20)};
			window.SlideSlider.ShowSlider();
		}
	}
}

function to_init(e)
{
	var is_loaded = false;
	try {
		is_loaded = (bPhotoPlayerLoad == true);
	}
	catch(e){}

	if (!window.jsUtils)
		BX.loadScript('/bitrix/js/main/utils.js?v=<?=@filemtime($_SERVER['DOCUMENT_ROOT'].'/bitrix/js/main/utils.js')?>');

	if (is_loaded)
	{
		BX.bind(window, "resize", Show);
		Show();
		// Source
		BPCSourse.prototype.OnBeforeSendData = function()
		{
			BX('image-upload').style.display = 'block';
			for (var ii = 0, jj = ['prev', 'next']; ii < 2; ii++)
			{
				var res = BX(jj[ii]);
				if (res)
				{
					if (!res.__onclick) { res.__onclick = res.onclick; }
					res.onclick = function() { return false; }
				}
			}
		}
		BPCSourse.prototype.OnAfterSendData = function()
		{
			BX('image-upload').style.display = 'none';
			for (var ii = 0, jj = ['prev', 'next']; ii < 2; ii++)
			{
				var res = BX(jj[ii]);
				if (res)
				{
					res.onclick = res.__onclick;
				}
			}
		}

		// Slider
		BPCSlider1 = BPCSlider;
		BPCSlider1.prototype.ShowItem = function(item_id, number)
		{
			var res = BX('bx_slider_content_item_' + number);
			try
			{
				var oChildNodes = res.childNodes;
				if (oChildNodes && oChildNodes.length > 0)
				{
					for (var jj = 0; jj < oChildNodes.length; jj++)
						res.removeChild(oChildNodes[jj]);
				}
			}
			catch(e) {}
			res.appendChild(this.CreateItem(item_id));
			BX('counter').innerHTML = item_id;
			BX('title').innerHTML = this.oSource.Data[item_id]['title'];
			return true;
		}
		BPCSlider1.prototype.OnBeforeItemShow = function(item_id, params)
		{
			params = (typeof params != "object" ? {} : params);
			return (params['slideshow'] != true);
		}
		// Player
		BPCPlayer1 = BPCPlayer;
		BPCPlayer1.prototype.OnStopPlay = function()
		{
			if (BX('pause'))
				BX('pause').id = 'play';
		}
		BPCPlayer1.prototype.OnStartPlay = function()
		{
			if (BX('play'))
				BX('play').id = 'pause';
		}

		SlideSlider = new BPCSlider1(
			<?=CUtil::PhpToJSObject($arResult['ELEMENT_FOR_JS'])?>, // array of elements
			<?=intVal($arParams["ELEMENT_ID"])?>, // active element
			<?=intVal($count_elements)?>, // count elements
			<?=$number_element?> // number element in set
		);
		var windowSize = GetWindowSize();
		SlideSlider.item_params = {'width' : (windowSize.innerWidth - 40), 'height' : (windowSize.innerHeight - 20)};
		SlideSlider.ShowSlider();
		player = new BPCPlayer1(SlideSlider);
		if (player)
		{
			player.params = {
				period : 2,
				status : 'paused'
			};
			<?if ($play):?>
			window.player.play();
			<?endif;?>
		}
		else
		{
			var str = '<?=CUtil::JSEscape(GetMessage("P_ERROR_WHEN_PAGE_LOAD"))?>';
			BX("bx_slider_content_item_1").innerHTML = '<div class="error">' + str.replace("#HREF#", window.location.href) + '</div>';
		}
	}
	else
	{
		setTimeout(to_init, 100);
	}
}
if (window.attachEvent)
	window.attachEvent("onload", to_init);
else if (window.addEventListener)
	window.addEventListener("load", to_init, false);
else
	setTimeout(to_init, 100);
</script>
</body></html>
<?
die();
?>