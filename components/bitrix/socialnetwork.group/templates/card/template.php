<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI;
use Bitrix\Socialnetwork\Helper;

UI\Extension::load([
	'socialnetwork.common',
	'ui.icons.b24',
	'ui.info-helper',
]);

if($arResult["FatalError"] <> '')
{
	?><span class='errortext'><?=$arResult["FatalError"]?></span><br /><br /><?php
}
else
{
	if($arResult["ErrorMessage"] <> '')
	{
		?><span class='errortext'><?=$arResult["ErrorMessage"]?></span><br /><br /><?php
	}

	?><script>
		BX.ready(function() {
			(new BX.Socialnetwork.WorkgroupCard()).init({
				groupId: <?= (int)$arParams['GROUP_ID'] ?>,
				groupType: '<?= CUtil::JSEscape($arResult['groupTypeCode']) ?>',
				isProject: <?= ($arResult['Group']['PROJECT'] === 'Y' ? 'true' : 'false') ?>,
				isScrumProject: <?= ($arResult['isScrumProject'] ? 'true' : 'false') ?>,
				isOpened: <?= ($arResult['Group']['OPENED'] === 'Y' ? 'true' : 'false') ?>,
				currentUserId: <?= ($USER->isAuthorized() ? $USER->getid() : 0) ?>,

				userRole: '<?=CUtil::JSUrlEscape($arResult["CurrentUserPerms"]["UserRole"])?>',
				userIsMember: <?=($arResult["CurrentUserPerms"]["UserIsMember"] ? 'true' : 'false')?>,
				userIsAutoMember: <?=(isset($arResult["CurrentUserPerms"]["UserIsAutoMember"]) && $arResult["CurrentUserPerms"]["UserIsAutoMember"] ? 'true' : 'false')?>,
				userIsScrumMaster: <?= (isset($arResult['CurrentUserPerms']['UserIsScrumMaster']) && $arResult['CurrentUserPerms']['UserIsScrumMaster'] ? 'true' : 'false') ?>,

				initiatedByType: '<?=CUtil::JSUrlEscape($arResult["CurrentUserPerms"]["InitiatedByType"])?>',
				favoritesValue: <?=($arResult["FAVORITES"] ? 'true' : 'false')?>,
				canInitiate: <?=($arResult["CurrentUserPerms"]["UserCanInitiate"] && !$arResult["HideArchiveLinks"] ? 'true' : 'false')?>,
				canProcessRequestsIn: <?=($arResult["CurrentUserPerms"]["UserCanProcessRequestsIn"] && !$arResult["HideArchiveLinks"] ? 'true' : 'false')?>,
				canModify: <?=($arResult["CurrentUserPerms"]["UserCanModifyGroup"] ? 'true' : 'false')?>,
				canModerate: <?=($arResult["CurrentUserPerms"]["UserCanModerateGroup"] ? 'true' : 'false')?>,
				hideArchiveLinks: <?=($arResult["HideArchiveLinks"] ? 'true' : 'false')?>,
				containerNodeId: 'socialnetwork-group-card-box',
				subscribeButtonNodeId: 'group_card_subscribe_button',
				menuButtonNodeId: 'group_card_menu_button',
				sliderMenuNodeId: '<?= CUtil::JSEscape((string)$arParams['SLIDER_MENU_CONTAINER_ID']) ?>',
				styles: {
					tags: {
						box: 'socialnetwork-group-tag-box',
						item: 'socialnetwork-group-tag'
					},
					users: {
						box: 'socialnetwork-group-user-box',
						item: 'socialnetwork-group-user'
					},
				},
				urls: {
					groupsList: '<?= CUtil::JSUrlEscape($arParams["PATH_TO_GROUPS_LIST"]) ?>'
				},
				editFeaturesAllowed: <?= (Helper\Workgroup::getEditFeaturesAvailability() ? 'true' : 'false') ?>,
				copyFeatureAllowed: <?=(Helper\Workgroup::isGroupCopyFeatureEnabled() ? 'true' : 'false')?>,
			})
		});

		BX.message({
			SGCSPathToGroupTag: '<?= CUtil::JSUrlEscape($arParams["PATH_TO_GROUP_TAG"]) ?>',
			SGCSPathToUserProfile: '<?= CUtil::JSUrlEscape($arParams["PATH_TO_USER"]) ?>',
			SGCSWaitTitle: '<?= GetMessageJS("SONET_C6_CARD_WAIT") ?>'
		});
	</script><?php

	$this->SetViewTarget(($arResult['IS_IFRAME'] ? 'pagetitle' : 'sonet-slider-pagetitle'), 1000);

	$bodyClass = $APPLICATION->GetPageProperty("BodyClass");
	$APPLICATION->SetPageProperty("BodyClass", ($bodyClass ? $bodyClass." " : "")."pagetitle-menu-visible");
	include("title_buttons.php");
	$this->EndViewTarget();

	?><div class="socialnetwork-group-content" id="socialnetwork-group-card-box">
		<div class="socialnetwork-group-box">
			<h2 class="socialnetwork-group-title"><?=$arResult['Group']['NAME']?></h2>
		</div><?php

		if ($arResult['Group']['DESCRIPTION'] !== '')
		{
			?><div class="socialnetwork-group-box">
				<div class="socialnetwork-group-desc"><?=$arResult['Group']['DESCRIPTION']?></div>
			</div><?php
		}

		?><div class="socialnetwork-group-box">
			<div class="socialnetwork-group-left"><?=Loc::getMessage('SONET_C6_CREATED')?></div>
			<div class="socialnetwork-group-right"><?php
				echo CComponentUtil::getDateTimeFormatted([
					'TIMESTAMP' => MakeTimeStamp($arResult["Group"]["DATE_CREATE"]),
					'TZ_OFFSET' => CTimeZone::getOffset()
				]);
			?></div>
		</div><?php

		if ($arResult['Group']['PROJECT'] === 'Y')
		{
			?><div class="socialnetwork-group-box">
				<div class="socialnetwork-group-left"><?=Loc::getMessage('SONET_C6_PROJECT_DATE_START')?></div>
				<div class="socialnetwork-group-right"><?=
					!empty($arResult['Group']['PROJECT_DATE_START'])
						? FormatDateFromDB($arResult['Group']['PROJECT_DATE_START'], $arParams['DATE_FORMAT'], true)
						: ''
				?></div>
			</div>
			<div class="socialnetwork-group-box">
				<div class="socialnetwork-group-left"><?=Loc::getMessage('SONET_C6_PROJECT_DATE_FINISH')?></div>
				<div class="socialnetwork-group-right"><?=
					!empty($arResult['Group']['PROJECT_DATE_FINISH'])
						? FormatDateFromDB($arResult['Group']['PROJECT_DATE_FINISH'], $arParams['DATE_FORMAT'], true)
						: ''
				?></div>
			</div><?php
		}

		?><div class="socialnetwork-group-box">
			<div class="socialnetwork-group-left"><?=Loc::getMessage($arResult['Group']['PROJECT'] === 'Y' ? 'SONET_C6_CARD_OWNER_PROJECT' : 'SONET_C6_CARD_OWNER')?></div>
			<div class="socialnetwork-group-right">
				<div class="socialnetwork-group-user-box"><?php
					$owner = $arResult["Owner"];

					$backgroundStyle = (
						!empty($owner["USER_PERSONAL_PHOTO_FILE"])
						&& !empty($owner["USER_PERSONAL_PHOTO_FILE"]["SRC"])
						? "background-image: url('".htmlspecialcharsbx($owner["USER_PERSONAL_PHOTO_FILE"]["SRC"])."'); background-size: cover;"
						: ""
					);

					?><div bx-user-id="<?= (int)$owner['USER_ID'] ?>" class="ui-icon ui-icon-common-user socialnetwork-group-user" title="<?=$owner["NAME_FORMATTED"]?>">
						<i style="<?=$backgroundStyle?>"></i>
					</div>
				</div>
			</div>
		</div>
		<div class="socialnetwork-group-box">
			<div class="socialnetwork-group-left">
				<?=
					Loc::getMessage(
						$arResult['Group']['PROJECT'] === 'Y'
						? $arResult['isScrumProject']
							? 'SONET_C6_CARD_MOD_SCRUM_PROJECT'
							: 'SONET_C6_CARD_MOD_PROJECT'
						: 'SONET_C6_CARD_MOD'
					)
				?> (<?= (int)$arResult["Group"]["NUMBER_OF_MODERATORS"] ?>)
			</div>
			<div class="socialnetwork-group-right">
				<div class="socialnetwork-group-user-box"><?php
					$counter = 0;
					if (
						is_array($arResult["Moderators"]["List"])
						&& !empty($arResult["Moderators"]["List"])
					)
					{
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

							?><div bx-user-id="<?= (int)$moderator['USER_ID'] ?>" class="ui-icon ui-icon-common-user socialnetwork-group-user" title="<?=$moderator["NAME_FORMATTED"]?>">
								<i style="<?=$backgroundStyle?>"></i>
							</div><?php
							$counter++;
						}
					}

					if ($counter >= $arParams['USER_LIMIT'])
					{
						?><div class="socialnetwork-group-user-more">+ <?=(count($arResult["Moderators"]["List"]) - $arParams['USER_LIMIT'])?></div><?php
					}

				?></div>
			</div>
		</div>
		<div class="socialnetwork-group-box">
			<div class="socialnetwork-group-left"><?=Loc::getMessage('SONET_C6_CARD_MEMBERS')?> (<?= (int)$arResult["Group"]["NUMBER_OF_MEMBERS"] ?>)</div>
			<div class="socialnetwork-group-right">
				<div class="socialnetwork-group-user-box"><?php
					$counter = 0;
					if (
						is_array($arResult["Members"]["List"])
						&& !empty($arResult["Members"]["List"])
					)
					{
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

							?><div bx-user-id="<?= (int)$member['USER_ID'] ?>" class="ui-icon ui-icon-common-user socialnetwork-group-user" title="<?=$member["NAME_FORMATTED"]?>">
								<i style="<?=$backgroundStyle?>"></i>
							</div><?php
							$counter++;
						}
					}

					if ($counter >= $arParams['USER_LIMIT'])
					{
						?><div class="socialnetwork-group-user-more">+ <?=(count($arResult["Members"]["List"]) - $arParams['USER_LIMIT'])?></div><?php
					}

				?></div>
			</div>
		</div><?php

		if (
			is_array($arResult['Subjects'])
			&& count($arResult['Subjects']) > 1
		)
		{
			?><div class="socialnetwork-group-box">
				<div class="socialnetwork-group-left"><?=Loc::getMessage('SONET_C6_CARD_SUBJECT')?></div>
				<div class="socialnetwork-group-right"><?=$arResult['Group']['SUBJECT_NAME']?></div>
			</div><?php
		}

		if (
			is_array($arResult["Group"]["KEYWORDS_LIST"])
			&& !empty($arResult["Group"]["KEYWORDS_LIST"])
		)
		{
			?><div class="socialnetwork-group-box">
				<div class="socialnetwork-group-left"><?=Loc::getMessage('SONET_C6_TAGS')?></div>
				<div class="socialnetwork-group-right">
					<div class="socialnetwork-group-tag-box"><?php
						foreach($arResult["Group"]["KEYWORDS_LIST"] as $keyword)
						{
							?><a bx-tag-value="<?=$keyword?>" href="<?= CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP_TAG"], array('tag' => $keyword)) ?>" class="socialnetwork-group-tag"><?=$keyword?></a><?php
						}
					?></div>
				</div>
			</div><?php
		}

	?></div><?php
}
