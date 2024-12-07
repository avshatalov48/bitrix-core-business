<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

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

CJSCore::Init(array("ajax"));
$firstItemType = 'flv';
if (isset($arResult['SELECTED_ELEMENT']['VALUES']['TYPE']))
	$firstItemType = mb_strtolower($arResult['SELECTED_ELEMENT']['VALUES']['TYPE']);
?>
	<div id="bx_tv_block_<?=$arResult['PREFIX']?>" style="width: <?=$arParams['WIDTH']?>px;">
		<div id="tv_playerjsPublicTVCollector.tv[<?=$arResult['PREFIX']?>]" class="player_player" style="width: <?=$arParams['WIDTH']?>px; height: <?=$arParams['HEIGHT'] + $arResult['CORRECTION']['FLV']?>px;">
		<?php
		if ($arResult['FIRST_FLV_ITEM']):
		?>
		<div id="bitrix_tv_flv_cont_<?= $arResult["PREFIX"]?>" style="display: none;">
		<?php
		$APPLICATION->IncludeComponent(
			"bitrix:player",
			"",
			Array(
				"PLAYER_TYPE" => "flv",
				"USE_PLAYLIST" => "N",
				"PATH" => $firstItemType == 'flv' ? $arResult['SELECTED_ELEMENT']['FILE'] : $arResult['FIRST_FLV_ITEM'],
				"WIDTH" => $arParams['WIDTH'],
				"HEIGHT" => $arParams['HEIGHT'] + $arResult['CORRECTION']['FLV'],
				"PREVIEW" => $arResult['SELECTED_ELEMENT']['VALUES']['DETAIL_PICTURE'],
				"LOGO" => $arParams["LOGO"],
				"FULLSCREEN" => "Y",
				"SKIN_PATH" => "/bitrix/components/bitrix/player/mediaplayer/skins",
				"SKIN" => "",
				"CONTROLBAR" => "bottom",
				"WMODE" => "transparent",
				"WMODE_WMV" => "windowless",
				"HIDE_MENU" => "N",
				"SHOW_CONTROLS" => "Y",
				"SHOW_STOP" => "N",
				"SHOW_DIGITS" => "Y",
				"CONTROLS_BGCOLOR" => "FFFFFF",
				"CONTROLS_COLOR" => "000000",
				"CONTROLS_OVER_COLOR" => "000000",
				"SCREEN_COLOR" => "000000",
				"AUTOSTART" => "N",
				"REPEAT" => "N",
				"VOLUME" => "90",
				"DISPLAY_CLICK" => "play",
				"MUTE" => "N",
				"HIGH_QUALITY" => "Y",
				"ADVANCED_MODE_SETTINGS" => "Y",
				"BUFFER_LENGTH" => "10",
				"DOWNLOAD_LINK" => $arResult['SELECTED_ELEMENT']['FILE'],
				"DOWNLOAD_LINK_TARGET" => "_self",
				"ALLOW_SWF" => $arParams["ALLOW_SWF"],
				"ADDITIONAL_PARAMS" => array(
					'LOGO' => $arParams['LOGO'],
					'NUM' => $arResult['PREFIX'],
					'HEIGHT_CORRECT' => $arResult['CORRECTION'],
				),
				"PLAYER_ID" => "bitrix_tv_flv_".$arResult["PREFIX"]
			),
			$component,
			Array("HIDE_ICONS" => "Y")
		);?>
		</div>
		<?php
		endif;

		if ($arResult['FIRST_WMV_ITEM']):
		?>
		<div id="bitrix_tv_wmv_cont_<?= $arResult["PREFIX"]?>" style="display: none">
		<?php
		$APPLICATION->IncludeComponent(
			"bitrix:player",
			"",
			Array(
				"PLAYER_TYPE" => "wmv",
				"USE_PLAYLIST" => "N",
				"INIT_PLAYER"=>"N",
				"PATH" => $firstItemType == 'wmv' ? $arResult['SELECTED_ELEMENT']['FILE'] : $arResult['FIRST_WMV_ITEM'],
				"WIDTH" => $arParams['WIDTH'],
				"HEIGHT" => $arParams['HEIGHT'] + $arResult['CORRECTION']['FLV'],
				"PREVIEW" => $arResult['SELECTED_ELEMENT']['VALUES']['DETAIL_PICTURE'],
				"LOGO" => $arParams["LOGO"],
				"FULLSCREEN" => "Y",
				"CONTROLBAR" => "bottom",
				"WMODE" => "transparent",
				"WMODE_WMV" => "windowless",
				"HIDE_MENU" => "N",
				"SHOW_CONTROLS" => "Y",
				"SHOW_STOP" => "N",
				"SHOW_DIGITS" => "Y",
				"CONTROLS_BGCOLOR" => "FFFFFF",
				"CONTROLS_COLOR" => "000000",
				"CONTROLS_OVER_COLOR" => "000000",
				"SCREEN_COLOR" => "000000",
				"AUTOSTART" => "N",
				"REPEAT" => "N",
				"VOLUME" => "90",
				"DISPLAY_CLICK" => "play",
				"MUTE" => "N",
				"HIGH_QUALITY" => "Y",
				"ADVANCED_MODE_SETTINGS" => "Y",
				"BUFFER_LENGTH" => "10",
				"DOWNLOAD_LINK" => $arResult['SELECTED_ELEMENT']['FILE'],
				"DOWNLOAD_LINK_TARGET" => "_self",
				"ALLOW_SWF" => $arParams["ALLOW_SWF"],
				"ADDITIONAL_PARAMS" => array(
					'LOGO' => $arParams['LOGO'],
					'NUM' => $arResult['PREFIX'],
					'HEIGHT_CORRECT' => $arResult['CORRECTION'],
				),
				"PLAYER_ID" => "bitrix_tv_wmv_".$arResult["PREFIX"]
			),
			$component,
			Array("HIDE_ICONS" => "Y")
		);?>
		</div>
		<?php
		endif;
		?>
		</div>
	<?php
	if(!$arResult['NO_PLAY_LIST']):
		?>
		<div id="tv_list_<?=$arResult['PREFIX']?>" class="player_tree_list" style="width: <?=$arParams['WIDTH']-2?>px;"></div>
		<?php
	endif;
	?>
	</div>
	<?php
	//build tree and call player
	?>
