<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentPath */
/** @var string $componentName */
/** @var string $componentTemplate */
/** @var string $templateFolder */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */
/** @var CBitrixComponent $component */

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\UI;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Web\Uri;

$APPLICATION->SetAdditionalCSS('/bitrix/components/bitrix/socialnetwork.log.ex/templates/.default/style.css');
$APPLICATION->SetAdditionalCSS('/bitrix/components/bitrix/socialnetwork.blog.blog/templates/.default/style.css');
if (!$arResult["bFromList"])
{
	Asset::getInstance()->addJs("/bitrix/components/bitrix/socialnetwork.log.ex/templates/.default/script.js");
}

Asset::getInstance()->addJs('/bitrix/components/bitrix/socialnetwork.blog.post/templates/.default/index.js');

$ajax_page = $APPLICATION->GetCurPageParam("", array("logajax", "bxajaxid", "logout"));
$voteId = false;

$extensions = [
	'ajax',
	'viewer',
	'popup',
	'clipboard',
];
if ($arResult["bTasksAvailable"])
{
	$extensions[] = 'tasks_util_base';
	$extensions[] = 'tasks_util_query';
}
CJSCore::Init($extensions);

UI\Extension::load([
	'main.core',
	'ui.buttons',
	'ui.animations',
	'ui.tooltip',
	'ui.icons.b24',
	'main.rating',
	'socialnetwork.commentaux',
	'socialnetwork.livefeed',
	'landing_note',
	'ui.livefeed.background'
]);

$messages = Loc::loadLanguageFile(__FILE__);

$bodyClass = $APPLICATION->GetPageProperty("BodyClass");
$bodyClass = $bodyClass ? $bodyClass." no-all-paddings" : "no-all-paddings";
$APPLICATION->SetPageProperty("BodyClass", $bodyClass);
$arParams['MODE'] ??= null;

?><div
 class="feed-item-wrap"
 data-livefeed-id="<?= (int) ($arParams['LOG_ID'] ?? null) ?>"
 bx-content-view-key-signed="<?= htmlspecialcharsbx($arResult['CONTENT_VIEW_KEY_SIGNED']) ?>"><?php

?><script>
	BX.message(<?= Json::encode($messages) ?>);
	BX.message({
		BLOG_LINK: '<?=GetMessageJS("BLOG_LINK2")?>',
		<?php
		if (!$arResult["bFromList"])
		{
			?>
			sonetLESetPath: '<?=CUtil::JSEscape('/bitrix/components/bitrix/socialnetwork.log.entry/ajax.php')?>',
			sonetLSessid: '<?=bitrix_sessid_get()?>'
			<?php
		}
		?>
	});

	BX.ready(function() {
		if (
			typeof oSBPostManager != 'undefined'
			&& !oSBPostManager.inited
		)
		{
			oSBPostManager.init({
				tagLinkPattern: '<?=(!empty($arParams["PATH_TO_LOG_TAG"]) ? CUtil::JSEscape($arParams["PATH_TO_LOG_TAG"]) : '')?>',
				readOnly: '<?=($arResult['ReadOnly'] ? 'Y' : 'N')?>',
				pathToUser: '<?=(!empty($arParams["PATH_TO_USER"]) ? CUtil::JSEscape($arParams["PATH_TO_USER"]) : '')?>',
				pathToPost: '<?=(!empty($arParams["PATH_TO_POST"]) ? CUtil::JSEscape($arParams["PATH_TO_POST"]) : '')?>',
				allowToAll: <?=(empty($arResult["FEED_DESTINATION"]) || !isset($arResult["FEED_DESTINATION"]["DENY_TOALL"]) || !$arResult["FEED_DESTINATION"]["DENY_TOALL"] ? 'true' : 'false')?>,
				allowSearchEmailUsers: <?=($arResult["ALLOW_EMAIL_INVITATION"] ? 'true' : 'false')?>
			});
		}
	});
</script><?php

if (($arResult["MESSAGE"] ?? '') <> '')
{
	?><div class="feed-add-successfully">
		<span class="feed-add-info-text"><span class="feed-add-info-icon"></span><?=$arResult["MESSAGE"]?></span>
	</div><?php
}

if (($arResult["ERROR_MESSAGE"] ?? '') <> '')
{
	?><div class="feed-add-error">
		<span class="feed-add-info-text"><span class="feed-add-info-icon"></span><?=$arResult["ERROR_MESSAGE"]?></span>
	</div><?php
}

