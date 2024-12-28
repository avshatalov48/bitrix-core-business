<?
/** @var array $arResult */
/** @var array $arParams */
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

$className = "video-js ui-video-player ui-icon-set__scope {$arResult['SKIN_NAME']}";
?>
<div class="ui-player-playlist">
	<div class="ui-player-playlist__video">
		<video id="<?=$arResult["ID"]?>" class="<?=$className?>" width="<?=$arParams["WIDTH"]?>" height="<?=$arParams["HEIGHT"]?>"<?
		if(($arParams["MUTE"] ?? null) === "Y")
		{
			echo " muted";
		}
		?>>
		<? if ($arParams["USE_PLAYLIST"] !== 'Y' && !$arResult['YOUTUBE'] && !$arResult['LAZYLOAD']):?>
			<source src="<?=$arResult['PATH']?>" type="<?=$arResult['FILE_TYPE']?>">
		<? endif ?>
		</video>
	</div>

	<div class="ui-player-playlist__list">
		<div class="vjs-playlist"></div>
	</div>
</div>

<script>
	(function() {
		const params = <?=\Bitrix\Main\Web\Json::encode($arResult["VIDEOJS_PARAMS"])?>;
		const player = new BX.Fileman.Player('<?=$arResult['ID']?>', params);
		if(!player.lazyload)
		{
			player.init();
		}

		const sources = params?.sources || [];
		const playlist = sources.map(source => {
			return {
				name: source.title || '-',
				sources: [{ src: source.src, type: source.type }],
				thumbnail: [{
					src: source.thumbnail,
					height: 100,
				}],
			};
		});

		player.vjsPlayer.playlist(playlist);
		player.vjsPlayer.playlistUi();
	})();
</script>
