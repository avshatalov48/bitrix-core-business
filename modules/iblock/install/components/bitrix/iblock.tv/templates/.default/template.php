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

// generate playlist
$arPlaylist = array();
foreach ($arResult["SECTIONS"] as $arSection)
{
	foreach ($arSection["ELEMENTS"] as $arItem)
	{

		$arPlaylist[] = array(
			'title' => $arItem['NAME'],
			'src' => $arItem['FILE'],
			'thumbnail' => $arItem['DETAIL_PICTURE'],
		);
	}
}
?>
	<div id="bx_tv_block_<?=$arResult['PREFIX']?>" style="width: <?=$arParams['WIDTH']?>px;">
		<?$APPLICATION->IncludeComponent(
			"bitrix:player",
			"",
			Array(
				"PLAYER_TYPE" => "videojs",
				"USE_PLAYLIST" => "Y",
				"PATH" => '',
				"WIDTH" => $arParams['WIDTH'],
				"HEIGHT" => $arParams['HEIGHT'],
				"PREVIEW" => $arResult['SELECTED_ELEMENT']['VALUES']['DETAIL_PICTURE'],
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
				"PLAYER_ID" => "bitrix_tv_videojs_".$arResult["PREFIX"],
				"TRACKS" => $arPlaylist
			),
			$component,
			Array("HIDE_ICONS" => "Y")
		);?>
	</div>
<br clear="all"/>