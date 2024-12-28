<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

$skinClass = (
	empty($arResult['SKIN_NAME']) || $arResult['SKIN_NAME'] === 'vjs-default-skin'
		? 'vjs-audio-wave-skin'
		: $arResult['SKIN_NAME']
);

$className = "video-js ui-video-player vjs-audio-only-mode ui-icon-set__scope {$skinClass}";

?>
<audio id="<?=$arResult["ID"]?>" class="<?=$className?>" width="<?=$arParams["WIDTH"]?>" height="<?=$arParams["HEIGHT"]?>"<?
if ($arParams["MUTE"] === "Y")
	echo " muted";
?>>

<?if($arParams["USE_PLAYLIST"] != 'Y')
{?>
	<source src="<?=$arResult['PATH']?>" type="<?=$arResult['FILE_TYPE']?>">
<?}?>
</audio>

<script>
(function() {
	const params = <?=\Bitrix\Main\Web\Json::encode($arResult["VIDEOJS_PARAMS"])?>;
	params.isAudio = true;
	params.skin = 'vjs-audio-wave-skin';
	params.onInit = (player) => {
		player.vjsPlayer.volume(<?=floatval($arResult["VOLUME"])?>);
	};

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
