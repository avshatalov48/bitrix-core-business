<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
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

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\UI;

$APPLICATION->SetAdditionalCSS('/bitrix/components/bitrix/socialnetwork.log.ex/templates/.default/style.css');
$APPLICATION->SetAdditionalCSS('/bitrix/components/bitrix/socialnetwork.blog.blog/templates/.default/style.css');
if (!$arResult["bFromList"])
{
	$APPLICATION->AddHeadScript("/bitrix/components/bitrix/socialnetwork.log.ex/templates/.default/script.js");
}

$ajax_page = $APPLICATION->GetCurPageParam("", array("logajax", "bxajaxid", "logout"));
$voteId = false;

$extensions = [ 'ajax', 'viewer', 'popup', 'clipboard' ];
if ($arResult["bTasksAvailable"])
{
	$extensions[] = 'tasks_util_base';
	$extensions[] = 'tasks_util_query';
}
CJSCore::Init($extensions);

UI\Extension::load([
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

$bodyClass = $APPLICATION->GetPageProperty("BodyClass");
$bodyClass = $bodyClass ? $bodyClass." no-all-paddings" : "no-all-paddings";
$APPLICATION->SetPageProperty("BodyClass", $bodyClass);

?><div class="feed-item-wrap" data-livefeed-id="<?=(int)$arParams['LOG_ID']?>"><?

?><script>
	BX.message({
		BLOG_POST_LINK_COPIED: '<?=GetMessageJS("BLOG_POST_LINK_COPIED")?>',
		BLOG_HREF: '<?=GetMessageJS("BLOG_HREF")?>',
		BLOG_LINK: '<?=GetMessageJS("BLOG_LINK2")?>',
		BLOG_SHARE: '<?=GetMessageJS("BLOG_SHARE")?>',
		BLOG_BLOG_BLOG_EDIT: '<?=GetMessageJS("BLOG_BLOG_BLOG_EDIT")?>',
		BLOG_BLOG_BLOG_DELETE: '<?=GetMessageJS("BLOG_BLOG_BLOG_DELETE")?>',
		BLOG_MES_DELETE_POST_CONFIRM: '<?=GetMessageJS("BLOG_MES_DELETE_POST_CONFIRM")?>',
		BLOG_POST_CREATE_TASK: '<?=GetMessageJS("BLOG_POST_CREATE_TASK")?>',
		BLOG_POST_VOTE_EXPORT: '<?=GetMessageJS("BLOG_POST_VOTE_EXPORT")?>',
		BLOG_POST_MOD_PUB: '<?=GetMessageJS("BLOG_POST_MOD_PUB")?>',
		BLOG_MES_HIDE: '<?=GetMessageJS("BLOG_MES_HIDE")?>',
		BLOG_MES_HIDE_POST_CONFIRM: '<?=GetMessageJS("BLOG_MES_HIDE_POST_CONFIRM")?>',
		sonetBPDeletePath: '<?=CUtil::JSEscape($arResult["urlToDelete"])?>'
		<?
		if (!$arResult["bFromList"])
		{
			?>,
			sonetLESetPath: '<?=CUtil::JSEscape('/bitrix/components/bitrix/socialnetwork.log.entry/ajax.php')?>',
			sonetLSessid: '<?=bitrix_sessid_get()?>'
			<?
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
</script><?

if($arResult["MESSAGE"] <> '')
{
	?><div class="feed-add-successfully">
		<span class="feed-add-info-text"><span class="feed-add-info-icon"></span><?=$arResult["MESSAGE"]?></span>
	</div><?
}
if($arResult["ERROR_MESSAGE"] <> '')
{
	?><div class="feed-add-error">
		<span class="feed-add-info-text"><span class="feed-add-info-icon"></span><?=$arResult["ERROR_MESSAGE"]?></span>
	</div><?
}
if($arResult["FATAL_MESSAGE"] <> '')
{
	if(!$arResult["bFromList"])
	{
		?><div class="feed-add-error">
			<span class="feed-add-info-text"><span class="feed-add-info-icon"></span><?=$arResult["FATAL_MESSAGE"]?></span>
		</div><?
	}
}
elseif($arResult["NOTE_MESSAGE"] <> '')
{
	?><div class="feed-add-successfully">
		<span class="feed-add-info-text"><span class="feed-add-info-icon"></span><?=$arResult["NOTE_MESSAGE"]?></span>
	</div><?
}
else
{
	if(!empty($arResult["Post"]))
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

		if($arResult["Post"]["new"] === "Y")
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
			&& !in_array($arParams['TYPE'], [ 'DRAFT', 'MODERATION' ])
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
			</script><?
		}

		?><script>
			BX.viewElementBind(
				'blg-post-img-<?=$arResult["Post"]["ID"]?>',
				{showTitle: true},
				function(node){
					return BX.type.isElementNode(node) && (node.getAttribute('data-bx-viewer') || node.getAttribute('data-bx-image'));
				}
			);

			var postDest<?=$arResult["Post"]["ID"]?> = <?=\CUtil::phpToJSObject($arResult['postDestEntities'], false, false, true)?>;

			BX.ready(function () {
				if (
					(
						typeof oLF == 'undefined'
						|| !oLF.filterApi
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
		</script><?

		$commentsContent = '';

		if (
			!in_array($arParams["TYPE"], array("DRAFT", "MODERATION"))
			&& ($arResult["CommentPerm"] >= BLOG_PERMS_READ)
			&& (
				empty($arParams['MODE'])
				|| $arParams['MODE'] !== 'LANDING'
			)
		)
		{
			ob_start();

			$commentsResult = $APPLICATION->IncludeComponent(
				"bitrix:socialnetwork.blog.post.comment",
				"",
				array(
					"bPublicPage" => $arResult["bPublicPage"],
					"SEF" => $arParams["SEF"],
					"BLOG_VAR" => $arResult["ALIASES"]["blog"],
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
					"COMMENTS_COUNT" => $arParams["COMMENTS_COUNT"],
					"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT"],
					"DATE_TIME_FORMAT_WITHOUT_YEAR" => $arParams["DATE_TIME_FORMAT_WITHOUT_YEAR"],
					"TIME_FORMAT" => $arParams["TIME_FORMAT"],
					"USE_ASC_PAGING" => $arParams["USE_ASC_PAGING"],
					"USER_ID" => $arResult["USER_ID"],
					"GROUP_ID" => $arParams["GROUP_ID"],
					"SONET_GROUP_ID" => $arParams["SONET_GROUP_ID"],
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
					"ALLOW_VIDEO" => $arParams["ALLOW_VIDEO"],
					"ALLOW_IMAGE_UPLOAD" => $arParams["ALLOW_IMAGE_UPLOAD"],
					"SHOW_SPAM" => $arParams["BLOG_SHOW_SPAM"],
					"NO_URL_IN_COMMENTS" => $arParams["BLOG_NO_URL_IN_COMMENTS"],
					"NO_URL_IN_COMMENTS_AUTHORITY" => $arParams["BLOG_NO_URL_IN_COMMENTS_AUTHORITY"],
					"ALLOW_POST_CODE" => $arParams["BLOG_ALLOW_POST_CODE"],
					"AJAX_POST" => "Y",
					"POST_DATA" => $arResult["PostSrc"],
					"BLOG_DATA" => $arResult["Blog"],
					"FROM_LOG" => $arParams["FROM_LOG"],
					"bFromList" => $arResult["bFromList"],
					"LAST_LOG_TS" => $arParams["LAST_LOG_TS"],
					"MARK_NEW_COMMENTS" => $arParams["MARK_NEW_COMMENTS"],
					"AVATAR_SIZE" => $arParams["AVATAR_SIZE"],
					"AVATAR_SIZE_COMMON" => $arParams["AVATAR_SIZE_COMMON"],
					"AVATAR_SIZE_COMMENT" => $arParams["AVATAR_SIZE_COMMENT"],
					"FOLLOW" => $arParams["FOLLOW"],
					"LOG_ID" => intval($arParams["LOG_ID"]),
					"LOG_CONTENT_ITEM_TYPE" => (!empty($arParams['LOG_CONTENT_ITEM_ID']) ? $arParams['LOG_CONTENT_ITEM_TYPE'] : ''),
					"LOG_CONTENT_ITEM_ID" => (!empty($arParams['LOG_CONTENT_ITEM_ID']) ? intval($arParams['LOG_CONTENT_ITEM_ID']) : 0),
					"CREATED_BY_ID" => $arParams["CREATED_BY_ID"],
					"MOBILE" => $arParams["MOBILE"],
					"LAZYLOAD" => $arParams["LAZYLOAD"],
					"CAN_USER_COMMENT" => (!isset($arResult["CanComment"]) || $arResult["CanComment"] ? 'Y' : 'N'),
					"NAV_TYPE_NEW" => "Y",
					"SELECTOR_VERSION" => $arResult["SELECTOR_VERSION"]
				),
				$component
			);

			$commentsContent = ob_get_clean();
		}

		?><div
			 class="<?=implode(' ', $classNameList)?>"
			 id="blg-post-<?=$arResult["Post"]["ID"]?>"
			 data-livefeed-id="<?=(int)$arParams['LOG_ID']?>"
			 data-menu-id="blog-post-<?=(int)$arResult["Post"]["ID"]?>"
			<?
			if (isset($pinned))
			{
				?>
				 data-livefeed-post-pinned="<?=($pinned ? 'Y' : 'N')?>"
				<?
			}
			?>><a name="post<?=$arResult["Post"]["ID"]?>"></a><?
			$aditStylesList = [ 'feed-post-cont-wrap' ];

			if ($arResult["Post"]["hidden"] === 'Y')
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

			$style = ($avatar ? "background: url('".\CHTTP::urnEncode($avatar)."'); background-size: cover;" : "");

			?><div class="<?=implode(' ', $aditStylesList)?>" id="blg-post-img-<?=$arResult["Post"]["ID"]?>">
				<div class="ui-icon ui-icon-common-user feed-user-avatar"><i style="<?=$style?>"></i></div><?
				?><div class="feed-post-pinned-block"><?

					?><div class="feed-post-pinned-title"><?
						if (
							!empty($arParams['PINNED_PANEL_DATA'])
							&& $arParams['PINNED_PANEL_DATA']['TITLE'] <> ''
						)
						{
							?><?=$arParams['PINNED_PANEL_DATA']['TITLE']?><?
						}
					?></div><?

					?><div class="feed-post-pinned-text-box"><?
						?><div class="feed-post-pinned-desc"><?
							if (
								!empty($arParams['PINNED_PANEL_DATA'])
								&& $arParams['PINNED_PANEL_DATA']['DESCRIPTION'] <> ''
							)
							{
								?><?=$arParams['PINNED_PANEL_DATA']['DESCRIPTION']?><?
							}
						?></div><?
						?><a href="#" class="feed-post-pinned-link feed-post-pinned-link-expand"><?=Loc::getMessage('BLOG_POST_PINNED_EXPAND')?></a><?
					?></div><?
				?></div><?
				?><div class="feed-post-title-block"><?
					$anchor_id = $arResult["Post"]["ID"];
					$arTooltipParams = (
						$arResult["bPublicPage"]
						? array(
							'entityType' => 'LOG_ENTRY',
							'entityId' => intval($arParams['LOG_ID'])
						)
						: array()
					);

					$arTmpUser = array(
						"NAME" => $arResult["arUser"]["~NAME"],
						"LAST_NAME" => $arResult["arUser"]["~LAST_NAME"],
						"SECOND_NAME" => $arResult["arUser"]["~SECOND_NAME"],
						"LOGIN" => $arResult["arUser"]["~LOGIN"],
						"NAME_LIST_FORMATTED" => "",
					);

					if ($arParams["SEO_USER"] == "Y")
					{
						?><noindex><?
					}

					if ($arResult["bPublicPage"])
					{
						?><span
							class="feed-post-user-name<?=(array_key_exists("isExtranet", $arResult["arUser"]) && $arResult["arUser"]["isExtranet"] ? " feed-post-user-name-extranet" : "")?>"
							id="bp_<?=$anchor_id?>"
							bx-post-author-id="<?=$arResult["arUser"]["ID"]?>"
							bx-post-author-gender="<?=$arResult["arUser"]["PERSONAL_GENDER"]?>"
							bx-tooltip-user-id="<?=$arResult["arUser"]["ID"]?>"
							bx-tooltip-params="<?=htmlspecialcharsbx(\Bitrix\Main\Web\Json::encode($arTooltipParams))?>"
						><?=CUser::FormatName($arParams["NAME_TEMPLATE"], $arTmpUser, ($arParams["SHOW_LOGIN"] != "N" ? true : false))?></span><?
					}
					else
					{
						?><a
							class="feed-post-user-name<?=(array_key_exists("isExtranet", $arResult["arUser"]) && $arResult["arUser"]["isExtranet"] ? " feed-post-user-name-extranet" : "")?>"
							id="bp_<?=$anchor_id?>" href="<?=$arResult["arUser"]["url"]?>"
							bx-post-author-id="<?=$arResult["arUser"]["ID"]?>"
							bx-post-author-gender="<?=$arResult["arUser"]["PERSONAL_GENDER"]?>"
							bx-tooltip-user-id="<?=$arResult["arUser"]["ID"]?>"
							bx-tooltip-params="<?=htmlspecialcharsbx(\Bitrix\Main\Web\Json::encode($arTooltipParams))?>"
						><?=CUser::FormatName($arParams["NAME_TEMPLATE"], $arTmpUser, ($arParams["SHOW_LOGIN"] != "N" ? true : false))?></a><?
					}

					if ($arParams["SEO_USER"] == "Y")
					{
						?></noindex><?
					}

					if (
						!empty($arResult["Post"]["SPERM_SHOW"])
						&& (
							empty($arParams['MODE'])
							|| $arParams['MODE'] != 'LANDING'
						)
					)
					{
						?><span class="feed-add-post-destination-cont<?=($arResult["Post"]["LIMITED_VIEW"] ? ' feed-add-post-destination-limited-view' : '')?>"><?

						?><span class="feed-add-post-destination-icon"><span style="position: absolute; left: -3000px; overflow: hidden;">&nbsp;-&gt;&nbsp;</span></span><?

						$cnt = (
							(!empty($arResult["Post"]["SPERM_SHOW"]["U"]) ? count($arResult["Post"]["SPERM_SHOW"]["U"]) : 0) +
							(!empty($arResult["Post"]["SPERM_SHOW"]["SG"]) ? count($arResult["Post"]["SPERM_SHOW"]["SG"]) : 0) +
							(!empty($arResult["Post"]["SPERM_SHOW"]["DR"]) ? count($arResult["Post"]["SPERM_SHOW"]["DR"]) : 0) +
							(!empty($arResult["Post"]["SPERM_SHOW"]["CRMCONTACT"]) ? count($arResult["Post"]["SPERM_SHOW"]["CRMCONTACT"]) : 0)
						);

						$showMaxCount = ($cnt > 4 ? 4 : 5);

						$i = 0;
						if(!empty($arResult["Post"]["SPERM_SHOW"]["U"]))
						{
							foreach($arResult["Post"]["SPERM_SHOW"]["U"] as $id => $val)
							{
								$i++;
								if($i == $showMaxCount)
								{
									$more_cnt = $cnt + intval($arResult["Post"]["SPERM_HIDDEN"]) - ($showMaxCount - 1);
									$suffix = (
										($more_cnt % 100) > 10
										&& ($more_cnt % 100) < 20
											? 5
											: $more_cnt % 10
									);

									?><a href="javascript:void(0)" onclick="showHiddenDestination('<?=$arResult["Post"]["ID"]?>', this)" class="feed-post-link-new"><?=GetMessage("BLOG_DESTINATION_MORE_".$suffix, Array("#NUM#" => $more_cnt))?></a><span id="blog-destination-hidden-<?=$arResult["Post"]["ID"]?>" style="display:none;"><?
								}
								if($i != 1)
								{
									echo ", ";
								}
								if($val["NAME"] != "All")
								{
									$anchor_id = $arResult["Post"]["ID"]."_".$id;
									$classNameList = [ 'feed-add-post-destination-new' ];
									$arTooltipParams = array();

									if (
										array_key_exists("IS_EXTRANET", $val)
										&& $val["IS_EXTRANET"] == "Y"
									)
									{
										$classNameList[] = 'feed-add-post-destination-new-extranet';
									}
									elseif ($val["IS_EMAIL"] == "Y")
									{
										$classNameList[] = 'feed-add-post-destination-new-email';
										$arTooltipParams = array(
											'entityType' => 'LOG_ENTRY',
											'entityId' => intval($arParams['LOG_ID'])
										);
									}

									if ($arResult["bPublicPage"])
									{
										?><span
										 id="dest_<?=$anchor_id?>"
										 class="<?=implode(' ', $classNameList)?>"
										 bx-tooltip-user-id="<?=$val["ID"]?>"
										 bx-tooltip-params="<?=htmlspecialcharsbx(\Bitrix\Main\Web\Json::encode($arTooltipParams))?>"
										><?=$val["NAME"]?></span><?
									}
									else
									{
										?><a
										 id="dest_<?=$anchor_id?>"
										 href="<?=$val["URL"]?>"
										 class="<?=implode(' ', $classNameList)?>"
										 bx-tooltip-user-id="<?=$val["ID"]?>"
										 bx-tooltip-params="<?=htmlspecialcharsbx(\Bitrix\Main\Web\Json::encode($arTooltipParams))?>"
										><?=$val["NAME"]?></a><?
									}
								}
								else
								{
									if (
										$val["URL"] <> ''
										&& !$arResult["bPublicPage"]
									)
									{
										?><a href="<?=$val["URL"]?>" class="feed-add-post-destination-new"><?=($arResult["bIntranetInstalled"] ? GetMessage("BLOG_DESTINATION_ALL") : GetMessage("BLOG_DESTINATION_ALL_BSM"))?></a><?
									}
									else
									{
										?><span class="feed-add-post-destination-new"><?=($arResult["bIntranetInstalled"] ? GetMessage("BLOG_DESTINATION_ALL") : GetMessage("BLOG_DESTINATION_ALL_BSM"))?></span><?
									}
								}
							}
						}
						if(!empty($arResult["Post"]["SPERM_SHOW"]["SG"]))
						{
							foreach($arResult["Post"]["SPERM_SHOW"]["SG"] as $id => $val)
							{
								$i++;
								if($i == $showMaxCount)
								{
									$more_cnt = $cnt + intval($arResult["Post"]["SPERM_HIDDEN"]) - ($showMaxCount - 1);
									if (
										($more_cnt % 100) > 10
										&& ($more_cnt % 100) < 20
									)
										$suffix = 5;
									else
										$suffix = $more_cnt % 10;

									?><a href="javascript:void(0)" onclick="showHiddenDestination('<?=$arResult["Post"]["ID"]?>', this)" class="feed-post-link-new"><?=GetMessage("BLOG_DESTINATION_MORE_".$suffix, Array("#NUM#" => $more_cnt))?></a><span id="blog-destination-hidden-<?=$arResult["Post"]["ID"]?>" style="display:none;"><?
								}
								if($i != 1)
								{
									echo ", ";
								}
								if (
									$val["URL"] <> ''
									&& !$arResult["bPublicPage"]
								)
								{
									?><a href="<?=$val["URL"]?>" class="feed-add-post-destination-new<?=(array_key_exists("IS_EXTRANET", $val) && $val["IS_EXTRANET"] == "Y" ? " feed-add-post-destination-new-extranet" : "")?>" target="_top"><?=$val["NAME"]?></a><?
								}
								else
								{
									?><span class="feed-add-post-destination-new"><?=$val["NAME"]?></span><?
								}
							}
						}
						if(!empty($arResult["Post"]["SPERM_SHOW"]["DR"]))
						{
							foreach($arResult["Post"]["SPERM_SHOW"]["DR"] as $id => $val)
							{
								$i++;
								if($i == $showMaxCount)
								{
									$more_cnt = $cnt + intval($arResult["Post"]["SPERM_HIDDEN"]) - ($showMaxCount - 1);
									if (
										($more_cnt % 100) > 10
										&& ($more_cnt % 100) < 20
									)
										$suffix = 5;
									else
										$suffix = $more_cnt % 10;

									?><a href="javascript:void(0)" onclick="showHiddenDestination('<?=$arResult["Post"]["ID"]?>', this)" class="feed-post-link-new"><?=GetMessage("BLOG_DESTINATION_MORE_".$suffix, Array("#NUM#" => $more_cnt))?></a><span id="blog-destination-hidden-<?=$arResult["Post"]["ID"]?>" style="display:none;"><?
								}
								if($i != 1)
									echo ", ";

								if (
									$val["URL"] <> ''
									&& !$arResult["bExtranetSite"]
								)
								{
									?><a href="<?=$val["URL"]?>" class="feed-add-post-destination-new" target="_top"><?=$val["NAME"]?></a><?
								}
								else
								{
									?><span class="feed-add-post-destination-new"><?=$val["NAME"]?></span><?
								}
							}
						}

						if(!empty($arResult["Post"]["SPERM_SHOW"]["CRMCONTACT"]))
						{
							foreach($arResult["Post"]["SPERM_SHOW"]["CRMCONTACT"] as $id => $val)
							{
								$i++;
								if($i == $showMaxCount)
								{
									$more_cnt = $cnt + intval($arResult["Post"]["SPERM_HIDDEN"]) - ($showMaxCount - 1);
									$suffix = (
										($more_cnt % 100) > 10
										&& ($more_cnt % 100) < 20
											? 5
											: $more_cnt % 10
									);

									?><a href="javascript:void(0)" onclick="showHiddenDestination('<?=$arResult["Post"]["ID"]?>', this)" class="feed-post-link-new"><?=GetMessage("BLOG_DESTINATION_MORE_".$suffix, Array("#NUM#" => $more_cnt))?></a><span id="blog-destination-hidden-<?=$arResult["Post"]["ID"]?>" style="display:none;"><?
								}
								?><?=($i != 1 ? ", " : "")?><?
								if (!empty($val["CRM_PREFIX"]))
								{
									$classPrefixAdditional = (
										!$arResult["bPublicPage"]
										&& array_key_exists("CRM_USER_ID", $val)
										&& intval($val["CRM_USER_ID"]) > 0
											? " feed-add-post-destination-prefix-crmuser"
											: ""
									);
									?><span class="feed-add-post-destination-prefix<?=$classPrefixAdditional?>"><?=$val["CRM_PREFIX"]?>:&nbsp;</span><?
								}
								if (
									$val["URL"] <> ''
									&& !$arResult["bPublicPage"]
								)
								{
									?><a href="<?=$val["URL"]?>" class="feed-add-post-destination-new" target="_top"><?=$val["NAME"]?></a><?
								}
								else
								{
									?><span class="feed-add-post-destination-new"><?=$val["NAME"]?></span><?
								}
							}
						}

						if (
							isset($arResult["Post"]["SPERM_HIDDEN"])
							&& intval($arResult["Post"]["SPERM_HIDDEN"]) > 0
						)
						{
							if (
								($arResult["Post"]["SPERM_HIDDEN"] % 100) > 10
								&& ($arResult["Post"]["SPERM_HIDDEN"] % 100) < 20
							)
								$suffix = 5;
							else
								$suffix = $arResult["Post"]["SPERM_HIDDEN"] % 10;

							?><span class="feed-add-post-destination-new">&nbsp;<?=GetMessage("BLOG_DESTINATION_HIDDEN_".$suffix, Array("#NUM#" => intval($arResult["Post"]["SPERM_HIDDEN"])))?></span><?
						}

						if ($i > ($showMaxCount - 1))
						{
							echo "</span>";
						}

						if ($arResult["Post"]["LIMITED_VIEW"])
						{
							?><span class="feed-add-post-destination-new feed-add-post-destination-limited-view"><?=Loc::getMessage('BLOG_POST_LIMITED_VIEW')?></span><?
						}
						?></span><? // feed-add-post-destination-cont
					}

					if(
						(
							empty($arParams['MODE'])
							|| $arParams['MODE'] !== 'LANDING'
						)
						&& $arResult["urlToEdit"] <> ''
						&& (
							$arResult["PostPerm"] >= BLOG_PERMS_FULL
							|| (
								$arResult["PostPerm"] >= BLOG_PERMS_WRITE
								&& $arResult["Post"]["AUTHOR_ID"] == $arResult["USER_ID"]
							)
						)
					)
					{
						if (!empty($arResult['Post']['BACKGROUND_CODE']))
						{
							$editHref = '#';
							$onClick = "return BX.Livefeed.PostInstance.showBackgroundWarning({
								urlToEdit: '".$arResult["urlToEdit"]."'
							});";
						}
						else
						{
							$editHref = $arResult["urlToEdit"];
							$onClick = '';
						}

						?><a href="<?=$editHref?>" onclick="<?=$onClick?>" title="<?=Loc::getMessage("BLOG_BLOG_BLOG_EDIT")?>" target="_top"><?
							?><span class="feed-destination-edit" onclick="BX.addClass(this, 'feed-destination-edit-pressed');"></span><?
						?></a><?
					}

					$datetime_detail = CComponentUtil::GetDateTimeFormatted(array(
						'TIMESTAMP' => MakeTimeStamp($arResult["Post"]["DATE_PUBLISH"]),
						'DATETIME_FORMAT' => $arParams["DATE_TIME_FORMAT"],
						'DATETIME_FORMAT_WITHOUT_YEAR' => (isset($arParams["DATE_TIME_FORMAT_WITHOUT_YEAR"]) ? $arParams["DATE_TIME_FORMAT_WITHOUT_YEAR"] : false),
						'TZ_OFFSET' => $arResult["TZ_OFFSET"]
					));

					?><div class="feed-post-time-wrap"><?
						if ($arResult["bPublicPage"])
						{
							?><div class="feed-time"><?=$datetime_detail?></div><?
						}
						else
						{
							?><a href="<?=$arResult["Post"]["urlToPost"]?>" target="_top"><div class="feed-time"><?=$datetime_detail?></div></a><?
						}
					?></div><?

				?></div><? // feed-post-title-block

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
					(
						$arResult["Post"]["textFormated"] === ''
						|| mb_strtolower($arResult["Post"]["textFormated"]) === '<b></b>' // empty text patch for attached diles
					)
					&& !$arResult["Post"]["IS_IMPORTANT"]
				)
				{
					$classList[] = 'feed-info-block-empty';
				}
				
				?><div class="<?=(implode(' ', $classList))?>" id="blog_post_outer_<?=$arResult["Post"]["ID"]?>"><?

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

					?><div class="<?=implode(' ', $classNameList)?>"<?if($arResult["bFromList"]) {?> id="feed-post-contentview-BLOG_POST-<?=(int)$arResult["Post"]["ID"]?>" bx-content-view-xml-id="BLOG_POST-<?=(int)$arResult["Post"]["ID"]?>"<? }?>>
						<div class="feed-post-text-block-inner-inner" id="blog_post_body_<?=$arResult["Post"]["ID"]?>"><?

							if ($arResult["Post"]["IS_IMPORTANT"])
							{
								?><div class="feed-important-icon"></div><?
							}

							if($arResult["Post"]["MICRO"] !== "Y")
							{
								?><div class="feed-post-item feed-post-item-title"><?
									if ($arResult["bPublicPage"])
									{
										?><span class="feed-post-title"><?=$arResult["Post"]["TITLE"]?></span><?
									}
									else
									{
										?><a class="feed-post-title" href="<?=$arResult["Post"]["urlToPost"]?>" target="_top"><?=$arResult["Post"]["TITLE"]?></a><?
									}
								?></div><?
							}

							?><div class="feed-post-text"><?=$arResult["Post"]["textFormated"]?></div><?

							if ($arResult["Post"]["CUT"] === "Y")
							{
								?><div><a class="blog-postmore-link" href="<?=$arResult["Post"]["urlToPost"]?>"><?=GetMessage("BLOG_BLOG_BLOG_MORE")?></a></div><?
							}

							require($_SERVER["DOCUMENT_ROOT"].$templateFolder."/include/important.php");
							require($_SERVER["DOCUMENT_ROOT"].$templateFolder."/include/gratitude.php");

						?></div>
					</div><?

					if($arResult["bFromList"])
					{
						?><div class="feed-post-text-more" onclick="BX.UI.Animations.expand({
							moreButtonNode: this,
							type: 'post',
							classBlock: 'feed-post-text-block',
							classOuter: 'feed-post-text-block-inner',
							classInner: 'feed-post-text-block-inner-inner',
							heightLimit: 300,
							callback: function(textBlock) { if (typeof oLF != 'undefined') { oLF.expandPost(textBlock); } }
						})" id="blog_post_more_<?=$arResult["Post"]["ID"]?>"><?
						?><div class="feed-post-text-more-but"></div><?
						?></div><?
						?><script>
							BX.ready(function() {
								if (
									typeof oLF != 'undefined'
									&& BX.type.isNotEmptyObject(oLF)
									&& BX.type.isArray(oLF.arMoreButtonID)
								)
								{
									oLF.arMoreButtonID.push({
										outerBlockID : 'blog_post_outer_<?=$arResult["Post"]["ID"]?>',
										bodyBlockID : 'blog_post_body_<?=$arResult["Post"]["ID"]?>',
										moreButtonBlockID : 'blog_post_more_<?=$arResult["Post"]["ID"]?>',
										informerBlockID : 'blg-post-inform-<?=intval($arResult["Post"]["ID"])?>'
									});
								}
							});
						</script><?
					}
				?></div><?

				if(!empty($arResult["images"]))
				{
					?><div class="feed-com-files">
						<div class="feed-com-files-title"><?=GetMessage("BLOG_PHOTO")?></div>
						<div class="feed-com-files-cont"><?
							foreach($arResult["images"] as $val)
							{
								?><span class="feed-com-files-photo"><img src="<?=$val["small"]?>" alt="" border="0" data-bx-image="<?=$val["full"]?>" /></span><?
							}
						?></div>
					</div><?
				}

				if($arResult["POST_PROPERTIES"]["SHOW"] === "Y")
				{
					$eventHandlerID = AddEventHandler('main', 'system.field.view.file', Array('CBlogTools', 'blogUFfileShow'));
					foreach ($arResult["POST_PROPERTIES"]["DATA"] as $FIELD_NAME => $arPostField)
					{
						if(!empty($arPostField["VALUE"]))
						{

							$arPostField['BLOG_DATE_PUBLISH'] = $arResult["Post"]["DATE_PUBLISH"];
							$arPostField['URL_TO_POST'] = $arResult["Post"]["urlToPost"];
							$arPostField['POST_ID'] = $arResult["Post"]['ID'];
							?><?$APPLICATION->IncludeComponent(
								"bitrix:system.field.view",
								$arPostField["USER_TYPE"]["USER_TYPE_ID"],
								array(
									"LAZYLOAD" => $arParams["LAZYLOAD"],
									"DISABLE_LOCAL_EDIT" => $arResult["bPublicPage"],
									"VIEW_MODE" => ($arResult["bFromList"] ? "BRIEF" : "EXTENDED"),
									"arUserField" => $arPostField,
									"arAddField" => array(
										"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"], 
										"PATH_TO_USER" => $arParams["~PATH_TO_USER"],
									),
									"GRID" => ($arResult['Post']['hasInlineDiskFile'] || !empty($arResult['Post']['BACKGROUND_CODE']) ? 'N' : 'Y'),
									"USE_TOGGLE_VIEW" => ($arResult["PostPerm"] >= 'W' ? 'Y' : 'N'),
								), null, array("HIDE_ICONS"=>"Y")
							);?><?
						}
					}
					if ($eventHandlerID !== false && ( intval($eventHandlerID) > 0 ))
						RemoveEventHandler('main', 'system.field.view.file', $eventHandlerID);
				}

				if(!empty($arResult["Category"]))
				{
					?><div class="feed-com-tags-block">
						<noindex>
							<div class="feed-com-files-title"><?=GetMessage("BLOG_BLOG_BLOG_CATEGORY")?></div>
							<div class="feed-com-files-cont" id="blogpost-tags-<?=intval($arResult["Post"]['ID'])?>"><?
								$i=0;
								foreach($arResult["Category"] as $v)
								{
									if($i!=0)
										echo ",";
									?> <a href="<?=$v["urlToCategory"]?>" rel="nofollow" class="feed-com-tag" bx-tag-value="<?=$v["NAME"]?>"><?=$v["NAME"]?></a><?
									$i++;
								}
							?></div>
						</noindex>
					</div><?
				}

				if (!empty($arResult["URL_PREVIEW"]))
				{
					?><?=$arResult["URL_PREVIEW"]?><?
				}

				if($arResult["POST_PROPERTIES"]["SHOW"] == "Y")
				{
					foreach ($arResult["POST_PROPERTIES"]["DATA"] as $FIELD_NAME => $arPostField)
					{
						if(in_array($FIELD_NAME, $arParams["POST_PROPERTY_SOURCE"]) && !empty($arPostField["VALUE"]))
						{
							echo "<div><b>".$arPostField["EDIT_FORM_LABEL"].":</b>&nbsp;";
							?><?$APPLICATION->IncludeComponent(
								"bitrix:system.field.view",
								$arPostField["USER_TYPE"]["USER_TYPE_ID"],
								array(
									"arUserField" => $arPostField
								),
								null, 
								array("HIDE_ICONS"=>"Y")
							);?><?
							echo "</div>";
						}
					}
				}

				?><div id="blg-post-destcont-<?=$arResult["Post"]["ID"]?>"></div><?

				if (
					empty($arParams['MODE'])
					|| $arParams['MODE'] !== 'LANDING'
				)
				{
					?><div class="feed-post-informers" id="blg-post-inform-<?=$arResult["Post"]["ID"]?>"><div class="feed-post-informers-cont"><?

						$voteId = false;
						if ($arParams["SHOW_RATING"] == "Y")
						{
							$voteId = "BLOG_POST".'_'.$arResult["Post"]["ID"].'-'.(time()+rand(0, 1000));
							$emotion = (!empty($arResult["RATING"][$arResult["Post"]["ID"]]["USER_REACTION"])? mb_strtoupper($arResult["RATING"][$arResult["Post"]["ID"]]["USER_REACTION"]) : 'LIKE');

							if ($arResult["bIntranetInstalled"])
							{
								$likeClassList = [
									'feed-inform-item',
									'bx-ilike-left-wrap'
								];
								if (
										isset($arResult["RATING"])
										&& isset($arResult["RATING"][$arResult["Post"]["ID"]])
										&& isset($arResult["RATING"][$arResult["Post"]["ID"]]["USER_HAS_VOTED"])
										&& $arResult["RATING"][$arResult["Post"]["ID"]]["USER_HAS_VOTED"] === "Y"
								)
								{
									$likeClassList[] = 'bx-you-like-button';
								}
								?><span id="bx-ilike-button-<?=htmlspecialcharsbx($voteId)?>" class="feed-inform-ilike feed-new-like"><?
									?><span class="<?=implode(' ', $likeClassList)?>"><a href="#like" class="bx-ilike-text"><?=\CRatingsComponentsMain::getRatingLikeMessage($emotion)?></a></span><?
								?></span><?
							}
							else
							{
								?><span class="feed-inform-ilike"><?
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
								?></span><?
							}
						}

						if(
							$commentsResult
							&& !in_array($arParams["TYPE"], array("DRAFT", "MODERATION"))
						)
						{
							?><span class="feed-inform-item feed-inform-comments"><?
								?><a href="javascript:void(0);" id="blog-post-addc-add-<?=$arResult["Post"]["ID"]?>"><?=GetMessage("BLOG_COMMENTS_ADD")?></a><?
							?></span><?

							$allCommentCount = (int)$arResult["PostSrc"]["NUM_COMMENTS"];
							$newCommentsCount = (int)$commentsResult['newCountWOMark'];

							?><div class="feed-inform-item feed-inform-comments feed-inform-comments-pinned">
								<?=Loc::getMessage('BLOG_PINNED_COMMENTS')?>
								<span class="feed-inform-comments-pinned-all"><?=$allCommentCount?></span>
								<span class="feed-inform-comments-pinned-old"><?=$allCommentCount-$newCommentsCount?></span><?
								$classList = [ 'feed-inform-comments-pinned-new' ];
								if ($newCommentsCount > 0)
								{
									$classList[] = 'feed-inform-comments-pinned-new-active';
								}
								?><span class="<?=implode(' ', $classList)?>"><?
									?><svg width="6" height="6" viewBox="0 0 6 6" fill="none" xmlns="http://www.w3.org/2000/svg"><?
										?><path opacity="0.840937" d="M3.36051 5.73145V3.76115H5.33081V2.70174H3.36051V0.731445H2.30111V2.70174H0.330811V3.76115H2.30111V5.73145H3.36051Z" fill="white"></path><?
									?></svg><?
									?><span class="feed-inform-comments-pinned-new-value"><?=$newCommentsCount?></span><?
								?></span><?
							?></div><?
						}

						if (
							!$arResult["ReadOnly"]
							&& array_key_exists("FOLLOW", $arParams)
							&& $arParams["FOLLOW"] <> ''
							&& (int)$arParams["LOG_ID"] > 0
						)
						{
							?><span class="feed-inform-item feed-inform-follow" data-follow="<?=($arParams["FOLLOW"] === "Y" ? "Y" : "N")?>" id="log_entry_follow_<?=(int)$arParams["LOG_ID"]?>" onclick="__blogPostSetFollow(<?=(int)$arParams["LOG_ID"]?>)"><a href="javascript:void(0);"><?=GetMessage("BLOG_POST_FOLLOW_".($arParams["FOLLOW"] === "Y" ? "Y" : "N"))?></a></span><?
						}

						if (!$arResult["bPublicPage"])
						{
							?><a
								 data-bx-post-id="<?=(int)$arResult['Post']['ID']?>"
								 data-bx-path-to-post="<?=htmlspecialcharsbx($arParams['PATH_TO_POST'])?>"
								 data-bx-path-to-edit="<?=($arResult['urlToEdit'] <> '' ? htmlspecialcharsbx($arResult['urlToEdit']) : '')?>"
								 data-bx-path-to-hide="<?=($arResult['urlToHide'] <> '' ? htmlspecialcharsbx($arResult['urlToHide']) : '')?>"
								 data-bx-path-to-delete="<?=(!$arResult['bFromList'] && $arResult['urlToDelete'] <> '' ? htmlspecialcharsbx($arResult['urlToDelete']) : '')?>"
								 data-bx-path-to-pub="<?=($arResult['urlToPostPub'] <> '' ? htmlspecialcharsbx($arResult['urlToPostPub']) : '')?>"
								 data-bx-public-page="<?=($arResult['bPublicPage'] ? 'Y' : 'N')?>"
								 data-bx-tasks-available="<?=($arResult['bTasksAvailable'] ? 'Y' : 'N')?>"
								 data-bx-vote-id="<?=(int)$voteId?>"
								 data-bx-post-type="<?=htmlspecialcharsbx($arParams['TYPE'])?>"
								 data-bx-group-read-only="<?=($arResult['ReadOnly'] ? 'Y' : 'N')?>"
								 data-bx-server-name="<?=htmlspecialcharsbx((\Bitrix\Main\Context::getCurrent()->getRequest()->isHttps() ? 'https' : 'http').'://'.((defined('SITE_SERVER_NAME') && SITE_SERVER_NAME <> '') ? SITE_SERVER_NAME : \Bitrix\Main\Config\Option::get('main', 'server_name', '')))?>"
								 data-bx-items="<?=\Bitrix\Main\Web\Json::encode(!empty($arParams['ADIT_MENU']) ? $arParams['ADIT_MENU'] : [])?>"
								 id="feed-post-menuanchor-<?=$arResult['Post']['ID']?>"
								 href="#"
								 class="feed-inform-item feed-post-more-link"><?
								?><span class="feed-post-more-text" id="feed-post-more-<?=$arResult["Post"]["ID"]?>"><?=GetMessage("BLOG_POST_BUTTON_MORE")?></span><?
								?><span class="feed-post-more-arrow"></span><?
							?></a><?
							?><script>
							BX.bind(BX('feed-post-menuanchor-<?=$arResult["Post"]["ID"]?>'), 'click', function(e) {
								BX.SBPostMenu.showMenu({
									event: e,
									menuNode: BX('feed-post-menuanchor-<?=$arResult["Post"]["ID"]?>'),
								});
								return BX.PreventDefault(e);
							});
							</script><?
						}

						?><span class="feed-inform-item feed-post-time-wrap feed-inform-contentview"><?
							if (
								!$arResult["bPublicPage"]
								&& isset($arResult["CONTENT_ID"])
								&& !in_array($arParams["TYPE"], array("DRAFT", "MODERATION"))
							)
							{
								$APPLICATION->IncludeComponent(
									"bitrix:socialnetwork.contentview.count", "",
									[
										"CONTENT_ID" => $arResult["CONTENT_ID"],
										"CONTENT_VIEW_CNT" => (isset($arResult["CONTENT_VIEW_CNT"]) ? $arResult["CONTENT_VIEW_CNT"] : 0),
										"PATH_TO_USER_PROFILE" => $arParams["PATH_TO_USER"],
										'IS_SET' => ($arResult['contentViewIsSet'] ? 'Y' : 'N')
									],
									$component,
									[ "HIDE_ICONS" => "Y" ]
								);
							}
							?></span><?

						if (
							$arResult["bIntranetInstalled"]
							&& $arParams["SHOW_RATING"] == "Y"
						)
						{
							?><div class="feed-post-emoji-top-panel-outer"><?
							?><div id="feed-post-emoji-top-panel-container-<?=htmlspecialcharsbx($voteId)?>" class="feed-post-emoji-top-panel-box <?=(intval($arResult["RATING"][$arResult["Post"]["ID"]]["TOTAL_POSITIVE_VOTES"]) > 0 ? 'feed-post-emoji-top-panel-container-active' : '')?>"><?
							$APPLICATION->IncludeComponent(
								"bitrix:rating.vote",
								"like_react",
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
									"REACTIONS_LIST" => $arResult["RATING"][$arResult["Post"]["ID"]]["REACTIONS_LIST"],
									"PATH_TO_USER_PROFILE" => $arParams["~PATH_TO_USER"],
									'TOP_DATA' => (!empty($arResult['TOP_RATING_DATA']) ? $arResult['TOP_RATING_DATA'] : false),
									'VOTE_ID' => $voteId
								),
								$component,
								array("HIDE_ICONS" => "Y")
							);
							?></div><?
							?></div><?
						}
					?></div></div><?
				}
			?></div><?


			if (
				!in_array($arParams["TYPE"], array("DRAFT", "MODERATION"))
				&& (
					empty($arParams['MODE'])
					|| $arParams['MODE'] != 'LANDING'
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
							$_REQUEST["RELOAD"] == "Y"
							&& !(
								empty($_REQUEST["bxajaxid"])
								&& empty($_REQUEST["logajax"])
							)
						)
					)
				)
				{
					include_once($_SERVER["DOCUMENT_ROOT"].$templateFolder."/destination.php");
				}

				if ($commentsContent <> '')
				{
					?><div class="feed-comments-block-wrap"><?=$commentsContent?></div><?
				}
			}

			?><div class="feed-post-right-top-corner"><?

				if ($USER->isAuthorized())
				{
					$pinned = (
						!empty($arParams['PINNED_PANEL_DATA'])
						|| (isset($arParams['PINNED']) && $arParams['PINNED'] === 'Y')
					);

					?><a href="#" class="feed-post-pinned-link feed-post-pinned-link-collapse"><?=Loc::getMessage('BLOG_POST_PINNED_COLLAPSE')?></a><?
					?><div id="feed-post-menuanchor-right-<?=$arResult["Post"]["ID"]?>" class="feed-post-right-top-menu"></div><?

					?>
					<script>
						BX.bind(BX('feed-post-menuanchor-right-<?=$arResult["Post"]["ID"]?>'), 'click', function(e) {
							BX.SBPostMenu.showMenu({
								event: e,
								menuNode: BX('feed-post-menuanchor-<?=$arResult["Post"]["ID"]?>'),
							});
							return BX.PreventDefault(e);
						});
					</script>
					<?

					?><div bx-data-pinned="<?=($pinned ? 'Y' : 'N')?>" class="feed-post-pin" title="<?=Loc::getMessage('SONET_EXT_LIVEFEED_PIN_TITLE_'.($pinned ? 'Y' : 'N'))?>"></div><?
				}

			?></div><?

		?></div><?
	}
	elseif(!$arResult["bFromList"])
	{
		echo GetMessage("BLOG_BLOG_BLOG_NO_AVAIBLE_MES");
	}
}
?></div><? // feed-item-wrap