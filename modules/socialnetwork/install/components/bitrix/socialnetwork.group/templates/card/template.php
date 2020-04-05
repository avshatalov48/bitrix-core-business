<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI;

UI\Extension::load("socialnetwork.common");

if(strlen($arResult["FatalError"])>0)
{
	?><span class='errortext'><?=$arResult["FatalError"]?></span><br /><br /><?
}
else
{
	if(strlen($arResult["ErrorMessage"])>0)
	{
		?><span class='errortext'><?=$arResult["ErrorMessage"]?></span><br /><br /><?
	}

	?><script>
		BX.ready(function() {
			SonetGroupCardSlider.getInstance().init({
				groupId: <?=intval($arParams["GROUP_ID"])?>,
				isProject: <?=($arResult['Group']['PROJECT'] == 'Y' ? 'true' : 'false')?>,
				isOpened: <?=($arResult['Group']['OPENED'] == 'Y' ? 'true' : 'false')?>,
				currentUserId: <?=($USER->isAuthorized() ? $USER->getid() : 0)?>,
				userRole: '<?=CUtil::JSUrlEscape($arResult["CurrentUserPerms"]["UserRole"])?>',
				userIsMember: <?=($arResult["CurrentUserPerms"]["UserIsMember"] ? 'true' : 'false')?>,
				userIsAutoMember: <?=(isset($arResult["CurrentUserPerms"]["UserIsAutoMember"]) && $arResult["CurrentUserPerms"]["UserIsAutoMember"] ? 'true' : 'false')?>,
				initiatedByType: '<?=CUtil::JSUrlEscape($arResult["CurrentUserPerms"]["InitiatedByType"])?>',
				favoritesValue: <?=($arResult["FAVORITES"] ? 'true' : 'false')?>,
				canInitiate: <?=($arResult["CurrentUserPerms"]["UserCanInitiate"] && !$arResult["HideArchiveLinks"] ? 'true' : 'false')?>,
				canModify: <?=($arResult["CurrentUserPerms"]["UserCanModifyGroup"] ? 'true' : 'false')?>,
				canModerate: <?=($arResult["CurrentUserPerms"]["UserCanModerateGroup"] ? 'true' : 'false')?>,
				hideArchiveLinks: <?=($arResult["HideArchiveLinks"] ? 'true' : 'false')?>,
				containerNodeId: 'socialnetwork-group-card-box',
				subscribeButtonNodeId: 'group_card_subscribe_button',
				menuButtonNodeId: 'group_card_menu_button',
				styles: {
					tags: {
						box: 'socialnetwork-group-tag-box',
						item: 'socialnetwork-group-tag'
					},
					users: {
						box: 'socialnetwork-group-user-box',
						item: 'socialnetwork-group-user'
					},
					fav: {
						switch: 'socialnetwork-group-fav-switch',
						activeSwitch: 'socialnetwork-group-fav-switch-active'
					}
				},
				urls: {
					groupsList: '<?=CUtil::JSUrlEscape($arParams["PATH_TO_GROUPS_LIST"])?>'
				},
				editFeaturesAllowed: <?=(\Bitrix\Socialnetwork\Item\Workgroup::getEditFeaturesAvailability() ? 'true' : 'false')?>
			})
		});

		BX.message({
			SGCSPathToGroupTag: '<?=CUtil::JSUrlEscape($arParams["PATH_TO_GROUP_TAG"])?>',
			SGCSPathToUserProfile: '<?=CUtil::JSUrlEscape($arParams["PATH_TO_USER"])?>',
			SGCSWaitTitle: '<?=GetMessageJS("SONET_C6_CARD_WAIT")?>'
		});
	</script><?

	$this->SetViewTarget("sonet-slider-pagetitle", 1000);
	$bodyClass = $APPLICATION->GetPageProperty("BodyClass");
	$APPLICATION->SetPageProperty("BodyClass", ($bodyClass ? $bodyClass." " : "")."pagetitle-menu-visible");
	include("title_buttons.php");
	$this->EndViewTarget();


	?><div class="socialnetwork-group-content" id="socialnetwork-group-card-box">
		<div class="socialnetwork-group-box">
			<h2 class="socialnetwork-group-title"><?=$arResult['Group']['NAME']?></h2>
		</div><?

		if ($arResult['Group']['DESCRIPTION'] !== '')
		{
			?><div class="socialnetwork-group-box">
				<div class="socialnetwork-group-desc"><?=$arResult['Group']['DESCRIPTION']?></div>
			</div><?
		}

		?><div class="socialnetwork-group-box">
			<div class="socialnetwork-group-left"><?=Loc::getMessage('SONET_C6_CREATED')?></div>
			<div class="socialnetwork-group-right"><?=FormatDateFromDB($arResult["Group"]["DATE_CREATE"], $arParams["DATE_TIME_FORMAT"], true)?></div>
		</div><?

		if ($arResult['Group']['PROJECT'] == 'Y')
		{
			?><div class="socialnetwork-group-box">
				<div class="socialnetwork-group-left"><?=Loc::getMessage('SONET_C6_PROJECT_DATE_START')?></div>
				<div class="socialnetwork-group-right"><?=FormatDateFromDB($arResult["Group"]["PROJECT_DATE_START"], $arParams["DATE_FORMAT"], true)?></div>
			</div>
			<div class="socialnetwork-group-box">
				<div class="socialnetwork-group-left"><?=Loc::getMessage('SONET_C6_PROJECT_DATE_FINISH')?></div>
				<div class="socialnetwork-group-right"><?=FormatDateFromDB($arResult["Group"]["PROJECT_DATE_FINISH"], $arParams["DATE_FORMAT"], true)?></div>
			</div><?
		}

		?><div class="socialnetwork-group-box">
			<div class="socialnetwork-group-left"><?=Loc::getMessage($arResult['Group']['PROJECT'] == 'Y' ? 'SONET_C6_CARD_OWNER_PROJECT' : 'SONET_C6_CARD_OWNER')?></div>
			<div class="socialnetwork-group-right">
				<div class="socialnetwork-group-user-box"><?
					$owner = $arResult["Owner"];

					$backgroundStyle = (
						!empty($owner["USER_PERSONAL_PHOTO_FILE"])
						&& !empty($owner["USER_PERSONAL_PHOTO_FILE"]["SRC"])
						? "background-image: url('".htmlspecialcharsbx($owner["USER_PERSONAL_PHOTO_FILE"]["SRC"])."'); background-size: cover;"
						: ""
					);

					?><div bx-user-id="<?=intval($owner['USER_ID'])?>" class="socialnetwork-group-user" title="<?=$owner["NAME_FORMATTED"]?>" style="<?=$backgroundStyle?>"></div>
				</div>
			</div>
		</div>
		<div class="socialnetwork-group-box">
			<div class="socialnetwork-group-left"><?=Loc::getMessage($arResult['Group']['PROJECT'] == 'Y' ? 'SONET_C6_CARD_MOD_PROJECT' : 'SONET_C6_CARD_MOD')?> (<?=intval($arResult["Group"]["NUMBER_OF_MODERATORS"])?>)</div>
			<div class="socialnetwork-group-right">
				<div class="socialnetwork-group-user-box"><?
					$counter = 0;
					foreach($arResult["Moderators"]["List"] as $moderator)
					{
						if ($counter >= $arParams['USER_LIMIT'])
						{
							break;
						}

						$backgroundStyle = (
							!empty($moderator["USER_PERSONAL_PHOTO_FILE"])
							&& !empty($moderator["USER_PERSONAL_PHOTO_FILE"]["SRC"])
								? "background-image: url('".htmlspecialcharsbx($moderator["USER_PERSONAL_PHOTO_FILE"]["SRC"])."'); background-size: cover;"
								: ""
						);

						?><div bx-user-id="<?=intval($moderator['USER_ID'])?>" class="socialnetwork-group-user" title="<?=$moderator["NAME_FORMATTED"]?>" style="<?=$backgroundStyle?>"></div><?
						$counter++;
					}

					if ($counter >= $arParams['USER_LIMIT'])
					{
						?><div class="socialnetwork-group-user-more">+ <?=(count($arResult["Moderators"]["List"]) - $arParams['USER_LIMIT'])?></div><?
					}

				?></div>
			</div>
		</div>
		<div class="socialnetwork-group-box">
			<div class="socialnetwork-group-left"><?=Loc::getMessage('SONET_C6_CARD_MEMBERS')?> (<?=intval($arResult["Group"]["NUMBER_OF_MEMBERS"])?>)</div>
			<div class="socialnetwork-group-right">
				<div class="socialnetwork-group-user-box"><?
					$counter = 0;
					foreach($arResult["Members"]["List"] as $member)
					{
						if ($counter >= $arParams['USER_LIMIT'])
						{
							break;
						}

						$backgroundStyle = (
							!empty($member["USER_PERSONAL_PHOTO_FILE"])
							&& !empty($member["USER_PERSONAL_PHOTO_FILE"]["SRC"])
								? "background-image: url('".htmlspecialcharsbx($member["USER_PERSONAL_PHOTO_FILE"]["SRC"])."'); background-size: cover;"
								: ""
						);

						?><div bx-user-id="<?=intval($member['USER_ID'])?>" class="socialnetwork-group-user" title="<?=$member["NAME_FORMATTED"]?>" style="<?=$backgroundStyle?>"></div><?
						$counter++;
					}

					if ($counter >= $arParams['USER_LIMIT'])
					{
						?><div class="socialnetwork-group-user-more">+ <?=(count($arResult["Members"]["List"]) - $arParams['USER_LIMIT'])?></div><?
					}

				?></div>
			</div>
		</div><?

		if (
			is_array($arResult['Subjects'])
			&& count($arResult['Subjects']) > 1
		)
		{
			?><div class="socialnetwork-group-box">
				<div class="socialnetwork-group-left"><?=Loc::getMessage('SONET_C6_CARD_SUBJECT')?></div>
				<div class="socialnetwork-group-right"><?=$arResult['Group']['SUBJECT_NAME']?></div>
			</div><?
		}

		if (
			is_array($arResult["Group"]["KEYWORDS_LIST"])
			&& !empty($arResult["Group"]["KEYWORDS_LIST"])
		)
		{
			?><div class="socialnetwork-group-box">
				<div class="socialnetwork-group-left"><?=Loc::getMessage('SONET_C6_TAGS')?></div>
				<div class="socialnetwork-group-right">
					<div class="socialnetwork-group-tag-box"><?
						foreach($arResult["Group"]["KEYWORDS_LIST"] as $keyword)
						{
							?><a bx-tag-value="<?=$keyword?>" href="<?=CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP_TAG"], array('tag' => $keyword));?>" class="socialnetwork-group-tag"><?=$keyword?></a><?
						}
					?></div>
				</div>
			</div><?
		}

		?><div
			class="socialnetwork-group-fav-switch<?=(!empty($arResult['FAVORITES']) ? " socialnetwork-group-fav-switch-active" : "")?>"
			title="<?=Loc::getMessage("SONET_C6_CARD_FAVORITES_".(!empty($arResult['FAVORITES']) ? "Y" : "N"))?>"></div><?
	?></div><?
}
?>