if (($arResult["FATAL_MESSAGE"] ?? '') <> '')
{
	if (!$arResult["bFromList"])
	{
		?><div class="feed-add-error">
			<span class="feed-add-info-text"><span class="feed-add-info-icon"></span><?= $arResult["FATAL_MESSAGE"] ?></span>
		</div><?php
	}
}
elseif (($arResult["NOTE_MESSAGE"] ?? '') <> '')
{
	?><div class="feed-add-successfully">
		<span class="feed-add-info-text"><span class="feed-add-info-icon"></span><?= $arResult["NOTE_MESSAGE"] ?></span>
	</div><?php
}
else
{
	if (!empty($arResult["Post"]))
	{
		$APPLICATION->IncludeComponent("bitrix:main.user.link",
			'',
			array(
				"AJAX_ONLY" => "Y",
				"PATH_TO_SONET_USER_PROFILE" => $arParams["~PATH_TO_USER"],
				"PATH_TO_SONET_MESSAGES_CHAT" => $arParams["~PATH_TO_MESSAGES_CHAT"],
				"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT"],
				"SHOW_YEAR" => $arParams["SHOW_YEAR"],
				"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
				"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
				"PATH_TO_CONPANY_DEPARTMENT" => $arParams["~PATH_TO_CONPANY_DEPARTMENT"],
				"PATH_TO_VIDEO_CALL" => $arParams["~PATH_TO_VIDEO_CALL"],
				"HTML_ID" => "user".$arResult["Post"]["ID"],
			),
			false,
			array("HIDE_ICONS" => "Y")
		);

		$classNameList = [ 'feed-post-block' ];

		if (
			isset($arResult["Post"]["new"])
			&& $arResult["Post"]["new"] === "Y"
		)
		{
			$classNameList[] = 'feed-post-block-new';
		}

		if ($arResult["Post"]["IS_IMPORTANT"])
		{
			$classNameList[] = 'feed-imp-post';
		}

		if (
			$arResult["Post"]["HAS_TAGS"] === "Y"
			|| (
				isset($arResult["POST_PROPERTIES"]["DATA"]["UF_BLOG_POST_FILE"])
				&& !empty($arResult["POST_PROPERTIES"]["DATA"]["UF_BLOG_POST_FILE"]["VALUE"])
			)
			|| (
				isset($arResult["POST_PROPERTIES"]["DATA"]["UF_BLOG_POST_DOC"])
				&& !empty($arResult["POST_PROPERTIES"]["DATA"]["UF_BLOG_POST_DOC"]["VALUE"])
			)
		)
		{
			$classNameList[] = 'feed-post-block-files';
		}

		if (
			isset($arResult["POST_PROPERTIES"]["DATA"]["UF_BLOG_POST_VOTE"])
			&& !empty($arResult["POST_PROPERTIES"]["DATA"]["UF_BLOG_POST_VOTE"]["VALUE"])
		)
		{
			$classNameList[] = 'feed-post-block-vote';
		}

		if (
			!empty($arResult['GRATITUDE'])
			&& !empty($arResult['GRATITUDE']['TYPE'])
			&& !empty($arResult['GRATITUDE']['TYPE']['XML_ID'])
		)
		{
			$classNameList[] = 'feed-post-block-background';
			$classNameList[] = 'feed-post-block-grat';
			$classNameList[] = 'feed-post-block-grat-'.htmlspecialcharsbx($arResult['GRATITUDE']['TYPE']['XML_ID']);
		}

		if (!$arResult["bFromList"])
		{
			$classNameList[] = 'feed-post-block-short';
		}

		if (
			$arResult["Post"]["HAS_TAGS"] === "Y"
			|| (
				isset($arResult["POST_PROPERTIES"]["DATA"]["UF_BLOG_POST_FILE"])
				&& !empty($arResult["POST_PROPERTIES"]["DATA"]["UF_BLOG_POST_FILE"]["VALUE"])
			)
			|| (
				isset($arResult["POST_PROPERTIES"]["DATA"]["UF_BLOG_POST_DOC"])
				&& !empty($arResult["POST_PROPERTIES"]["DATA"]["UF_BLOG_POST_DOC"]["VALUE"])
			)
			|| !empty($arResult["URL_PREVIEW"])
		)
		{
			$classNameList[] = 'feed-post-block-separator';
		}

		if (!empty($arParams['PINNED_PANEL_DATA']))
		{
			$classNameList[] = 'feed-post-block-pinned';
		}

		if (
			!empty($arParams['FOLLOW'])
			&& $arParams['FOLLOW'] === 'N'
		)
		{
			$classNameList[] = 'feed-post-block-unfollowed';
		}

		if (
			(
				!isset($arParams['IS_CRM'])
				|| $arParams['IS_CRM'] !== 'Y'
			)
			&& (
				!isset($arParams['TYPE'])
				|| !in_array($arParams['TYPE'], [ 'DRAFT', 'MODERATION' ])
			)
			&& $USER->isAuthorized()
		)
		{
			$pinned = (
				!empty($arParams['PINNED_PANEL_DATA'])
				|| (isset($arParams['PINNED']) && $arParams['PINNED'] === 'Y')
			);

			$classNameList[] = 'feed-post-block-pin';
			if ($pinned)
			{
				$classNameList[] = 'feed-post-block-pin-active';
			}
		}

		$hasNotEmptyProperty = (
			isset($arResult['POST_PROPERTIES']['DATA'])
			&& is_array($arResult['POST_PROPERTIES']['DATA'])
			&& array_reduce($arResult['POST_PROPERTIES']['DATA'], static function ($val, $propertyData) {
				return $val || (
					$propertyData['VALUE'] !== null
					&& $propertyData['VALUE'] !== false
					&& $propertyData['VALUE'] !== []
					&& !(
						$propertyData['VALUE'] === '0'
						&& $propertyData['USER_TYPE']['BASE_TYPE'] === 'int'
					)
				);
			}, false)
		);

		if (
			$hasNotEmptyProperty
			|| !empty($arResult['Category'])
			|| !empty($arResult['URL_PREVIEW'])
		)
		{
			$classNameList[] = 'feed-post-block-has-bottom';
		}

		if (
			!$arResult["ReadOnly"]
			&& array_key_exists("FOLLOW", $arParams)
			&& $arParams["FOLLOW"] <> ''
			&& (int)$arParams["LOG_ID"] > 0
		)
		{
			?><script>
				BX.message({
					sonetBPFollowY: '<?=GetMessageJS("BLOG_POST_FOLLOW_Y")?>',
					sonetBPFollowN: '<?=GetMessageJS("BLOG_POST_FOLLOW_N")?>'
				});
			</script><?php
		}

		?><script>
			BX.viewElementBind(
				'blg-post-img-<?=$arResult["Post"]["ID"]?>',
				{showTitle: true},
				function(node){
					return BX.type.isElementNode(node) && (node.getAttribute('data-bx-viewer') || node.getAttribute('data-bx-image'));
				}
			);

			var postDest<?= $arResult['Post']['ID'] ?> = <?= CUtil::phpToJSObject($arResult['postDestEntities'], false, false, true) ?>;

			BX.ready(function () {
				if (
					(
						BX.type.isUndefined(BX.Livefeed)
						|| !BX.Livefeed.FilterInstance.filterApi
					)
					&& BX('blg-post-<?=$arResult["Post"]["ID"]?>')
				)
				{
					BX('blg-post-<?=$arResult["Post"]["ID"]?>').addEventListener('click', BX.delegate(function(e) {
						var tagValue = BX.getEventTarget(e).getAttribute('bx-tag-value');
						if (BX.type.isNotEmptyString(tagValue))
						{
							if (this.clickTag(tagValue))
							{
								e.preventDefault();
							}
						}
					}, oSBPostManager), true);
				}
			});
		</script><?php

		$commentsContent = '';
		$commentsResult = [];

		if (
			($arResult["CommentPerm"] >= BLOG_PERMS_READ)
			&& (
				$arParams['MODE'] !== 'LANDING'
				|| empty($arParams['MODE'])
			)
			&& (
				!isset($arResult["TYPE"])
				|| !in_array($arParams["TYPE"], [ "DRAFT", "MODERATION" ])
			)
		)
		{
			ob_start();

			$commentsResult = $APPLICATION->IncludeComponent(
				"bitrix:socialnetwork.blog.post.comment",
				"",
				[
					"bPublicPage" => $arResult["bPublicPage"],
					"SEF" => $arParams["SEF"],
					"BLOG_VAR" => $arResult["ALIASES"]["blog"] ?? '',
					"POST_VAR" => $arParams["POST_VAR"],
					"USER_VAR" => $arParams["USER_VAR"],
					"PAGE_VAR" => $arParams["PAGE_VAR"],
					"PATH_TO_BLOG" => $arParams["PATH_TO_BLOG"],
					"PATH_TO_POST" => $arParams["PATH_TO_POST"],
					"PATH_TO_USER" => $arParams["PATH_TO_USER"],
					"PATH_TO_SMILE" => $arParams["PATH_TO_SMILE"],
					"PATH_TO_MESSAGES_CHAT" => $arParams["PATH_TO_MESSAGES_CHAT"],
					"ID" => $arResult["Post"]["ID"],
					"CACHE_TYPE" => $arParams["CACHE_TYPE"],
					"CACHE_TIME" => $arParams["CACHE_TIME"],
					"COMMENTS_COUNT" => $arParams["COMMENTS_COUNT"] ?? 0,
					"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT"],
					"DATE_TIME_FORMAT_WITHOUT_YEAR" => $arParams["DATE_TIME_FORMAT_WITHOUT_YEAR"],
					"TIME_FORMAT" => $arParams["TIME_FORMAT"],
					"USE_ASC_PAGING" => $arParams["USE_ASC_PAGING"] ?? '',
					"USER_ID" => $arResult["USER_ID"],
					"GROUP_ID" => $arParams["GROUP_ID"],
					"SONET_GROUP_ID" => $arParams["SONET_GROUP_ID"] ?? null,
					"NOT_USE_COMMENT_TITLE" => "Y",
					"USE_SOCNET" => "Y",
					"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
					"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
					"SHOW_YEAR" => $arParams["SHOW_YEAR"],
					"PATH_TO_CONPANY_DEPARTMENT" => $arParams["PATH_TO_CONPANY_DEPARTMENT"],
					"PATH_TO_VIDEO_CALL" => $arParams["PATH_TO_VIDEO_CALL"],
					"SHOW_RATING" => $arParams["SHOW_RATING"],
					"RATING_TYPE" => ($arResult["bIntranetInstalled"] || $arParams["RATING_TYPE"] === "like" ? "like_react" : $arParams["RATING_TYPE"]),
					"IMAGE_MAX_WIDTH" => $arParams["IMAGE_MAX_WIDTH"],
					"IMAGE_MAX_HEIGHT" => $arParams["IMAGE_MAX_HEIGHT"],
					"ALLOW_VIDEO" => $arParams["ALLOW_VIDEO"] ?? null,
					"ALLOW_IMAGE_UPLOAD" => $arParams["ALLOW_IMAGE_UPLOAD"] ?? null,
					"SHOW_SPAM" => $arParams["BLOG_SHOW_SPAM"] ?? null,
					"NO_URL_IN_COMMENTS" => $arParams["BLOG_NO_URL_IN_COMMENTS"],
					"NO_URL_IN_COMMENTS_AUTHORITY" => $arParams["BLOG_NO_URL_IN_COMMENTS_AUTHORITY"],
					"ALLOW_POST_CODE" => $arParams["BLOG_ALLOW_POST_CODE"] ?? null,
					"AJAX_POST" => "Y",
					"POST_DATA" => $arResult["PostSrc"],
					"BLOG_DATA" => $arResult["Blog"],
					"FROM_LOG" => $arParams["FROM_LOG"] ?? null,
					"bFromList" => $arResult["bFromList"],
					"LAST_LOG_TS" => $arParams["LAST_LOG_TS"] ?? null,
					"MARK_NEW_COMMENTS" => $arParams["MARK_NEW_COMMENTS"] ?? null,
					"AVATAR_SIZE" => $arParams["AVATAR_SIZE"],
					"AVATAR_SIZE_COMMON" => $arParams["AVATAR_SIZE_COMMON"],
					"AVATAR_SIZE_COMMENT" => $arParams["AVATAR_SIZE_COMMENT"],
					"FOLLOW" => $arParams["FOLLOW"] ?? null,
					"LOG_ID" => (int) ($arParams["LOG_ID"] ?? null),
					"LOG_CONTENT_ITEM_TYPE" => (!empty($arParams['LOG_CONTENT_ITEM_ID']) ? $arParams['LOG_CONTENT_ITEM_TYPE'] : ''),
					"LOG_CONTENT_ITEM_ID" => (!empty($arParams['LOG_CONTENT_ITEM_ID']) ? (int)$arParams['LOG_CONTENT_ITEM_ID'] : 0),
					"CREATED_BY_ID" => $arParams["CREATED_BY_ID"] ?? null,
					"MOBILE" => $arParams["MOBILE"] ?? null,
					"LAZYLOAD" => $arParams["LAZYLOAD"] ?? null,
					"CAN_USER_COMMENT" => (
						!isset($arResult["CanComment"])
						|| $arResult["CanComment"] ? 'Y' : 'N'
					),
					"NAV_TYPE_NEW" => "Y",
					"SELECTOR_VERSION" => $arResult["SELECTOR_VERSION"] ?? null,
					'FORM_ID' => $arParams['FORM_ID'],
				],
				$component
			);

			$commentsContent = ob_get_clean();
		}

		?><div
			 class="<?=implode(' ', $classNameList)?>"
			 id="blg-post-<?=$arResult["Post"]["ID"]?>"
			 data-livefeed-id="<?=(int) ($arParams['LOG_ID'] ?? null)?>"
			 bx-content-view-key-signed="<?= htmlspecialcharsbx($arResult['CONTENT_VIEW_KEY_SIGNED']) ?>"
			 data-menu-id="blog-post-<?=(int)$arResult["Post"]["ID"]?>"
			<?php
			if (isset($pinned))
			{
				?>
				 data-livefeed-post-pinned="<?=($pinned ? 'Y' : 'N')?>"
				 data-security-entity-pin="<?= (int)$arParams['LOG_ID'] ?>"
				 data-security-token-pin="<?= htmlspecialcharsbx($arResult['LOG_ID_TOKEN']) ?>"
				<?php
			}
			?>><a name="post<?= $arResult['Post']['ID'] ?>"></a><?php
			$aditStylesList = [ 'feed-post-cont-wrap' ];

			if (
				isset($arResult["Post"]["hidden"])
				&& $arResult["Post"]["hidden"] === 'Y'
			)
			{
				$aditStylesList[] = 'feed-hidden-post';
			}

			if (
				array_key_exists("USER_ID", $arParams)
				&& (int)$arParams["USER_ID"] > 0
			)
			{
				$aditStylesList[] = 'sonet-log-item-createdby-'.$arParams["USER_ID"];
			}

			if (
				array_key_exists("ENTITY_TYPE", $arParams)
				&& $arParams["ENTITY_TYPE"] <> ''
				&& array_key_exists("ENTITY_ID", $arParams)
				&& (int)$arParams["ENTITY_ID"] > 0
			)
			{
				$aditStylesList[] = 'sonet-log-item-where-'.$arParams["ENTITY_TYPE"].'-'.$arParams["ENTITY_ID"].'-all';
				if (
					array_key_exists("EVENT_ID", $arParams)
					&& $arParams["EVENT_ID"] <> ''
				)
				{
					$aditStylesList[] = 'sonet-log-item-where-'.$arParams["ENTITY_TYPE"].'-'.$arParams["ENTITY_ID"].'-'.str_replace('_', '-', $arParams["EVENT_ID"]);
					if (
						array_key_exists("EVENT_ID_FULLSET", $arParams)
						&& $arParams["EVENT_ID_FULLSET"] <> ''
					)
					{
						$aditStylesList[] = 'sonet-log-item-where-'.$arParams["ENTITY_TYPE"].'-'.$arParams["ENTITY_ID"].'-'.str_replace('_', '-', $arParams["EVENT_ID_FULLSET"]);
					}
				}
			}

			$avatar = false;
			if (
				isset($arResult["arUser"]["PERSONAL_PHOTO_resized"]["src"]) &&
				$arResult["arUser"]["PERSONAL_PHOTO_resized"]["src"] <> ''
			)
			{
				$avatar = $arResult["arUser"]["PERSONAL_PHOTO_resized"]["src"];
			}

			$style = ($avatar ? "background: url('" . Uri::urnEncode($avatar) . "'); background-size: cover;" : "");

			?><div class="<?=implode(' ', $aditStylesList)?>" id="blg-post-img-<?=$arResult["Post"]["ID"]?>">
				<div class="ui-icon ui-icon-common-user feed-user-avatar"><i style="<?= $style ?>"></i></div><?php
				?><div class="feed-post-pinned-block"><?php

					?><div class="feed-post-pinned-title"><?php
						if (
							!empty($arParams['PINNED_PANEL_DATA'])
							&& $arParams['PINNED_PANEL_DATA']['TITLE'] <> ''
						)
						{
							?><?= $arParams['PINNED_PANEL_DATA']['TITLE'] ?><?php
						}
					?></div><?php

					?><div class="feed-post-pinned-text-box"><?php
						?><div class="feed-post-pinned-desc"><?php
							if (
								!empty($arParams['PINNED_PANEL_DATA'])
								&& $arParams['PINNED_PANEL_DATA']['DESCRIPTION'] <> ''
							)
							{
								?><?= $arParams['PINNED_PANEL_DATA']['DESCRIPTION'] ?><?php
							}
						?></div><?php
						?><a href="#" class="feed-post-pinned-link feed-post-pinned-link-expand"><?= Loc::getMessage('BLOG_POST_PINNED_EXPAND') ?></a><?php
					?></div><?php
				?></div><?php

				$canEditPost = (
					(
						empty($arParams['MODE'])
						|| $arParams['MODE'] !== 'LANDING'
					)
					&& (string)$arResult['urlToEdit'] !== ''
					&& (
						$arResult['PostPerm'] >= BLOG_PERMS_FULL
						|| (
							$arResult['PostPerm'] >= BLOG_PERMS_WRITE
							&& (int)$arResult['Post']['AUTHOR_ID'] === (int)$arResult['USER_ID']
						)
					)
				);

				$classList = [
					'feed-post-title-block',
				];

				if ($canEditPost)
				{
					$classList[] = '--can-edit';
				}

				?><div class="<?= implode(' ', $classList) ?>"><?php

					$anchor_id = $arResult["Post"]["ID"];
					$arTooltipParams = (
						$arResult["bPublicPage"]
							? [
								'entityType' => 'LOG_ENTRY',
								'entityId' => (int)$arParams['LOG_ID'],
							]
							: []
					);

					$arTmpUser = array(
						"NAME" => $arResult["arUser"]["~NAME"],
						"LAST_NAME" => $arResult["arUser"]["~LAST_NAME"],
						"SECOND_NAME" => $arResult["arUser"]["~SECOND_NAME"],
						"LOGIN" => $arResult["arUser"]["~LOGIN"],
						"NAME_LIST_FORMATTED" => "",
					);

					if (
						isset($arParams['SEO_USER'])
						&& $arParams['SEO_USER'] === 'Y'
					)
					{
						?><noindex><?php
					}

					if ($arResult["bPublicPage"])
					{
						?><span
							class="feed-post-user-name<?=(array_key_exists("isExtranet", $arResult["arUser"]) && $arResult["arUser"]["isExtranet"] ? " feed-post-user-name-extranet" : "")?>"
							id="bp_<?=$anchor_id?>"
							bx-post-author-id="<?=$arResult["arUser"]["ID"]?>"
							bx-post-author-gender="<?=$arResult["arUser"]["PERSONAL_GENDER"]?>"
							bx-tooltip-user-id="<?=$arResult["arUser"]["ID"]?>"
							bx-tooltip-params="<?=htmlspecialcharsbx(Json::encode($arTooltipParams))?>"
						><?= CUser::FormatName($arParams['NAME_TEMPLATE'], $arTmpUser, $arParams['SHOW_LOGIN'] !== 'N') ?></span><?php
					}
					else
					{
						?><a
							class="feed-post-user-name<?=(array_key_exists("isExtranet", $arResult["arUser"]) && $arResult["arUser"]["isExtranet"] ? " feed-post-user-name-extranet" : "")?>"
							id="bp_<?=$anchor_id?>" href="<?=$arResult["arUser"]["url"]?>"
							bx-post-author-id="<?=$arResult["arUser"]["ID"]?>"
							bx-post-author-gender="<?=$arResult["arUser"]["PERSONAL_GENDER"]?>"
							bx-tooltip-user-id="<?=$arResult["arUser"]["ID"]?>"
							bx-tooltip-params="<?=htmlspecialcharsbx(Json::encode($arTooltipParams))?>"
						><?= CUser::FormatName($arParams['NAME_TEMPLATE'], $arTmpUser, $arParams['SHOW_LOGIN'] !== 'N') ?></a><?php
					}

					if (
						isset($arParams['SEO_USER'])
						&& $arParams['SEO_USER'] === 'Y'
					)
					{
						?></noindex><?php
					}

					if (
						!empty($arResult["Post"]["SPERM_SHOW"])
						&& (
							empty($arParams['MODE'])
							|| $arParams['MODE'] !== 'LANDING'
						)
					)
					{
						?><span class="feed-add-post-destination-cont<?=($arResult["Post"]["LIMITED_VIEW"] ? ' feed-add-post-destination-limited-view' : '')?>"><?php

						?><span class="feed-add-post-destination-icon"><span style="position: absolute; left: -3000px; overflow: hidden;">&nbsp;-&gt;&nbsp;</span></span><?php

						$cnt = (
							(!empty($arResult["Post"]["SPERM_SHOW"]["U"]) ? count($arResult["Post"]["SPERM_SHOW"]["U"]) : 0) +
							(!empty($arResult["Post"]["SPERM_SHOW"]["SG"]) ? count($arResult["Post"]["SPERM_SHOW"]["SG"]) : 0) +
							(!empty($arResult["Post"]["SPERM_SHOW"]["DR"]) ? count($arResult["Post"]["SPERM_SHOW"]["DR"]) : 0) +
							(!empty($arResult["Post"]["SPERM_SHOW"]["CRMCONTACT"]) ? count($arResult["Post"]["SPERM_SHOW"]["CRMCONTACT"]) : 0)
						);

						$showMaxCount = ($cnt > 4 ? 4 : 5);

						$i = 0;
						if (!empty($arResult["Post"]["SPERM_SHOW"]["U"]))
						{
							foreach ($arResult["Post"]["SPERM_SHOW"]["U"] as $id => $val)
							{
								$i++;
								if ($i === $showMaxCount)
								{
									$more_cnt = $cnt + (int)$arResult["Post"]["SPERM_HIDDEN"] - ($showMaxCount - 1);
									$suffix = (
										($more_cnt % 100) > 10
										&& ($more_cnt % 100) < 20
											? 5
											: $more_cnt % 10
									);

									?><a href="javascript:void(0)" onclick="showHiddenDestination('<?= $arResult['Post']['ID'] ?>', this)" class="feed-post-link-new"><?= Loc::getMessage('BLOG_DESTINATION_MORE_' . $suffix, [ '#NUM#' => $more_cnt ])?></a><span id="blog-destination-hidden-<?= $arResult['Post']['ID'] ?>" style="display:none;"><?php
								}
								if ($i !== 1)
								{
									echo ", ";
								}
								if ($val['NAME'] !== 'All')
								{
									$anchor_id = $arResult["Post"]["ID"]."_".$id;
									$classNameList = [ 'feed-add-post-destination-new' ];
									$arTooltipParams = array();

									if (
										array_key_exists("IS_EXTRANET", $val)
										&& $val['IS_EXTRANET'] === 'Y'
									)
									{
										$classNameList[] = 'feed-add-post-destination-new-extranet';
									}
									elseif ($val['IS_EMAIL'] === 'Y')
									{
										$classNameList[] = 'feed-add-post-destination-new-email';
										$arTooltipParams = array(
											'entityType' => 'LOG_ENTRY',
											'entityId' => (int)$arParams['LOG_ID']
										);
									}

									if ($arResult["bPublicPage"])
									{
										?><span
										 id="dest_<?=$anchor_id?>"
										 class="<?=implode(' ', $classNameList)?>"
										 bx-tooltip-user-id="<?= $val["ID"] ?>"
										 bx-tooltip-params="<?= htmlspecialcharsbx(Json::encode($arTooltipParams)) ?>"
										><?= $val['NAME'] ?></span><?php
									}
									else
									{
										?><a
										 id="dest_<?=$anchor_id?>"
										 href="<?=$val["URL"]?>"
										 class="<?=implode(' ', $classNameList)?>"
										 bx-tooltip-user-id="<?=$val["ID"]?>"
										 bx-tooltip-params="<?= htmlspecialcharsbx(Json::encode($arTooltipParams)) ?>"
										 data-bx-entity-type="<?= htmlspecialcharsbx($val['entityType'] ?? '') ?>"
										 data-bx-entity-id="<?= htmlspecialcharsbx($val['entityId'] ?? '') ?>"
										><?= $val['NAME'] ?></a><?php
									}
								}
								elseif (
									$val["URL"] <> ''
									&& !$arResult["bPublicPage"]
								)
								{
									?><a
									 href="<?= $val['URL'] ?>"
									 class="feed-add-post-destination-new"
									 data-bx-entity-type="<?= htmlspecialcharsbx($val['entityType'] ?? '') ?>"
									 data-bx-entity-id="<?= htmlspecialcharsbx($val['entityId'] ?? '') ?>"
									><?= ($arResult['bIntranetInstalled'] ? Loc::getMessage('BLOG_DESTINATION_ALL') : Loc::getMessage('BLOG_DESTINATION_ALL_BSM')) ?></a><?php
								}
								else
								{
									?><span
									 class="feed-add-post-destination-new"
									 data-bx-entity-type="<?= htmlspecialcharsbx($val['entityType'] ?? '') ?>"
									 data-bx-entity-id="<?= htmlspecialcharsbx($val['entityId'] ?? '') ?>"
									><?= ($arResult['bIntranetInstalled'] ? Loc::getMessage('BLOG_DESTINATION_ALL') : Loc::getMessage('BLOG_DESTINATION_ALL_BSM')) ?></span><?php
								}
							}
						}

						if (!empty($arResult["Post"]["SPERM_SHOW"]["SG"]))
						{
							foreach ($arResult["Post"]["SPERM_SHOW"]["SG"] as $id => $val)
							{
								$i++;
								if ($i === $showMaxCount)
								{
									$more_cnt = $cnt + (int)$arResult['Post']['SPERM_HIDDEN'] - ($showMaxCount - 1);
									$suffix = (
										($more_cnt % 100) > 10
										&& ($more_cnt % 100) < 20
											? 5
											: $more_cnt % 10
									);

									?><a href="javascript:void(0)" onclick="showHiddenDestination('<?=$arResult["Post"]["ID"]?>', this)" class="feed-post-link-new"><?=GetMessage("BLOG_DESTINATION_MORE_".$suffix, Array("#NUM#" => $more_cnt))?></a><span id="blog-destination-hidden-<?=$arResult["Post"]["ID"]?>" style="display:none;"><?php
								}
								if ($i != 1)
								{
									echo ", ";
								}
								if (
									$val["URL"] <> ''
									&& !$arResult["bPublicPage"]
								)
								{
									?><a
									 href="<?=$val["URL"]?>"
									 class="feed-add-post-destination-new<?= (array_key_exists('IS_EXTRANET', $val) && $val["IS_EXTRANET"] === 'Y' ? ' feed-add-post-destination-new-extranet' : '') ?>"
									 target="_top"
									 data-bx-entity-type="<?= htmlspecialcharsbx($val['entityType'] ?? '') ?>"
									 data-bx-entity-id="<?= htmlspecialcharsbx($val['entityId'] ?? '') ?>"
									><?= $val['NAME'] ?></a><?php
								}
								else
								{
									?><span
									 class="feed-add-post-destination-new"
									 data-bx-entity-type="<?= htmlspecialcharsbx($val['entityType'] ?? '') ?>"
									 data-bx-entity-id="<?= htmlspecialcharsbx($val['entityId'] ?? '') ?>"
									><?= $val["NAME"] ?></span><?php
								}
							}
						}

						if (!empty($arResult["Post"]["SPERM_SHOW"]["DR"]))
						{
							foreach ($arResult["Post"]["SPERM_SHOW"]["DR"] as $id => $val)
							{
								$i++;
								if ($i === $showMaxCount)
								{
									$more_cnt = $cnt + (int)$arResult["Post"]["SPERM_HIDDEN"] - ($showMaxCount - 1);
									$suffix = (
										($more_cnt % 100) > 10
										&& ($more_cnt % 100) < 20
											? 5
											: $more_cnt % 10
									);

									?><a href="javascript:void(0)" onclick="showHiddenDestination('<?=$arResult["Post"]["ID"]?>', this)" class="feed-post-link-new"><?=GetMessage("BLOG_DESTINATION_MORE_".$suffix, Array("#NUM#" => $more_cnt))?></a><span id="blog-destination-hidden-<?=$arResult["Post"]["ID"]?>" style="display:none;"><?php
								}

								if ($i !== 1)
								{
									echo ", ";
								}

								if (
									$val["URL"] <> ''
									&& !$arResult["bExtranetSite"]
								)
								{
									?><a
									 href="<?=$val["URL"]?>"
									 class="feed-add-post-destination-new"
									 target="_top"
									 data-bx-entity-type="<?= htmlspecialcharsbx($val['entityType'] ?? '') ?>"
									 data-bx-entity-id="<?= htmlspecialcharsbx($val['entityId'] ?? '') ?>"
									><?=$val["NAME"]?></a><?php
								}
								else
								{
									?><span
									 class="feed-add-post-destination-new"
									 data-bx-entity-type="<?= htmlspecialcharsbx($val['entityType'] ?? '') ?>"
									 data-bx-entity-id="<?= htmlspecialcharsbx($val['entityId'] ?? '') ?>"
									><?=$val["NAME"]?></span><?php
								}
							}
						}

						if (!empty($arResult["Post"]["SPERM_SHOW"]["CRMCONTACT"]))
						{
							foreach ($arResult["Post"]["SPERM_SHOW"]["CRMCONTACT"] as $id => $val)
							{
								$i++;
								if ($i === $showMaxCount)
								{
									$more_cnt = $cnt + (int)$arResult["Post"]["SPERM_HIDDEN"] - ($showMaxCount - 1);
									$suffix = (
										($more_cnt % 100) > 10
										&& ($more_cnt % 100) < 20
											? 5
											: $more_cnt % 10
									);

									?><a href="javascript:void(0)" onclick="showHiddenDestination('<?=$arResult["Post"]["ID"]?>', this)" class="feed-post-link-new"><?=GetMessage("BLOG_DESTINATION_MORE_".$suffix, Array("#NUM#" => $more_cnt))?></a><span id="blog-destination-hidden-<?=$arResult["Post"]["ID"]?>" style="display:none;"><?php
								}
								?><?= ($i !== 1 ? ", " : "") ?><?php

								$classPrefixAdditional = '';
								if (!empty($val["CRM_PREFIX"]))
								{
									$classPrefixAdditional = (
										!$arResult["bPublicPage"]
										&& array_key_exists("CRM_USER_ID", $val)
										&& (int)$val["CRM_USER_ID"] > 0
											? " feed-add-post-destination-prefix-crmuser"
											: ""
									);
									?><span data-bx-entity-id="<?= htmlspecialcharsbx($val['entityId'] ?? '') ?>"><?=$val["CRM_PREFIX"]?>:&nbsp;</span><?php
								}
								if (
									$val["URL"] <> ''
									&& !$arResult["bPublicPage"]
								)
								{
									?><a
									 href="<?=$val["URL"]?>"
									 class="feed-add-post-destination-new"
									 target="_top"
									 class="feed-add-post-destination-prefix<?= $classPrefixAdditional ?>"
									 data-bx-entity-type="<?= htmlspecialcharsbx($val['entityType'] ?? '') ?>"
									><?= $val["NAME"] ?></a><?php
								}
								else
								{
									?><span
									 class="feed-add-post-destination-new"
									 class="feed-add-post-destination-prefix<?= $classPrefixAdditional ?>"
									 data-bx-entity-type="<?= htmlspecialcharsbx($val['entityType'] ?? '') ?>"
									><?= $val["NAME"] ?></span><?php
								}
							}
						}

						if (
							isset($arResult["Post"]["SPERM_HIDDEN"])
							&& (int)$arResult["Post"]["SPERM_HIDDEN"] > 0
						)
						{
							$suffix = (
								($arResult["Post"]["SPERM_HIDDEN"] % 100) > 10
								&& ($arResult["Post"]["SPERM_HIDDEN"] % 100) < 20
									? 5
									: $arResult["Post"]["SPERM_HIDDEN"] % 10
							);

							?><span class="feed-add-post-destination-new">&nbsp;<?= Loc::getMessage("BLOG_DESTINATION_HIDDEN_" . $suffix, [
								"#NUM#" => (int)$arResult["Post"]["SPERM_HIDDEN"],
							]) ?></span><?php
						}

						if ($i > ($showMaxCount - 1))
						{
							echo "</span>";
						}

						if ($arResult["Post"]["LIMITED_VIEW"])
						{
							?><span class="feed-add-post-destination-new feed-add-post-destination-limited-view"><?=Loc::getMessage('BLOG_POST_LIMITED_VIEW')?></span><?php
						}
						?></span><?php // feed-add-post-destination-cont
					}

					if ($canEditPost)
					{
						if (!empty($arResult['Post']['BACKGROUND_CODE']))
						{
							$editHref = '#';
							$onClick = "return BX.Livefeed.Post.showBackgroundWarning({
								urlToEdit: '".$arResult["urlToEdit"]."'
							});";
						}
						else
						{
							$editHref = $arResult["urlToEdit"];
							$onClick = '';
						}

						if ($arParams['CONTEXT'] === 'spaces')
						{
							$editHref = '#';
							$onClick = "BX.Livefeed.Post.editSpacesPost('" .
								$arResult['Post']['ID']."' , '".($arParams['SONET_GROUP_ID'] ?? 0) .
								"'); return event.preventDefault();"
							;
						}

						?><a href="<?=$editHref?>" onclick="<?=$onClick?>" title="<?=Loc::getMessage("BLOG_BLOG_BLOG_EDIT")?>" target="_top"><?php
							?><span class="feed-destination-edit" onclick="BX.addClass(this, 'feed-destination-edit-pressed');"></span><?php
						?></a><?php
					}

					$datetime_detail = CComponentUtil::GetDateTimeFormatted(array(
						'TIMESTAMP' => MakeTimeStamp($arResult["Post"]["DATE_PUBLISH"]),
						'DATETIME_FORMAT' => $arParams["DATE_TIME_FORMAT"],
						'DATETIME_FORMAT_WITHOUT_YEAR' => ($arParams["DATE_TIME_FORMAT_WITHOUT_YEAR"] ?? false),
						'TZ_OFFSET' => $arResult["TZ_OFFSET"]
					));

					?><div class="feed-post-time-wrap"><?php
						if ($arResult["bPublicPage"])
						{
							?><div class="feed-time"><?=$datetime_detail?></div><?php
						}
						else
						{
							?><a href="<?=$arResult["Post"]["urlToPost"]?>" target="_top"><div class="feed-time"><?=$datetime_detail?></div></a><?php
						}
					?></div><?php

				?></div><?php // feed-post-title-block

				$classList = [
					'feed-post-text-block'
				];

				if ($arResult["Post"]["IS_IMPORTANT"])
				{
					$classList[] = 'feed-post-block-background';
					$classList[] = 'feed-post-block-important';
				}

				if (!empty($arResult['Post']['BACKGROUND_CODE']))
				{
					$classList[] = 'feed-post-block-colored';
				}

				if (
					!$arResult["Post"]["IS_IMPORTANT"]
					&& (
						$arResult["Post"]["textFormated"] === ''
						|| preg_match('#^<b></b>$#i', $arResult["Post"]["textFormated"]) // empty text patch for attached diles
					)

				)
				{
					$classList[] = 'feed-info-block-empty';
				}

				?><div class="<?=(implode(' ', $classList))?>" id="blog_post_outer_<?=$arResult["Post"]["ID"]?>"><?php

					$classNameList = [];
					if ($arResult["bFromList"])
					{
						$classNameList[] = 'feed-post-contentview';
						$classNameList[] = 'feed-post-text-block-inner';
					}

					if (
						(
							!empty($arResult['GRATITUDE'])
							&& !empty($arResult['GRATITUDE']['TYPE'])
							&& !empty($arResult['GRATITUDE']['TYPE']['XML_ID'])
						)
						|| $arResult["Post"]["IS_IMPORTANT"]
						|| !empty($arResult["Post"]["BACKGROUND_CODE"])
					)
					{
						$classNameList[] = 'feed-post-block-limited-width';
					}

					if (!empty($arResult['Post']['BACKGROUND_CODE']))
					{
						$classNameList[] = 'ui-livefeed-background';
						$classNameList[] = 'ui-livefeed-background-'.preg_replace(['/(\d+)_/', '/_/'], ['', '-'], $arResult['Post']['BACKGROUND_CODE']);
					}

					$idAttribute = ($arResult['bFromList'] ? 'id="feed-post-contentview-BLOG_POST-' . (int)$arResult['Post']['ID'] . '"' : '');

					$contentViewXmlIdAttribute = (
						$arResult['bFromList']
							? 'bx-content-view-xml-id="' . $arResult['CONTENT_ID'] . '"' .
								' bx-content-view-key-signed="' . $arResult['CONTENT_VIEW_KEY_SIGNED'] .'"'
							: ''
					);

					?><div class="<?= implode(' ', $classNameList) ?>" <?= $idAttribute ?> <?= $contentViewXmlIdAttribute ?>>
						<div class="feed-post-text-block-inner-inner" id="blog_post_body_<?= $arResult['Post']['ID'] ?>"><?php

							if ($arResult["Post"]["IS_IMPORTANT"])
							{
								?><div class="feed-important-icon"></div><?php
							}

							if ($arResult["Post"]["MICRO"] !== "Y")
							{
								?><div class="feed-post-item feed-post-item-title"><?php
									if ($arResult["bPublicPage"])
									{
										?><span class="feed-post-title"><?= $arResult['Post']['TITLE'] ?></span><?php
									}
									else
									{
										?><a class="feed-post-title" href="<?= $arResult['Post']['urlToPost'] ?>" target="_top"><?= $arResult['Post']['TITLE'] ?></a><?php
									}
								?></div><?php
							}

							?><div class="feed-post-text"><?= $arResult['Post']['textFormated'] ?></div><?php

							if (
								isset($arResult["Post"]["CUT"])
								&& $arResult["Post"]["CUT"] === "Y"
							)
							{
								?><div><a class="blog-postmore-link" href="<?= $arResult['Post']['urlToPost'] ?>"><?= Loc::getMessage('BLOG_BLOG_BLOG_MORE') ?></a></div><?php
							}

							require($_SERVER['DOCUMENT_ROOT'] . $templateFolder . '/include/important.php');
							require($_SERVER['DOCUMENT_ROOT'] . $templateFolder . '/include/gratitude.php');

						?></div>
					</div><?php

					if ($arResult["bFromList"])
					{
						?><div class="feed-post-text-more" id="blog_post_more_<?= $arResult['Post']['ID'] ?>"><?php
							?><div class="feed-post-text-more-but"></div><?php
						?></div><?php
						?><script>
							BX.ready(function() {
								if (
									BX.type.isUndefined(BX.Livefeed)
									|| BX.type.isUndefined(BX.Livefeed.FeedInstance)
								)
								{
									return;
								}

								BX.Livefeed.FeedInstance.init();

								<?php if (isset($arParams["TYPE"]) && in_array($arParams['TYPE'], ['DRAFT', 'MODERATION'])): ?>
									setTimeout(function() {
										BX.Livefeed.MoreButton.recalcPostsList();
									}, 1000);
								<?php endif; ?>

								BX.Livefeed.FeedInstance.addMoreButton(
									'blog_post_<?= $arResult['Post']['ID'] ?>',
									{
										outerBlockID : 'blog_post_outer_<?= $arResult["Post"]["ID"] ?>',
										bodyBlockID : 'blog_post_body_<?= $arResult["Post"]["ID"] ?>',
										informerBlockID : 'blg-post-inform-<?= (int)$arResult["Post"]["ID"] ?>',
									}
								);
							});
						</script><?php
					}
				?></div><?php

				if (!empty($arResult["images"]))
				{
					?><div class="feed-com-files">
						<div class="feed-com-files-title"><?= Loc::getMessage('BLOG_PHOTO') ?></div>
						<div class="feed-com-files-cont"><?php
							foreach ($arResult["images"] as $val)
							{
								$width = (!empty($val['resizedWidth']) ? 'width="' . (int)$val['resizedWidth'] . '"' : '');
								$height = (!empty($val['resizedHeight']) ? 'height="' . (int)$val['resizedHeight'] . '"' : '');

								?><span class="feed-com-files-photo"><img src="<?= $val["small"] ?>" alt="" border="0" data-bx-image="<?= $val["full"] ?>" <?= $width ?> <?= $height ?>/></span><?php
							}
						?></div>
					</div><?php
				}

				if ($arResult["POST_PROPERTIES"]["SHOW"] === "Y")
				{
					$eventHandlerID = AddEventHandler('main', 'system.field.view.file', Array('CBlogTools', 'blogUFfileShow'));
					foreach ($arResult["POST_PROPERTIES"]["DATA"] as $FIELD_NAME => $arPostField)
					{
						if (!empty($arPostField["VALUE"]))
						{
							$arPostField['BLOG_DATE_PUBLISH'] = $arResult["Post"]["DATE_PUBLISH"];
							$arPostField['URL_TO_POST'] = $arResult["Post"]["urlToPost"];
							$arPostField['POST_ID'] = $arResult["Post"]['ID'];
							?><?php
							$APPLICATION->IncludeComponent(
								"bitrix:system.field.view",
								$arPostField["USER_TYPE"]["USER_TYPE_ID"],
								[
									"LAZYLOAD" => $arParams["LAZYLOAD"] ?? null,
									"DISABLE_LOCAL_EDIT" => $arResult["bPublicPage"],
									"VIEW_MODE" => ($arResult["bFromList"] ? "BRIEF" : "EXTENDED"),
									"arUserField" => $arPostField,
									"arAddField" => array(
										"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
										"PATH_TO_USER" => $arParams["~PATH_TO_USER"],
									),
									"GRID" => ($arResult['Post']['hasInlineDiskFile'] || !empty($arResult['Post']['BACKGROUND_CODE']) ? 'N' : 'Y'),
									"USE_TOGGLE_VIEW" => ($arResult["PostPerm"] >= 'W' ? 'Y' : 'N'),
									'NAME_TEMPLATE' => $arParams['NAME_TEMPLATE'],
									'PATH_TO_USER' => $arParams['~PATH_TO_USER'],
									'PUBLIC' => $arResult['bPublicPage'],
								],
								null,
								[ 'HIDE_ICONS' => 'Y' ]
							);
							?><?php
						}
					}
					if ($eventHandlerID > 0)
					{
						RemoveEventHandler('main', 'system.field.view.file', $eventHandlerID);
					}
				}

				if (!empty($arResult["Category"]))
				{
					?><div class="feed-com-tags-block">
						<noindex>
							<div class="feed-com-files-title"><?= Loc::getMessage("BLOG_BLOG_BLOG_CATEGORY") ?></div>
							<div class="feed-com-files-cont" id="blogpost-tags-<?= (int)$arResult["Post"]['ID'] ?>"><?php
								$i = 0;
								foreach ($arResult["Category"] as $v)
								{
									if ($i !== 0)
									{
										echo ',';
									}
									?> <a href="<?=$v["urlToCategory"]?>" rel="nofollow" class="feed-com-tag" bx-tag-value="<?=$v["NAME"]?>"><?=$v["NAME"]?></a><?php
									$i++;
								}
							?></div>
						</noindex>
					</div><?php
				}

				if (!empty($arResult["URL_PREVIEW"]))
				{
					?><?=$arResult["URL_PREVIEW"]?><?php
				}

				if ($arResult['POST_PROPERTIES']['SHOW'] === 'Y')
				{
					foreach ($arResult["POST_PROPERTIES"]["DATA"] as $FIELD_NAME => $arPostField)
					{
						if (
							!empty($arPostField["VALUE"])
							&& in_array($FIELD_NAME, $arParams["POST_PROPERTY_SOURCE"], true)
						)
						{
							echo "<div><b>".$arPostField["EDIT_FORM_LABEL"].":</b>&nbsp;";
							?><?php $APPLICATION->IncludeComponent(
								"bitrix:system.field.view",
								$arPostField["USER_TYPE"]["USER_TYPE_ID"],
								array(
									"arUserField" => $arPostField
								),
								null,
								array("HIDE_ICONS"=>"Y")
							);?><?php
							echo "</div>";
						}
					}
				}

				?><div id="blg-post-destcont-<?=$arResult["Post"]["ID"]?>"></div><?php

				if (
					empty($arParams['MODE'])
					|| $arParams['MODE'] !== 'LANDING'
				)
				{
					?><div class="feed-post-informers" id="blg-post-inform-<?=$arResult["Post"]["ID"]?>"><div class="feed-post-informers-cont"><?php

						$voteId = false;
						if ($arParams['SHOW_RATING'] === 'Y')
						{
							$voteId = 'BLOG_POST_' . $arResult['Post']['ID'] . '-' . (time() + random_int(0, 1000));
							$emotion = (!empty($arResult["RATING"][$arResult["Post"]["ID"]]["USER_REACTION"])? mb_strtoupper($arResult["RATING"][$arResult["Post"]["ID"]]["USER_REACTION"]) : 'LIKE');

							if ($arResult["bIntranetInstalled"])
							{
								$likeClassList = [
									'feed-inform-item',
									'bx-ilike-left-wrap'
								];
								if (
									isset($arResult["RATING"][$arResult["Post"]["ID"]]["USER_HAS_VOTED"])
									&& $arResult["RATING"][$arResult["Post"]["ID"]]["USER_HAS_VOTED"] === "Y"
								)
								{
									$likeClassList[] = 'bx-you-like-button';
								}
								?><span id="bx-ilike-button-<?=htmlspecialcharsbx($voteId)?>" class="feed-inform-ilike feed-new-like"><?php
									?><span class="<?= implode(' ', $likeClassList) ?>"><a href="#like" class="bx-ilike-text"><?= CRatingsComponentsMain::getRatingLikeMessage($emotion) ?></a></span><?php
								?></span><?php
							}
							else
							{
								?><span class="feed-inform-ilike"><?php
								$APPLICATION->IncludeComponent(
									"bitrix:rating.vote",
									$arParams["RATING_TYPE"],
									array(
										"ENTITY_TYPE_ID" => "BLOG_POST",
										"ENTITY_ID" => $arResult["Post"]["ID"],
										"OWNER_ID" => $arResult["Post"]["AUTHOR_ID"],
										"USER_VOTE" => $arResult["RATING"][$arResult["Post"]["ID"]]["USER_VOTE"],
										"USER_REACTION" => $arResult["RATING"][$arResult["Post"]["ID"]]["USER_REACTION"],
										"USER_HAS_VOTED" => $arResult["RATING"][$arResult["Post"]["ID"]]["USER_HAS_VOTED"],
										"TOTAL_VOTES" => $arResult["RATING"][$arResult["Post"]["ID"]]["TOTAL_VOTES"],
										"TOTAL_POSITIVE_VOTES" => $arResult["RATING"][$arResult["Post"]["ID"]]["TOTAL_POSITIVE_VOTES"],
										"TOTAL_NEGATIVE_VOTES" => $arResult["RATING"][$arResult["Post"]["ID"]]["TOTAL_NEGATIVE_VOTES"],
										"TOTAL_VALUE" => $arResult["RATING"][$arResult["Post"]["ID"]]["TOTAL_VALUE"],
										"PATH_TO_USER_PROFILE" => $arParams["~PATH_TO_USER"],
									),
									$component,
									array("HIDE_ICONS" => "Y")
								);
								?></span><?php
							}
						}

						if (
							$commentsResult
							&& (
								!isset($arResult["TYPE"])
								|| !in_array($arParams["TYPE"], array("DRAFT", "MODERATION"))
							)
						)
						{
							$caption = ($arResult['CanComment'] ? Loc::getMessage('BLOG_COMMENTS_ADD') : Loc::getMessage('BLOG_COMMENTS'));

							?><span class="feed-inform-item feed-inform-comments"><?php
								?><a href="javascript:void(0);" id="blog-post-addc-add-<?= $arResult["Post"]["ID"] ?>"><?= $caption ?></a><?php
							?></span><?php

							$allCommentCount = (int)$arResult["PostSrc"]["NUM_COMMENTS"];
							$newCommentsCount = (int) ($commentsResult['newCountWOMark'] ?? 0);

							?><div class="feed-inform-item feed-inform-comments feed-inform-comments-pinned">
								<?= Loc::getMessage('BLOG_PINNED_COMMENTS') ?>
								<span class="feed-inform-comments-pinned-all"><?= $allCommentCount ?></span>
								<span class="feed-inform-comments-pinned-old"><?= $allCommentCount - $newCommentsCount ?></span><?php

								$classNameList = [ 'feed-inform-comments-pinned-new' ];
								if ($newCommentsCount > 0)
								{
									$classNameList[] = 'feed-inform-comments-pinned-new-active';
								}
								?><span class="<?= implode(' ', $classNameList) ?>"><?php
									?><svg width="6" height="6" viewBox="0 0 6 6" fill="none" xmlns="http://www.w3.org/2000/svg"><?php
										?><path opacity="0.840937" d="M3.36051 5.73145V3.76115H5.33081V2.70174H3.36051V0.731445H2.30111V2.70174H0.330811V3.76115H2.30111V5.73145H3.36051Z" fill="white"></path><?php
									?></svg><?php
									?><span class="feed-inform-comments-pinned-new-value"><?= $newCommentsCount ?></span><?php
								?></span><?php
							?></div><?php
						}

						if (
							!$arResult["ReadOnly"]
							&& array_key_exists("FOLLOW", $arParams)
							&& $arParams["FOLLOW"] <> ''
							&& (int)$arParams["LOG_ID"] > 0
						)
						{
							?><span class="feed-inform-item feed-inform-follow" data-follow="<?= ($arParams["FOLLOW"] === "Y" ? "Y" : "N") ?>" id="log_entry_follow_<?= (int)$arParams["LOG_ID"] ?>" onclick="__blogPostSetFollow(<?= (int)$arParams["LOG_ID"] ?>)"><a href="javascript:void(0);"><?= Loc::getMessage("BLOG_POST_FOLLOW_" . ($arParams["FOLLOW"] === "Y" ? "Y" : "N")) ?></a></span><?php
						}

						if (!$arResult["bPublicPage"])
						{
							?><a
								 data-bx-post-id="<?= (int)$arResult['Post']['ID'] ?>"
								 data-bx-path-to-post="<?= htmlspecialcharsbx($arParams['PATH_TO_POST']) ?>"
								 data-bx-path-to-edit="<?= ($arResult['urlToEdit'] <> '' ? htmlspecialcharsbx($arResult['urlToEdit']) : '') ?>"
								 data-bx-path-to-hide="<?= ($arResult['urlToHide'] <> '' ? htmlspecialcharsbx($arResult['urlToHide']) : '') ?>"
								 data-bx-path-to-delete="<?= (!$arResult['bFromList'] && $arResult['urlToDelete'] <> '' ? htmlspecialcharsbx($arResult['urlToDelete']) : '') ?>"
								 data-bx-path-to-pub="<?= ($arResult['urlToPostPub'] <> '' ? htmlspecialcharsbx($arResult['urlToPostPub']) : '') ?>"
								 data-bx-public-page="<?= ($arResult['bPublicPage'] ? 'Y' : 'N') ?>"
								 data-bx-tasks-available="<?= ($arResult['bTasksAvailable'] ? 'Y' : 'N') ?>"
								 data-bx-vote-id="<?= (int)$voteId ?>"
								 data-bx-post-type="<?=htmlspecialcharsbx($arParams['TYPE'] ?? '')?>"
								 data-bx-group-read-only="<?=($arResult['ReadOnly'] ? 'Y' : 'N')?>"
								 data-bx-server-name="<?= htmlspecialcharsbx((\Bitrix\Main\Context::getCurrent()->getRequest()->isHttps() ? 'https' : 'http').'://'.((defined('SITE_SERVER_NAME') && SITE_SERVER_NAME <> '') ? SITE_SERVER_NAME : \Bitrix\Main\Config\Option::get('main', 'server_name'))) ?>"
								 data-bx-items="<?= Json::encode(!empty($arParams['ADIT_MENU']) ? $arParams['ADIT_MENU'] : []) ?>"
								 id="feed-post-menuanchor-<?=$arResult['Post']['ID']?>"
								 href="#"
								 class="feed-inform-item feed-post-more-link"><?php
								?><span class="feed-post-more-text" id="feed-post-more-<?= $arResult["Post"]["ID"] ?>"><?= Loc::getMessage("BLOG_POST_BUTTON_MORE") ?></span><?php
								?><span class="feed-post-more-arrow"></span><?php
							?></a><?php
							?><script>
							BX.bind(BX('feed-post-menuanchor-<?= $arResult["Post"]["ID"] ?>'), 'click', function(e) {
								BX.SBPostMenu.showMenu({
									event: e,
									menuNode: BX('feed-post-menuanchor-<?=$arResult["Post"]["ID"]?>'),
									context: '<?= CUtil::JSescape($arParams['CONTEXT']) ?>',
									sonetGroupId: '<?= (int) ($arParams['SONET_GROUP_ID'] ?? null) ?>',
								});
								return BX.PreventDefault(e);
							});
							</script><?php
						}

						if ($arResult['IS_COPILOT_READONLY_ENABLED'])
						{
							$postId = (int)$arResult['Post']['ID'];
							$blogPostButtonCopilotId = "blog_post_button_copilot_$postId";
							$pathToPostCreate = $arResult['PATH_TO_CREATE_NEW_POST'];
							?>

							<span id="<?= $blogPostButtonCopilotId ?>"></span>
							<script>
								BX.ready(() => new BX.Socialnetwork.Blog.Post.BlogCopilotReadonly({
									container: BX('<?= $blogPostButtonCopilotId ?>'),
									blogText: '<?= CUtil::JSEscape($arResult['Post']['DETAIL_TEXT']) ?>',
									enabledBySettings: <?= ($arResult['IS_COPILOT_READONLY_ENABLED_BY_SETTINGS'] ?? true) ? 'true' : 'false' ?>,
									copilotParams: {
										moduleId: 'socialnetwork',
										contextId: 'socialnetwork_blog_post_<?= $postId ?>',
										category: 'readonly_livefeed',
									},
									blogId: 'BLOG_<?= $postId ?>',
									pathToPostCreate: '<?= $pathToPostCreate?>',
								}));
							</script>

							<?php
						}

						?><span class="feed-inform-item feed-post-time-wrap feed-inform-contentview"><?php
							if (
								!$arResult["bPublicPage"]
								&& isset($arResult["CONTENT_ID"])
								&& (
									!isset($arResult["TYPE"])
									|| !in_array($arParams["TYPE"], array("DRAFT", "MODERATION"))
								)
							)
							{
								$APPLICATION->IncludeComponent(
									"bitrix:socialnetwork.contentview.count", "",
									[
										"CONTENT_ID" => $arResult["CONTENT_ID"],
										"CONTENT_VIEW_CNT" => ($arResult["CONTENT_VIEW_CNT"] ?? 0),
										"PATH_TO_USER_PROFILE" => $arParams["PATH_TO_USER"],
										'IS_SET' => ($arResult['contentViewIsSet'] ? 'Y' : 'N')
									],
									$component,
									[ "HIDE_ICONS" => "Y" ]
								);
							}
							?></span><?php

						if (
							$arResult["bIntranetInstalled"]
							&& $arParams['SHOW_RATING'] === 'Y'
						)
						{
							?><div class="feed-post-emoji-top-panel-outer"><?php
							?><div id="feed-post-emoji-top-panel-container-<?= htmlspecialcharsbx($voteId) ?>" class="feed-post-emoji-top-panel-box <?=( (int) ($arResult["RATING"][$arResult["Post"]["ID"]]["TOTAL_POSITIVE_VOTES"] ?? 0) > 0 ? 'feed-post-emoji-top-panel-container-active' : '') ?>"><?php
							$APPLICATION->IncludeComponent(
								"bitrix:rating.vote",
								"like_react",
								array(
									"ENTITY_TYPE_ID" => "BLOG_POST",
									"ENTITY_ID" => $arResult["Post"]["ID"],
									"OWNER_ID" => $arResult["Post"]["AUTHOR_ID"],
									"USER_VOTE" => ($arResult["RATING"][$arResult["Post"]["ID"]]["USER_VOTE"] ?? ''),
									"USER_REACTION" => ($arResult["RATING"][$arResult["Post"]["ID"]]["USER_REACTION"] ?? ''),
									"USER_HAS_VOTED" => ($arResult["RATING"][$arResult["Post"]["ID"]]["USER_HAS_VOTED"] ?? ''),
									"TOTAL_VOTES" => ($arResult["RATING"][$arResult["Post"]["ID"]]["TOTAL_VOTES"] ?? ''),
									"TOTAL_POSITIVE_VOTES" => ($arResult["RATING"][$arResult["Post"]["ID"]]["TOTAL_POSITIVE_VOTES"] ?? ''),
									"TOTAL_NEGATIVE_VOTES" => ($arResult["RATING"][$arResult["Post"]["ID"]]["TOTAL_NEGATIVE_VOTES"] ?? ''),
									"TOTAL_VALUE" => ($arResult["RATING"][$arResult["Post"]["ID"]]["TOTAL_VALUE"] ?? ''),
									"REACTIONS_LIST" => ($arResult["RATING"][$arResult["Post"]["ID"]]["REACTIONS_LIST"] ?? []),
									"PATH_TO_USER_PROFILE" => $arParams["~PATH_TO_USER"],
									'TOP_DATA' => (!empty($arResult['TOP_RATING_DATA']) ? $arResult['TOP_RATING_DATA'] : false),
									'VOTE_ID' => $voteId
								),
								$component,
								array("HIDE_ICONS" => "Y")
							);
							?></div><?php
							?></div><?php
						}
					?></div></div><?php
				}
			?></div><?php


			if (
				(
					empty($arParams['MODE'])
					|| $arParams['MODE'] !== 'LANDING'
				)
				&& (
					!isset($arResult["TYPE"])
					|| !in_array($arParams["TYPE"], [ "DRAFT", "MODERATION" ])
				)
			)
			{
				if (
					!$arResult["bPublicPage"]
					&& (
						(
							empty($_REQUEST["bxajaxid"])
							&& empty($_REQUEST["logajax"])
						)
						|| (
							($_REQUEST['RELOAD'] ?? '') === "Y"
							&& !(
								empty($_REQUEST["bxajaxid"])
								&& empty($_REQUEST["logajax"])
							)
						)
					)
				)
				{
					include_once($_SERVER["DOCUMENT_ROOT"] . $templateFolder . "/destination.php");
				}

				if ($commentsContent <> '')
				{
					?><div class="feed-comments-block-wrap"><?= $commentsContent ?></div><?php
				}
			}

			?><div class="feed-post-right-top-corner"><?php

				if ($USER->isAuthorized())
				{
					$pinned = (
						!empty($arParams['PINNED_PANEL_DATA'])
						|| (isset($arParams['PINNED']) && $arParams['PINNED'] === 'Y')
					);

					?><a href="#" class="feed-post-pinned-link feed-post-pinned-link-collapse"><?= Loc::getMessage('BLOG_POST_PINNED_COLLAPSE') ?></a><?php
					?><div id="feed-post-menuanchor-right-<?= $arResult["Post"]["ID"] ?>" class="feed-post-right-top-menu"></div><?php

					?>
					<script>
						BX.bind(BX('feed-post-menuanchor-right-<?= $arResult["Post"]["ID"] ?>'), 'click', function(e) {
							BX.SBPostMenu.showMenu({
								event: e,
								menuNode: BX('feed-post-menuanchor-<?= $arResult["Post"]["ID"] ?>'),
								context: '<?= CUtil::JSescape($arParams['CONTEXT']) ?>',
								sonetGroupId: '<?= (int) ($arParams['SONET_GROUP_ID'] ?? null) ?>',
							});
							return BX.PreventDefault(e);
						});
					</script>
					<?php

					?><div bx-data-pinned="<?=($pinned ? 'Y' : 'N')?>" class="feed-post-pin" title="<?=Loc::getMessage('SONET_EXT_LIVEFEED_PIN_TITLE_'.($pinned ? 'Y' : 'N'))?>"></div><?php
				}

			?></div><?php

		?></div><?php
	}
	elseif (!$arResult["bFromList"])
	{
		echo GetMessage("BLOG_BLOG_BLOG_NO_AVAIBLE_MES");
	}
}
?></div><?php // feed-item-wrap
