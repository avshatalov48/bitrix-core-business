<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */
/** @var boolean $is_unread */

use \Bitrix\Main\Localization\Loc;

if (!empty($arResult['Post']['IMPORTANT']))
{
	?><div class="feed-imp-post-footer"><?

		?><span class="feed-imp-btn-main-wrap"><?
			if ($arResult["Post"]["IMPORTANT"]["IS_READ"] === "Y")
			{
				?><span class="feed-imp-btn-wrap"><?
					?><span class="have-read-text-block"><?
						?><i></i><?
						?><span class="feed-imp-post-footer-message"><?=Loc::getMessage('BLOG_ALREADY_READ')?></span><?
						?><span class="feed-imp-post-footer-comma">,</span><?
					?></span><?
				?></span><?
			}
			else
			{
				?><span class="feed-imp-btn-wrap"><?
					?><button
					 class="ui-btn ui-btn-lg ui-btn-success ui-btn-round"
					 id="blog-post-readers-btn-<?=$arResult["Post"]["ID"]?>"
					 bx-blog-post-id="<?=$arResult["Post"]["ID"]?>"
					 bx-url="<?=htmlspecialcharsbx($arResult["arUser"]["urlToPostImportant"])?>"
					 onclick="new SBPImpPost(this); return false;"
					><?
						?><?=Loc::getMessage(trim('BLOG_READ_'.$arResult['Post']['IMPORTANT']['USER']['PERSONAL_GENDER']))?><?
					?></button><?
				?></span><?
			}
		?></span><?

		$stylesList = [];
		if ($arResult["Post"]["IMPORTANT"]["COUNT"] <= 0)
		{
			$stylesList[] = 'display:none;';
		}
		?><span id="blog-post-readers-count-<?=$arResult['Post']['ID']?>" class="feed-imp-post-footer-text" style="<?=implode(' ', $stylesList)?>"
			title="<?=Loc::getMessage("BLOG_USERS_ALREADY_READ")?> <?=$arResult["Post"]["IMPORTANT"]["COUNT"]?> <?=Loc::getMessage('BLOG_READERS')?>"
		><?
			?><?=Loc::getMessage("BLOG_USERS_ALREADY_READ")?>&nbsp;<a class="feed-imp-post-user-link" href="javascript:void(0);"><?
			?><span><?=$arResult["Post"]["IMPORTANT"]["COUNT"]?></span> <?=Loc::getMessage('BLOG_READERS')?></a><?
		?></span><?
	?></div><?

	?><script>
		BX.ready(function(){
			var sbpimp<?=$arResult['Post']['ID']?> =  new top.SBPImpPostCounter(
				BX('blog-post-readers-count-<?=$arResult['Post']['ID']?>'),
				<?=$arResult['Post']['ID']?>, {
					'pathToUser' : '<?=CUtil::JSEscape($arParams['~PATH_TO_USER'])?>',
					'nameTemplate' : '<?=CUtil::JSEscape($arParams['NAME_TEMPLATE'])?>'
				}
			);
			<?
			if ($arResult['Post']['IMPORTANT']['IS_READ'] !== 'Y')
			{
				?>BX.addCustomEvent(BX('blog-post-readers-btn-<?=$arResult['Post']['ID']?>'), 'onInit', BX.proxy(sbpimp<?=$arResult['Post']['ID']?>.click, sbpimp<?=$arResult['Post']['ID']?>));<?
			}
			?>
			BX.message({'BLOG_ALREADY_READ' : '<?=GetMessageJS('BLOG_ALREADY_READ')?>'});
		});
	</script><?
}
?>