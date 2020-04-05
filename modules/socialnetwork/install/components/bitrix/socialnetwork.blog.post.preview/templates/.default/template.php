<?
use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

?>

<div class="blog-post-preview">
	<table class="blog-post-preview-info">
		<tr>
			<td>
				<div class="blog-post-preview-header-icon user-default-avatar">
					<img src="<?=(isset($arResult["arUser"]["PERSONAL_PHOTO_resized"]["src"]) && strlen($arResult["arUser"]["PERSONAL_PHOTO_resized"]["src"]) > 0 ? $arResult["arUser"]["PERSONAL_PHOTO_resized"]["src"] : "/bitrix/images/1.gif")?>" width="<?=$arParams["AVATAR_SIZE"]?>" height="<?=$arParams["AVATAR_SIZE"]?>">
				</div>
			</td>
			<td>
				<span class="blog-post-preview-header-title">
					<script>
						BX.tooltip(<?=htmlspecialcharsbx($arResult["POST"]["AUTHOR"])?>, "a_<?=htmlspecialcharsbx($arResult["POST"]['AUTHOR_UNIQID'])?>", "");
					</script>
					<a id="a_<?=htmlspecialcharsbx($arResult["POST"]['AUTHOR_UNIQID'])?>" href="<?=htmlspecialcharsbx($arResult["POST"]["AUTHOR_PROFILE"])?>">
						<?=htmlspecialcharsbx($arResult["POST"]['AUTHOR_FORMATTED_NAME'])?>
					</a>
					<span class="urlpreview__time-wrap">
						<a href="<?=htmlspecialcharsbx($arParams['URL'])?>"><span class="urlpreview__time"><?=htmlspecialcharsbx($arResult["POST"]["DATE_FORMATTED"])?></span></a>
					</span>
				</span>
				<?if($arResult['POST']['TITLE']):?>
					<p><strong><?=htmlspecialcharsbx($arResult['POST']['TITLE'])?></strong></p>
				<?endif?>
				<p><?=htmlspecialcharsbx($arResult['POST']['PREVIEW_TEXT'])?></p>
			</td>
		</tr>
	</table>
</div>

