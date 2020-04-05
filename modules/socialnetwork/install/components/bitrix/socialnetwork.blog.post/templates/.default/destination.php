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

UI\Extension::load("ui.selector");

?><script>
	BX.message({
		BX_FPD_LINK_1:'<?=GetMessageJS("MPF_DESTINATION_1")?>',
		BX_FPD_LINK_2:'<?=GetMessageJS("MPF_DESTINATION_2")?>'
	});

	var socBPDest = {
		shareUrl : '/bitrix/urlrewrite.php?SEF_APPLICATION_CUR_PAGE_URL=<?=str_replace("%23", "#", urlencode($arParams["PATH_TO_POST"]))?>'
	};

</script><?

$selectorId = randString(6);

?><div class="feed-add-post-destination-block feed-add-post-destination-block-post" id="destination-sharing" style="display:none;">
	<form action="" name="blogShare" id="blogShare" method="POST" onsubmit="return false;" bx-selector-id="<?=htmlspecialcharsbx($selectorId)?>">
	<div class="feed-add-post-destination-title"><?=GetMessage("MPF_DESTINATION")?></div>
	<?
	$APPLICATION->IncludeComponent(
		"bitrix:main.user.selector",
		"",
		[
			"ID" => $selectorId,
			"LIST" => array(),
			"INPUT_NAME" => 'DEST_CODES[]',
			"USE_SYMBOLIC_ID" => "Y",
			"BUTTON_SELECT_CAPTION" => Loc::getMessage('MPF_DESTINATION_1'),
			"BUTTON_SELECT_CAPTION_MORE" => Loc::getMessage('MPF_DESTINATION_2'),
			"API_VERSION" => 3,
			"SELECTOR_OPTIONS" => array(
				'lazyLoad' => 'Y',
				'context' => 'BLOG_POST',
				'contextCode' => '',
				'enableSonetgroups' => 'Y',
				'departmentSelectDisable' => 'N',
				'showVacations' => 'Y',
				'useClientDatabase' => (!isset($arResult["bPublicPage"]) || !$arResult["bPublicPage"] ? 'Y' : 'N'),
				'allowAddUser' => ($arResult["bExtranetUser"] ? 'N' : 'Y'),
				'allowSearchEmailUsers' => ($arResult["ALLOW_EMAIL_INVITATION"] ? 'Y' : 'N'),
				'allowEmailInvitation' => (!$arResult["bExtranetUser"] && $arResult["ALLOW_EMAIL_INVITATION"] ? 'Y' : 'N'),
				'allowAddCrmContact' => (
					!$arResult["bExtranetUser"]
					&& $arResult["ALLOW_EMAIL_INVITATION"]
					&& \Bitrix\Main\Loader::includeModule('crm')
					&& \CCrmContact::checkCreatePermission()
						? 'Y'
						: 'N'
				),
				'enableAll' => 'Y'
			)
		]
	);
	?>
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