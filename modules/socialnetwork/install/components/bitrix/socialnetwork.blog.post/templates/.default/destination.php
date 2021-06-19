<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentPath */
/** @var string $componentName */
/** @var string $componentTemplate */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI;

UI\Extension::load([ 'ui.entity-selector' ]);

?><script>
	BX.message({
		BX_FPD_SHARE_LINK_1: '<?= CUtil::JSEscape(Loc::getMessage('MPF_DESTINATION_1')) ?>',
		BX_FPD_SHARE_LINK_2: '<?= CUtil::JSEscape(Loc::getMessage('MPF_DESTINATION_2')) ?>'
	});

	var socBPDest = {
		shareUrl : '/bitrix/urlrewrite.php?SEF_APPLICATION_CUR_PAGE_URL=<?=str_replace("%23", "#", urlencode($arParams["PATH_TO_POST"]))?>'
	};

</script><?

$selectorId = \Bitrix\Main\Security\Random::getString(6);

?><div class="feed-add-post-destination-block feed-add-post-destination-block-post" id="destination-sharing" style="display:none;">
	<form action="" name="blogShare" id="blogShare" method="POST" onsubmit="return false;" bx-selector-id="<?=htmlspecialcharsbx($selectorId)?>">
	<div class="feed-add-post-destination-title"><?=Loc::getMessage("MPF_DESTINATION")?></div>
	<input type="hidden" id="entity-selector-data-<?=htmlspecialcharsbx($selectorId)?>" name="DEST_DATA" value="[]"/>
	<div id="entity-selector-<?=htmlspecialcharsbx($selectorId)?>"></div>
	<input type="hidden" name="post_id" id="sharePostId" value="">
	<input type="hidden" name="user_id" id="shareUserId" value="">
	<input type="hidden" name="act" value="share">
	<?=bitrix_sessid_post()?>
	<div class="feed-add-post-buttons-post">
		<button id="sharePostSubmitButton" class="ui-btn ui-btn-lg ui-btn-primary" onclick="sharingPost()"><?=GetMessage("BLOG_SHARE_ADD")?></button>
		<button class="ui-btn ui-btn-lg ui-btn-link" onclick="closeSharing();"><?=GetMessage("BLOG_SHARE_CANCEL")?></button>
	</div>
	</form>
</div>