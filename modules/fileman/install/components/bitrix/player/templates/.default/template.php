<?
/** @var array $arResult */
/** @var array $arParams */

use Bitrix\Main\Web\Json;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

$tagName = $arResult['AUDIO_FILE'] === true ? 'audio' : 'video';
$className = "video-js ui-video-player ui-icon-set__scope {$arResult['SKIN_NAME']}";
if ($tagName === 'audio')
{
	$className .= " vjs-audio-only-mode";
}
?>

<<?=$tagName?> id="<?=$arResult["ID"]?>" class="<?=$className?>" width="<?=$arParams["WIDTH"]?>" height="<?=$arParams["HEIGHT"]?>"<?
if(($arParams["MUTE"] ?? null) === "Y")
{
	echo " muted";
}
?>>
<? if ($arParams["USE_PLAYLIST"] !== 'Y' && !$arResult['YOUTUBE'] && !$arResult['LAZYLOAD']):?>
	<source src="<?=$arResult['PATH']?>" type="<?=$arResult['FILE_TYPE']?>">
<? endif ?>
</<?=$tagName?>>
<script>
(function() {
	const params = <?=Json::encode($arResult["VIDEOJS_PARAMS"])?>;
	params.isAudio = <?=Json::encode($arResult['AUDIO_FILE'])?>;

	const init = () => {
		const player = new BX.UI.VideoPlayer.Player('<?=$arResult['ID']?>', params);
		if(!player.lazyload)
		{
			player.init();
		}
	};

	if (BX.Reflection.getClass('BX.UI.VideoPlayer.Player') !== null)
	{
		init();
	}
	else
	{
		BX.Runtime.loadExtension('ui.video-player').then(() => {
			init();
		});
	}
})();
</script>