<script>
	<?= $arResult['LIST']; ?>

	jsPublicTVCollector.tv[<?=$arResult['PREFIX']?>] = new jsPublicTV();
	jsPublicTVCollector.tv[<?=$arResult['PREFIX']?>].LanguagePhrases = {
		'duration':'<?=GetMessageJS("BITRIXTV_TEMPLATE_DURATION")?>',
		'title':'<?=GetMessageJS("BITRIXTV_TEMPLATE_TITLE")?>',
		'description':'<?=GetMessageJS("BITRIXTV_TEMPLATE_DESCRIPTION")?>',
		'file':'<?=GetMessageJS("BITRIXTV_TEMPLATE_FILE")?>',
		'download':'<?=GetMessageJS("BITRIXTV_TEMPLATE_DOWNLOAD")?>',
		'size_mb':'<?=GetMessageJS("BITRIXTV_TEMPLATE_BXTV_SIZE_MB")?>',
		'play':'<?=GetMessageJS("BITRIXTV_TEMPLATE_BXTV_PLAY")?>',
		'edit':'<?=GetMessageJS("BITRIXTV_TEMPLATE_BXTV_EDIT")?>'
	};

	//set uniq prefix
	jsPublicTVCollector.tv[<?=$arResult['PREFIX']?>].Prefix = 'p<?=$arResult['PREFIX']?>';

	//Init additonal TV properties
	jsPublicTVCollector.add[<?=$arResult['PREFIX']?>] = {};

	//set orderplay \section\
	jsPublicTVCollector.add[<?=$arResult['PREFIX']?>].PlayOrder = function(type)
	{
		jsPublicTVCollector.tv[<?=$arResult['PREFIX']?>].PlayOrder = type;
	}

	/*select*/
	//set selected item
	jsPublicTVCollector.add[<?=$arResult['PREFIX']?>].SelectListItem = function(old_i, old_j)
	{
		if(jsPublicTVCollector.tv[<?=$arResult['PREFIX']?>].CurrentItem)
		{
			var i = jsPublicTVCollector.tv[<?=$arResult['PREFIX']?>].CurrentItem.Section;
			var j = jsPublicTVCollector.tv[<?=$arResult['PREFIX']?>].CurrentItem.Item;
			var prefix = jsPublicTVCollector.tv[<?=$arResult['PREFIX']?>].Prefix ;
			var item = document.getElementById(prefix + 'bx-tv-s' + i + 'i' + j);
			if(item)
			{
				item = item.getElementsByTagName('DIV');
				if(item.length>0)
					item[0].className = jsPublicTVCollector.add[<?=$arResult['PREFIX']?>].ListItemColors.select;

				//scroll to selected
				TreeBlockID = document.getElementById(jsPublicTVCollector.tv[<?=$arResult['PREFIX']?>].TreeBlockID.id);
				TreeBlockID.scrollTop = BX.browser.IsIE()
					?item[0].offsetTop-13
					:item[0].offsetTop - TreeBlockID.offsetTop - 4;

				//unselect
				if(typeof(old_i) != "undefined" && typeof(old_j) != "undefined" && old_j!=='' && old_i!=='')
				{
					var item = document.getElementById(prefix + 'bx-tv-s' + old_i + 'i' + old_j);
					if(item)
					{
						item = item.getElementsByTagName('DIV');
						if(item.length>0)
							item[0].className = jsPublicTVCollector.add[<?=$arResult['PREFIX']?>].ListItemColors.normal;
					}
				}
			}
		}
	}

	//set hover item
	jsPublicTVCollector.add[<?=$arResult['PREFIX']?>].HoverListItem = function(ob)
	{
		if(ob.className != jsPublicTVCollector.add[<?=$arResult['PREFIX']?>].ListItemColors.select)
		{
			if(ob.className != jsPublicTVCollector.add[<?=$arResult['PREFIX']?>].ListItemColors.hover)
				ob.className = jsPublicTVCollector.add[<?=$arResult['PREFIX']?>].ListItemColors.hover;
			else
				ob.className = jsPublicTVCollector.add[<?=$arResult['PREFIX']?>].ListItemColors.normal;
		}
	}

	//set default hover\select colors
	jsPublicTVCollector.add[<?=$arResult['PREFIX']?>].ListItemColors = {select: 'selected-tv-item', hover:'hover-tv-item', normal:'normal-tv-item'}
	/*end-select*/

	//Template of the item block
	jsPublicTVCollector.tv[<?=$arResult['PREFIX']?>].AddPlayerListener(
		'BUILD_ITEM',
		function(txt, i, j)
		{
			txt =
			'<div onmouseover="jsPublicTVCollector.add[<?=$arResult['PREFIX']?>].HoverListItem(this)" onmouseout="jsPublicTVCollector.add[<?=$arResult['PREFIX']?>].HoverListItem(this)" style="padding:10px 0px">'
				+'<div class="bitrix-tv-small-image" onclick="jsPublicTVCollector.tv[<?=$arResult['PREFIX']?>].PlayFile('+i+','+j+',true,true)">'
					+'<img width="' + jsPublicTVCollector.tv[<?=$arResult['PREFIX']?>].ShowPreviewImageSize[0] + 'px" height="' + jsPublicTVCollector.tv[<?=$arResult['PREFIX']?>].ShowPreviewImageSize[1] + 'px" src="' + jsPublicTVCollector.tv[<?=$arResult['PREFIX']?>]['Sections'][i]['Items'][j]['SmallImage'] + '">' //image
				+'</div>'
				+'<div class="bitrix-tv-tree-item-description">'
					+'<a onclick="jsPublicTVCollector.tv[<?=$arResult['PREFIX']?>].PlayFile('+i+','+j+',true,true)" class="tv-desc-name">' + jsPublicTVCollector.tv[<?=$arResult['PREFIX']?>]['Sections'][i]['Items'][j]['Name'] + '</a>' //name
					+jsPublicTVCollector.tv[<?=$arResult['PREFIX']?>]['Sections'][i]['Items'][j]['Description'] //description
					+'<div class="delimiter-tv-param-line-bottom">'
						+'<div class="delimiter-tv-param-line">'
							+'<a href="' + jsPublicTVCollector.tv[<?=$arResult['PREFIX']?>]['Sections'][i]['Items'][j]['File'] + '">'
								+ jsPublicTVCollector.tv[<?=$arResult['PREFIX']?>].LanguagePhrases.download + '</a>'
									+ (jsPublicTVCollector.tv[<?=$arResult['PREFIX']?>]['Sections'][i]['Items'][j]['Size'].length >0
										?' <span class="tv-gray">('+jsPublicTVCollector.tv[<?=$arResult['PREFIX']?>]['Sections'][i]['Items'][j]['Size']+jsPublicTVCollector.tv[<?=$arResult['PREFIX']?>].LanguagePhrases.size_mb+')</span>'
										:'')
						+'</div>'
						+(jsPublicTVCollector.tv[<?=$arResult['PREFIX']?>]['Sections'][i]['Items'][j]['Duration'].length >0
							?'<div class="delimiter-tv-param"></div>'
								+'<div class="delimiter-tv-param-line">'
									+'<span class="tv-gray">'+jsPublicTVCollector.tv[<?=$arResult['PREFIX']?>]['Sections'][i]['Items'][j]['Duration']
									+'</span>'
								+'</div>'
							:'') //duration
						+ '<div class="delimiter-tv-param"></div>'
						+'<div class="delimiter-tv-param-line">'
							+ '<a href="javascript:void(0)" onclick="jsPublicTVCollector.tv[<?=$arResult['PREFIX']?>].PlayFile('+i+','+j+',true,true)">' + jsPublicTVCollector.tv[<?=$arResult['PREFIX']?>].LanguagePhrases.play + '</span>'
						+'</div>'
						+(jsPublicTVCollector.tv[<?=$arResult['PREFIX']?>]['Sections'][i]['Items'][j]['Action'].length >0
							? '<div class="delimiter-tv-param"></div>'
							+ '<div class="delimiter-tv-param-line">'
								+ '<a href="javascript:void(0)" onclick="' + jsPublicTVCollector.tv[<?=$arResult['PREFIX']?>]['Sections'][i]['Items'][j]['Action'] + '">' + jsPublicTVCollector.tv[<?=$arResult['PREFIX']?>].LanguagePhrases.edit + '</span>'
							+'</div>'
							:'') //edit action
						+'<div style="clear:both;"></div>'
					+'</div>'
				+'</div>'
				+'<div style="clear:both;"></div>'
			+'</div>'
			+'<div class="delimiter-gray-mono-grad2-item">';

			return txt;
		}
	);

	jsPublicTVCollector.tv[<?=$arResult['PREFIX']?>].AddPlayerListener(
		'BEFORE_PLAY_FILE',
		function(i, j, old_i, old_j)
		{
			jsPublicTVCollector.add[<?=$arResult['PREFIX']?>].SelectListItem(old_i, old_j);
		}
	);

	//init&run
	if(jsPublicTVCollector.tv[<?=$arResult['PREFIX']?>])
	{
		jsPublicTVCollector.tv[<?=$arResult['PREFIX']?>].Init(
			jsPublicTVCollector.list[<?=$arResult['PREFIX']?>],
			'tv_list_<?=$arResult['PREFIX']?>',
			'tv_description_<?=$arResult['PREFIX']?>',
			{
				block_id:
				{
					wmv: 'bitrix_tv_wmv_cont_<?=$arResult['PREFIX']?>',
					flv: 'bitrix_tv_flv_cont_<?=$arResult['PREFIX']?>'
				},
				obj_id:
				{
					wmv: 'bitrix_tv_wmv_<?=$arResult['PREFIX']?>',
					flv: 'bitrix_tv_flv_<?=$arResult['PREFIX']?>_div'
				},
				logo: '<?=$templateFolder.'/images/logo.png'?>',
				height: '<?=$arParams['HEIGHT']+$arResult['CORRECTION']['FLV']?>',
				width: '<?=$arParams['WIDTH']?>'
			}
		);
		jsPublicTVCollector.tv[<?=$arResult['PREFIX']?>].BuildTree(false, 0);

		SetItem = jsPublicTVCollector.tv[<?=$arResult['PREFIX']?>].SeekByRealParams(false, <?=intval($arResult['SELECTED_ELEMENT']['VALUES']['ID'])?>);
		if(false!==SetItem.section && false!==SetItem.element)
			jsPublicTVCollector.tv[<?=$arResult['PREFIX']?>].PlayFile(SetItem.section, SetItem.element, false, true);

		if(jsPublicTVCollector.tv[<?=$arResult['PREFIX']?>].PlayOrder != 'section')
			jsPublicTVCollector.add[<?=$arResult['PREFIX']?>].PlayOrder('section');

		//set selected item
		jsPublicTVCollector.add[<?=$arResult['PREFIX']?>].SelectListItem();
	}

	<?php
	if($arParams['STAT_EVENT'] || $arParams['SHOW_COUNTER_EVENT']):
		?>
		jsPublicTVCollector.tv[<?=$arResult['PREFIX']?>].GatherStatistics = true;
		<?php
	endif;
	?>
</script>
<br clear="all"/>