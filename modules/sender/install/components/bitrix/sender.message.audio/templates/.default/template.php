<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;

Loc::loadMessages(__FILE__);

/** @var CMain $APPLICATION */
/** @var array $arParams */
/** @var array $arResult */

$id = $arParams['INPUT_ID'];
$controlId = $arParams['CONTROL_ID'];
$playerId = $id . "player";
?>
<div class="bx-sender-audio">
	<div class="bx-sender-audio-player">
		<?
		$crmInstalled = Loader::includeModule('crm');

		$APPLICATION->IncludeComponent(
			"bitrix:player",
			"audio",
			array(
				"PLAYER_ID" => $playerId,
				"PLAYER_TYPE" => "videojs",
				"ADVANCED_MODE_SETTINGS" => "Y",
				"ALLOW_SWF" => "N",
				"AUTOSTART" => "N",
				"HEIGHT" => "30",
				"MUTE" => "N",
				"PATH" => $arResult['AUDIO_FILE']['FILE_PATH'],
				"TYPE" => "audio/mp3",
				"PLAYLIST_SIZE" => "180",
				"PRELOAD" => "N",
				"PREVIEW" => "",
				"REPEAT" => "none",
				"SHOW_CONTROLS" => "Y",
				"SKIN" => $crmInstalled ? "timeline_player.css" : "",
				"SKIN_PATH" => $crmInstalled ? "/bitrix/js/crm/" : "",
				"USE_PLAYLIST" => "N",
				"VOLUME" => "100",
				"WIDTH" => "600",
				"COMPONENT_TEMPLATE" => "audio",
				"SIZE_TYPE" => "absolute",
				"START_TIME" => "0",
				"PLAYBACK_RATE" => "1"
			),
			$this->component,
			array("HIDE_ICONS" => true)
		);
		?>
	</div>
	<div class="bx-sender-audio-file">
		<?
		$APPLICATION->IncludeComponent('bitrix:main.file.input', '.default',
			array(
				'INPUT_CAPTION' => Loc::getMessage("SENDER_MSG_AUDIO_UPLOAD_OWN_AUDIO"),
				'INPUT_NAME' => $arParams['INPUT_ID'],
				'INPUT_VALUE' => $arResult['AUDIO_FILE']['CREATED_FROM_PRESET'] ? [] : [ $arResult['AUDIO_FILE']['VALUE'] ],
				'MODULE_ID' => 'sender',
				'FORCE_MD5' => true,
				'CONTROL_ID' => $controlId,
				'MULTIPLE' => 'N',
				'ALLOW_UPLOAD' => 'F',
				'ALLOW_UPLOAD_EXT' => 'mp3'
			),
			$this->component,
			array("HIDE_ICONS" => true)
		);
		?>
	</div>
</div>


<script>
	BX.ready(function () {
		BX.Sender.Audio.init(<?=Json::encode(array(
			'id' => $arParams['INPUT_NAME'],
			'inputId' => $arParams['INPUT_ID'],
			'controlId' => $controlId,
			'playerId' => $playerId,
			'value' => $arResult['AUDIO_FILE']['VALUE'],
			'useTemplateValue' => !!$arResult['AUDIO_FILE']['CREATED_FROM_PRESET']
		))?>);
	});
</script>