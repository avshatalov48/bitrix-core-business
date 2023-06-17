<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var CBitrixComponentTemplate $this */
/** @var CBitrixComponent $component */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

use Bitrix\Main\Config\Option;
use Bitrix\Main\UI;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Uri;

UI\Extension::load([
	'ui.animations',
	'ui.tooltip',
	'ui.icons.b24',
	'main.rating',
	'socialnetwork.livefeed'
]);

if ($arResult["bTasksAvailable"])
{
	CJSCore::Init(array('tasks_util_query'));
}

if (($arResult["FatalError"] ?? '') <> '')
{
	?><span class='errortext'><?= $arResult["FatalError"] ?></span><br /><br /><?php
}
else
{
	$jsAjaxPage = CUtil::JSEscape($APPLICATION->GetCurPageParam("", array("bxajaxid", "logajax", "logout")));
	$randomString = RandString(8);
	$randomId = 0;

	$anchor_id = $randomString . $randomId;

	if (($arResult["ErrorMessage"] ?? '') <> '')
	{
		?><span class='errortext'><?= $arResult["ErrorMessage"] ?></span><br /><br /><?php
	}

	if (
		$arResult["Event"]
		&& is_array($arResult["Event"])
		&& !empty($arResult["Event"])
	)
	{
		$arEvent = &$arResult['Event'];

		?>
		<div
			class="feed-item-wrap"
			data-livefeed-id="<?= (int)$arEvent["EVENT"]["ID"] ?>"
			bx-content-view-key-signed="<?= htmlspecialcharsbx($arResult['CONTENT_VIEW_KEY_SIGNED'] ?? '') ?>"
		><?php

		if (!defined("SONET_LOG_JS"))
		{
			define("SONET_LOG_JS", true);

			$message = array(
				'sonetLEGetPath' => '/bitrix/components/bitrix/socialnetwork.log.entry/ajax.php',
				'sonetLESetPath' => '/bitrix/components/bitrix/socialnetwork.log.entry/ajax.php',
				'sonetLPathToUser' => $arParams["PATH_TO_USER"],
				'sonetLPathToGroup' => $arParams["PATH_TO_GROUP"],
				'sonetLPathToDepartment' => $arParams["PATH_TO_CONPANY_DEPARTMENT"] ?? null,
				'sonetLPathToSmile' => $arParams["PATH_TO_SMILE"],
				'sonetLShowRating' => $arParams["SHOW_RATING"],
				'sonetLTextLikeY' => Option::get("main", "rating_text_like_y", Loc::getMessage("SONET_C30_TEXT_LIKE_Y")),
				'sonetLTextLikeN' => Option::get("main", "rating_text_like_n", Loc::getMessage("SONET_C30_TEXT_LIKE_N")),
				'sonetLTextLikeD' => Option::get("main", "rating_text_like_d", Loc::getMessage("SONET_C30_TEXT_LIKE_D")),
				'sonetLTextPlus' => Loc::getMessage("SONET_C30_TEXT_PLUS"),
				'sonetLTextMinus' => Loc::getMessage("SONET_C30_TEXT_MINUS"),
				'sonetLTextCancel' => Loc::getMessage("SONET_C30_TEXT_CANCEL"),
				'sonetLTextAvailable' => Loc::getMessage("SONET_C30_TEXT_AVAILABLE"),
				'sonetLTextDenied' => Loc::getMessage("SONET_C30_TEXT_DENIED"),
				'sonetLTextRatingY' => Loc::getMessage("SONET_C30_TEXT_RATING_YES"),
				'sonetLTextRatingN' => Loc::getMessage("SONET_C30_TEXT_RATING_NO"),
				'sonetLTextCommentError' => Loc::getMessage("SONET_COMMENT_ERROR"),
				'sonetLPathToUserBlogPost' => $arParams["PATH_TO_USER_BLOG_POST"],
				'sonetLPathToGroupBlogPost' => $arParams["PATH_TO_GROUP_BLOG_POST"],
				'sonetLPathToUserMicroblogPost' => $arParams["PATH_TO_USER_MICROBLOG_POST"],
				'sonetLPathToGroupMicroblogPost' => $arParams["PATH_TO_GROUP_MICROBLOG_POST"],
				'sonetLNameTemplate' => $arParams["NAME_TEMPLATE"],
				'sonetLDateTimeFormat' => $arParams["DATE_TIME_FORMAT"],
				'sonetLShowLogin' => $arParams["SHOW_LOGIN"],
				'sonetLRatingType' => $arParams["RATING_TYPE"],
				'sonetLCurrentUserID' => (int)$USER->getId(),
				'sonetLAvatarSize' => $arParams["AVATAR_SIZE"],
				'sonetLAvatarSizeComment' => $arParams["AVATAR_SIZE_COMMON"],
				'sonetLBlogAllowPostCode' => $arParams["BLOG_ALLOW_POST_CODE"] ?? '',
				'sonetLDestinationHidden1' => Loc::getMessage("SONET_C30_DESTINATION_HIDDEN_1"),
				'sonetLDestinationHidden2' => Loc::getMessage("SONET_C30_DESTINATION_HIDDEN_2"),
				'sonetLDestinationHidden3' => Loc::getMessage("SONET_C30_DESTINATION_HIDDEN_3"),
				'sonetLDestinationHidden4' => Loc::getMessage("SONET_C30_DESTINATION_HIDDEN_4"),
				'sonetLDestinationHidden5' => Loc::getMessage("SONET_C30_DESTINATION_HIDDEN_5"),
				'sonetLDestinationHidden6' => Loc::getMessage("SONET_C30_DESTINATION_HIDDEN_6"),
				'sonetLDestinationHidden7' => Loc::getMessage("SONET_C30_DESTINATION_HIDDEN_7"),
				'sonetLDestinationHidden8' => Loc::getMessage("SONET_C30_DESTINATION_HIDDEN_8"),
				'sonetLDestinationHidden9' => Loc::getMessage("SONET_C30_DESTINATION_HIDDEN_9"),
				'sonetLDestinationHidden0' => Loc::getMessage("SONET_C30_DESTINATION_HIDDEN_0"),
				'sonetLDestinationLimit' => (int)$arParams["DESTINATION_LIMIT_SHOW"],
			);
			if ($arParams["USE_FOLLOW"] === "Y")
			{
				$message['sonetLFollowY'] = Loc::getMessage("SONET_LOG_T_FOLLOW_Y");
				$message['sonetLFollowN'] = Loc::getMessage("SONET_LOG_T_FOLLOW_N");
			}
			?><script>
				BX.message(<?= CUtil::PhpToJSObject($message) ?>);
			</script>
			<?php
		}

		$ind = $arParams["IND"];
		$is_unread = $arParams["EVENT"]["IS_UNREAD"];

		$important = (
			array_key_exists("EVENT_FORMATTED", $arEvent)
			&& array_key_exists("IS_IMPORTANT", $arEvent["EVENT_FORMATTED"])
			&& $arEvent["EVENT_FORMATTED"]["IS_IMPORTANT"]
		);

		if (
			$arParams["PUBLIC_MODE"] !== 'Y'
			&& isset($arEvent["EVENT_FORMATTED"]["URL"])
			&& $arEvent["EVENT_FORMATTED"]["URL"] !== ""
			&& $arEvent["EVENT_FORMATTED"]["URL"] !== false
		)
		{
			$url = $arEvent["EVENT_FORMATTED"]["URL"];
		}
		elseif (
			$arParams["PUBLIC_MODE"] !== "Y"
			&& isset($arEvent["EVENT"]["URL"])
			&& $arEvent["EVENT"]["URL"] !== ""
			&& $arEvent["EVENT"]["URL"] !== false
		)
		{
			$url = $arEvent["EVENT"]["URL"];
		}
		else
		{
			$url = "";
		}

		$hasTitle24 = isset($arEvent["EVENT_FORMATTED"]["TITLE_24"])
			&& $arEvent["EVENT_FORMATTED"]["TITLE_24"] !== ""
			&& $arEvent["EVENT_FORMATTED"]["TITLE_24"] !== false;

		$hasTitle24_2 = isset($arEvent["EVENT_FORMATTED"]["TITLE_24_2"])
			&& $arEvent["EVENT_FORMATTED"]["TITLE_24_2"] !== ""
			&& $arEvent["EVENT_FORMATTED"]["TITLE_24_2"] !== false;

		?><script>
			BX.viewElementBind(
				'sonet_log_day_item_<?=$ind?>',
				{showTitle: true},
				function(node){
					return BX.type.isElementNode(node) && (node.getAttribute('data-bx-viewer') || node.getAttribute('data-bx-image'));
				}
			);
		</script><?php

		$classNameList = [ 'feed-post-block' ];

		if ($is_unread)
		{
			$classNameList[] = 'feed-post-block-new';
		}

		if (
			array_key_exists("EVENT_FORMATTED", $arEvent)
			&& array_key_exists("STYLE", $arEvent["EVENT_FORMATTED"])
			&& $arEvent["EVENT_FORMATTED"]["STYLE"] <> ''
		)
		{
			$classNameList[] = 'feed-'.$arEvent["EVENT_FORMATTED"]["STYLE"];
		}

		if (!empty($arParams['PINNED_PANEL_DATA']))
		{
			$classNameList[] = 'feed-post-block-pinned';
		}

		if (
			!empty($arEvent['EVENT']['FOLLOW'])
			&& $arEvent['EVENT']['FOLLOW'] === 'N'
		)
		{
			$classNameList[] = 'feed-post-block-unfollowed';
		}

		if (
			(
				isset($arResult["EVENT_FORMATTED"]["UF"]["UF_SONET_LOG_FILE"])
				&& !empty($arResult["EVENT_FORMATTED"]["UF"]["UF_SONET_LOG_FILE"]["VALUE"])
			)
			|| (
				isset($arResult["EVENT_FORMATTED"]["UF"]["UF_SONET_LOG_DOC"])
				&& !empty($arResult["EVENT_FORMATTED"]["UF"]["UF_SONET_LOG_DOC"]["VALUE"])
			)
		)
		{
			$classNameList[] = 'feed-post-block-files';
		}

		$EVENT_ID = $arEvent["EVENT"]["EVENT_ID"];

		if (
			$arParams["FROM_LOG"] !== 'Y'
			|| in_array($EVENT_ID, array("files", "commondocs", "tasks"))
			|| $arEvent["EVENT_FORMATTED"]["MESSAGE"] == ''
		)
		{
			$classNameList[] = 'feed-post-block-short';
		}

		if ($important)
		{
			$classNameList[] = 'feed-post-block-separator';
		}

		if (
			(
				!isset($arParams['IS_CRM'])
				|| $arParams['IS_CRM'] !== 'Y'
			)
			&& $USER->isAuthorized()
		)
		{
			$pinned = (
				!empty($arParams['PINNED_PANEL_DATA'])
				|| (isset($arParams['EVENT']['PINNED']) && $arParams['EVENT']['PINNED'] === 'Y')
			);

			$classNameList[] = 'feed-post-block-pin';
			if ($pinned)
			{
				$classNameList[] = 'feed-post-block-pin-active';
			}
		}

		$hasNotEmptyProperty = (
			isset($arEvent['EVENT_FORMATTED']['UF'])
			&& is_array($arEvent['EVENT_FORMATTED']['UF'])
			&& array_reduce($arEvent['EVENT_FORMATTED']['UF'], static function ($val, $propertyData) {
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
			|| (
				$arEvent['EVENT']['EVENT_ID'] !== 'tasks'
				&& !empty($arEvent['TAGS'])
				&& is_array($arEvent['TAGS'])
			)
		)
		{
			$classNameList[] = 'feed-post-block-has-bottom';
		}

		?><div
			class="<?=implode(' ', $classNameList)?>"
			id="log-entry-<?=$arEvent["EVENT"]["ID"]?>"
			ondragenter="BX('feed_comments_block_<?=$arEvent["EVENT"]["ID"]?>').style.display = 'block'"
			data-livefeed-id="<?=(int)$arEvent["EVENT"]["ID"]?>"
			bx-content-view-key-signed="<?= htmlspecialcharsbx($arResult['CONTENT_VIEW_KEY_SIGNED'] ?? '') ?>"
			data-menu-id="post-menu-<?=$ind?>"
			<?php
			if (isset($pinned))
			{
				?>
				 data-livefeed-post-pinned="<?=($pinned ? 'Y' : 'N')?>"
				 data-security-entity-pin="<?= (int)$arEvent['EVENT']['ID'] ?>"
				 data-security-token-pin="<?= htmlspecialcharsbx($arResult['LOG_ID_TOKEN']) ?>"
				<?php
			}
			?>
		><?php
			$aditStylesList = [ 'feed-post-cont-wrap' ];

			if (
				isset($arEvent["EVENT"]["USER_ID"])
				&& $arEvent["EVENT"]["USER_ID"] > 0
			)
			{
				$aditStylesList[] = 'sonet-log-item-createdby-'.(int)$arEvent["EVENT"]["USER_ID"];
			}

			if (
				array_key_exists("ENTITY_TYPE", $arEvent["EVENT"])
				&& $arEvent["EVENT"]["ENTITY_TYPE"] <> ''
				&& array_key_exists("ENTITY_ID", $arEvent["EVENT"])
				&& (int)$arEvent["EVENT"]["ENTITY_ID"] > 0
			)
			{
				$aditStylesList[] = 'sonet-log-item-where-'.$arEvent["EVENT"]["ENTITY_TYPE"].'-'.(int)$arEvent["EVENT"]["ENTITY_ID"].'-all';

				if (
					array_key_exists("EVENT_ID", $arEvent["EVENT"])
					&& $arEvent["EVENT"]["EVENT_ID"] <> ''
				)
				{
					$aditStylesList[] = 'sonet-log-item-where-'.$arEvent["EVENT"]["ENTITY_TYPE"].'-'.(int)$arEvent["EVENT"]["ENTITY_ID"].'-'.str_replace('_', '-', $arEvent["EVENT"]["EVENT_ID"]);

					if (
						array_key_exists("EVENT_ID_FULLSET", $arEvent["EVENT"])
						&& $arEvent["EVENT"]["EVENT_ID_FULLSET"] <> ''
						&& $arEvent["EVENT"]["EVENT_ID_FULLSET"] !== $arEvent["EVENT"]["EVENT_ID"]
					)
					{
						$aditStylesList[] = 'sonet-log-item-where-'.$arEvent["EVENT"]["ENTITY_TYPE"].'-'.(int)$arEvent["EVENT"]["ENTITY_ID"].'-'.str_replace('_', '-', $arEvent["EVENT"]["EVENT_ID_FULLSET"]);
					}
				}
			}

			?><div id="sonet_log_day_item_<?=$ind?>" class="<?=implode(' ', $aditStylesList)?>"><?php

				if (($_REQUEST["action"] ?? '') === "get_entry")
				{
					$APPLICATION->RestartBuffer();
					$strEntryText = "";
					ob_start();
				}

				$avatar = false;
				if (isset($arEvent["AVATAR_SRC"]) && $arEvent["AVATAR_SRC"] <> '')
				{
					$avatar = $arEvent["AVATAR_SRC"];
				}

				$style = ($avatar ? "background: url('" . Uri::urnEncode($avatar) . "'); background-size: cover;" : "");

				?><div class="ui-icon ui-icon-common-user feed-user-avatar"><i style="<?= $style ?>"></i></div><?php

				?><div class="feed-post-pinned-block"><?php
					?><div class="feed-post-pinned-title"><?php
						if (
							!empty($arParams['PINNED_PANEL_DATA'])
							&& $arParams['PINNED_PANEL_DATA']['TITLE'] <> ''
						)
						{
							?><?=$arParams['PINNED_PANEL_DATA']['TITLE']?><?php
						}

					?></div><?php
					?><div class="feed-post-pinned-text-box"><?php
						?><div class="feed-post-pinned-desc"><?php
							if (
								!empty($arParams['PINNED_PANEL_DATA'])
								&& $arParams['PINNED_PANEL_DATA']['DESCRIPTION'] <> ''
							)
							{
								?><?=$arParams['PINNED_PANEL_DATA']['DESCRIPTION']?><?php
							}
						?></div><?php
						?><a href="#" class="feed-post-pinned-link feed-post-pinned-link-expand"><?=Loc::getMessage('SONET_C30_FEED_PINNED_EXPAND')?></a><?php
					?></div><?php
				?></div><?php

				?><div class="feed-post-title-block"><?php
					$strDestination = "";

					if (
						isset($arEvent["EVENT_FORMATTED"]["DESTINATION"])
						&& is_array($arEvent["EVENT_FORMATTED"]["DESTINATION"])
						&& !empty($arEvent["EVENT_FORMATTED"]["DESTINATION"])
					)
					{
						$strDestination .= ' <span class="feed-add-post-destination-icon"></span> ';

						$i = 0;
						foreach ($arEvent["EVENT_FORMATTED"]["DESTINATION"] as $arDestination)
						{
							$classAdditionalList = [ 'feed-add-post-destination-new' ];
							$classPrefixAdditionalList = [ 'feed-add-post-destination-prefix' ];

							if (!isset($arParams["PUBLIC_MODE"]) || $arParams["PUBLIC_MODE"] !== "Y")
							{
								if (
									array_key_exists("CRM_USER_ID", $arDestination)
									&& (int)$arDestination["CRM_USER_ID"] > 0
								)
								{
									$classPrefixAdditionalList[] = 'feed-add-post-destination-prefix-crmuser';
								}
								elseif (
									array_key_exists("IS_EMAIL", $arDestination)
									&& $arDestination["IS_EMAIL"] === "Y"
								)
								{
									$classAdditionalList[] = 'feed-add-post-destination-new-email';
								}
								elseif (
									array_key_exists("IS_EXTRANET", $arDestination)
									&& $arDestination["IS_EXTRANET"] === true
								)
								{
									$classAdditionalList[] = 'feed-add-post-destination-new-extranet';
								}
							}

							if ($i > 0)
							{
								$strDestination .= ', ';
							}

							if (!empty($arDestination["CRM_PREFIX"]))
							{
								$strDestination .= ' <span class="'.implode(' ', $classPrefixAdditionalList).'">'.$arDestination["CRM_PREFIX"].':&nbsp;</span>';
							}

							$strDestination .= ($arDestination["URL"] <> ''
								? '<a class="'.implode(' ', $classAdditionalList).'" href="'.htmlspecialcharsbx($arDestination["URL"]).'">'.$arDestination["TITLE"].'</a>'
								: '<span class="'.implode(' ', $classAdditionalList).'">'.$arDestination["TITLE"].'</span>'
							);
							$i++;
						}

						$iMoreDest = (int) ($arEvent["EVENT_FORMATTED"]["DESTINATION_MORE"] ?? 0);

						if ($iMoreDest > 0)
						{
							if (
								isset($arEvent["EVENT_FORMATTED"]["DESTINATION_HIDDEN"])
								&& (int)$arEvent["EVENT_FORMATTED"]["DESTINATION_HIDDEN"] > 0
							)
							{
								$iMoreDest += (int)$arEvent["EVENT_FORMATTED"]["DESTINATION_HIDDEN"];
							}

							$suffix = (
								($iMoreDest % 100) > 10
								&& ($iMoreDest % 100) < 20
									? 5
									: $iMoreDest % 10
							);

							$strDestination .= '<a class="feed-post-link-new" onclick="__logShowHiddenDestination(' . $arEvent["EVENT"]["ID"] . ', '.(
								isset($arEvent["CREATED_BY"]["TOOLTIP_FIELDS"]["ID"])
								&& is_array($arEvent["CREATED_BY"])
								&& is_array($arEvent["CREATED_BY"]["TOOLTIP_FIELDS"])
									? (int)$arEvent["CREATED_BY"]["TOOLTIP_FIELDS"]["ID"]
									: "false"
								).', this)" href="javascript:void(0)">'.str_replace("#COUNT#", $iMoreDest, GetMessage("SONET_C30_DESTINATION_MORE_".$suffix)).'</a>';
						}
						elseif (
							isset($arEvent["EVENT_FORMATTED"]["DESTINATION_HIDDEN"])
							&& (int)$arEvent["EVENT_FORMATTED"]["DESTINATION_HIDDEN"] > 0
						)
						{
							$suffix = (
								($arEvent["EVENT_FORMATTED"]["DESTINATION_HIDDEN"] % 100) > 10
								&& ($arEvent["EVENT_FORMATTED"]["DESTINATION_HIDDEN"] % 100) < 20
									? 5
									: $arEvent["EVENT_FORMATTED"]["DESTINATION_HIDDEN"] % 10
							);

							$strDestination .= ' '.str_replace("#COUNT#", $arEvent["EVENT_FORMATTED"]["DESTINATION_HIDDEN"], GetMessage("SONET_C30_DESTINATION_HIDDEN_".$suffix));
						}
					}

					$strCreatedBy = "";
					if (
						array_key_exists("CREATED_BY", $arEvent)
						&& is_array($arEvent["CREATED_BY"])
					)
					{
						if (
							array_key_exists("TOOLTIP_FIELDS", $arEvent["CREATED_BY"])
							&& is_array($arEvent["CREATED_BY"]["TOOLTIP_FIELDS"])
						)
						{
							if ($arParams["PUBLIC_MODE"] !== 'Y')
							{
								$classNameList = [ 'feed-post-user-name' ];
								if (
									array_key_exists("IS_EXTRANET", $arEvent["CREATED_BY"])
									&& $arEvent["CREATED_BY"]["IS_EXTRANET"] === "Y"
								)
								{
									$classNameList[] = 'feed-post-user-name-extranet';
								}
								$href = str_replace(array("#user_id#", "#USER_ID#", "#id#", "#ID#"), $arEvent["CREATED_BY"]["TOOLTIP_FIELDS"]["ID"], $arEvent["CREATED_BY"]["TOOLTIP_FIELDS"]["PATH_TO_SONET_USER_PROFILE"]);
								$anchor_id = $randomString . ($randomId++);

								$strCreatedBy .= '<a class="'.implode(' ', $classNameList).'"'.
									' id="anchor_'.$anchor_id.'"'.
									' bx-post-author-id="'.$arEvent["CREATED_BY"]["TOOLTIP_FIELDS"]["ID"].'"'.
									' bx-post-author-gender="'.$arEvent["CREATED_BY"]["TOOLTIP_FIELDS"]["PERSONAL_GENDER"].'"'.
									' bx-tooltip-user-id="'.$arEvent["CREATED_BY"]["TOOLTIP_FIELDS"]["ID"].'"'.
									' href="'.$href.'">'.CUser::FormatName($arParams["NAME_TEMPLATE"], $arEvent["CREATED_BY"]["TOOLTIP_FIELDS"], ($arParams["SHOW_LOGIN"] !== "N" ? true : false)).'</a>';
							}
							else
							{
								$strCreatedBy .= '<span class="feed-post-user-name'.(array_key_exists("IS_EXTRANET", $arEvent["CREATED_BY"]) && $arEvent["CREATED_BY"]["IS_EXTRANET"] === "Y" ? " feed-post-user-name-extranet" : "").'">'.CUser::FormatName($arParams["NAME_TEMPLATE"], $arEvent["CREATED_BY"]["TOOLTIP_FIELDS"], ($arParams["SHOW_LOGIN"] !== "N" ? true : false)).'</span>';
							}
						}
						elseif (
							array_key_exists("FORMATTED", $arEvent["CREATED_BY"])
							&& $arEvent["CREATED_BY"]["FORMATTED"] <> ''
						)
						{
							$strCreatedBy .= '<span class="feed-post-user-name'.(array_key_exists("IS_EXTRANET", $arEvent["CREATED_BY"]) && $arEvent["CREATED_BY"]["IS_EXTRANET"] === "Y" ? " feed-post-user-name-extranet" : "").'">'.$arEvent["CREATED_BY"]["FORMATTED"].'</span>';
						}
					}
					elseif (
						array_key_exists("ENTITY", $arEvent)
						&& (
							$arEvent["EVENT"]["EVENT_ID"] === "data"
							|| $arEvent["EVENT"]["EVENT_ID"] === "news"
						)
					)
					{
						if (
							array_key_exists("TOOLTIP_FIELDS", $arEvent["ENTITY"])
							&& is_array($arEvent["ENTITY"]["TOOLTIP_FIELDS"])
						)
						{
							$classNameList = [ 'feed-post-user-name' ];
							if (isset($arEvent["CREATED_BY"]["IS_EXTRANET"]) && $arEvent["CREATED_BY"]["IS_EXTRANET"] === "Y")
							{
								$classNameList[] = 'feed-post-user-name-extranet';
							}
							$anchor_id = $randomString.($randomId++);
							$href = str_replace(array("#user_id#", "#USER_ID#", "#id#", "#ID#"), $arEvent["ENTITY"]["TOOLTIP_FIELDS"]["ID"], $arEvent["ENTITY"]["TOOLTIP_FIELDS"]["PATH_TO_SONET_USER_PROFILE"]);

							$strCreatedBy .= '<a '.
								' class="'.implode(' ', $classNameList).'"'.
								' id="anchor_'.$anchor_id.'"'.
								' bx-post-author-id="'.$arEvent["ENTITY"]["TOOLTIP_FIELDS"]["ID"].'"'.
								' bx-post-author-gender="'.$arEvent["ENTITY"]["TOOLTIP_FIELDS"]["PERSONAL_GENDER"].'"'.
								' bx-tooltip-user-id="'.$arEvent["ENTITY"]["TOOLTIP_FIELDS"]["ID"].'"'.
								' href="'.$href.'">'.CUser::FormatName($arParams["NAME_TEMPLATE"], $arEvent["ENTITY"]["TOOLTIP_FIELDS"], ($arParams["SHOW_LOGIN"] !== "N" ? true : false)).'</a>';
						}
						elseif (
							array_key_exists("FORMATTED", $arEvent["ENTITY"])
							&& array_key_exists("NAME", $arEvent["ENTITY"]["FORMATTED"])
						)
						{
							if (array_key_exists("URL", $arEvent["ENTITY"]["FORMATTED"]) && $arEvent["ENTITY"]["FORMATTED"]["URL"] <> '')
							{
								$strCreatedBy .= '<a href="'.$arEvent["ENTITY"]["FORMATTED"]["URL"].'" class="feed-post-user-name'.(isset($arEvent["CREATED_BY"]["IS_EXTRANET"]) && $arEvent["CREATED_BY"]["IS_EXTRANET"] === "Y" ? " feed-post-user-name-extranet" : "").'">'.$arEvent["ENTITY"]["FORMATTED"]["NAME"].'</a>';
							}
							else
							{
								$strCreatedBy .= '<span class="feed-post-user-name'.(isset($arEvent["CREATED_BY"]["IS_EXTRANET"]) && $arEvent["CREATED_BY"]["IS_EXTRANET"] === "Y" ? " feed-post-user-name-extranet" : "").'">'.$arEvent["ENTITY"]["FORMATTED"]["NAME"].'</span>';
							}
						}
					}

					?><?= ($strCreatedBy != "" ? $strCreatedBy : "") ?><?php
					?><span><?= $strDestination ?></span><?php

					?><div class="feed-post-time-wrap"><?php

						$timestamp = (
							isset($arEvent["EVENT_FORMATTED"]["LOG_DATE_FORMAT"])
								? MakeTimeStamp($arEvent["EVENT_FORMATTED"]["LOG_DATE_FORMAT"])
								: (
									array_key_exists("LOG_DATE_FORMAT", $arEvent)
										? MakeTimeStamp($arEvent["LOG_DATE_FORMAT"])
										: $arEvent["LOG_DATE_TS"]
							)
						);

						$datetime_detail = CComponentUtil::getDateTimeFormatted([
							'TIMESTAMP' => $timestamp,
							'DATETIME_FORMAT' => $arParams["DATE_TIME_FORMAT"],
							'DATETIME_FORMAT_WITHOUT_YEAR' => ($arParams["DATE_TIME_FORMAT_WITHOUT_YEAR"] ?? false),
							'TZ_OFFSET' => $arResult["TZ_OFFSET"],
						]);

						if (!empty($url))
						{
							?><a href="<?= htmlspecialcharsbx($url) ?>"><div class="feed-time"><?= $datetime_detail ?></div></a><?php
						}
						else
						{
							?><div class="feed-time"><?= $datetime_detail ?></div><?php
						}

					?></div><?php

					$title24_2 = '';

					if (
						array_key_exists("EVENT_FORMATTED", $arEvent)
						&& ( $hasTitle24 || $hasTitle24_2 )
					)
					{
						if ($hasTitle24)
						{
							?><div class="feed-post-item"><?php
							switch ($arEvent["EVENT"]["EVENT_ID"])
							{
							case "photo":
								?><div class="feed-add-post-destination-title"><span class="feed-add-post-files-title feed-add-post-p"><?=$arEvent["EVENT_FORMATTED"]["TITLE_24"]?></span></div><?php
								break;
							case "timeman_entry":
								?><div class="feed-add-post-files-title"><?=$arEvent["EVENT_FORMATTED"]["TITLE_24"]?><a href="<?=htmlspecialcharsbx($arEvent['ENTITY']['FORMATTED']['URL'])?>" class="feed-work-time-link"><?=GetMessage("SONET_C30_MENU_ENTRY_TIMEMAN")?><span class="feed-work-time-icon"></span></a></div><?php
								break;
							case "report":
								?><div class="feed-add-post-files-title"><?=$arEvent["EVENT_FORMATTED"]["TITLE_24"]?><a href="<?=htmlspecialcharsbx($arEvent['ENTITY']['FORMATTED']['URL'])?>" class="feed-work-time-link"><?=GetMessage("SONET_C30_MENU_ENTRY_REPORTS")?><span class="feed-work-time-icon"></span></a></div><?php
								break;
							case "tasks":
								?><div class="feed-add-post-destination-title"><?=$arEvent["EVENT_FORMATTED"]["TITLE_24"]?><span class="feed-work-time"><?=GetMessage("SONET_C30_MENU_ENTRY_TASKS")?><span class="feed-work-time-icon"></span></span></div><?php
								break;
							default:
								?><div class="feed-add-post-destination-title"><?=$arEvent["EVENT_FORMATTED"]["TITLE_24"]?></div><?php
								break;
							}
							?></div><?php
						}

						if (
							!$important
							&& $hasTitle24_2
						)
						{
							ob_start();

							?><div class="feed-post-item feed-post-item-title"><?php
								if ($url !== "")
								{
									?><div class="feed-post-title<?=(isset($arEvent["EVENT_FORMATTED"]["TITLE_24_2_STYLE"]) ? " ".$arEvent["EVENT_FORMATTED"]["TITLE_24_2_STYLE"] : "")?>"><a href="<?=$url?>"><?=$arEvent["EVENT_FORMATTED"]["TITLE_24_2"]?></a></div><?php
								}
								else
								{
									?><div class="feed-post-title<?=(isset($arEvent["EVENT_FORMATTED"]["TITLE_24_2_STYLE"]) ? " ".$arEvent["EVENT_FORMATTED"]["TITLE_24_2_STYLE"] : "")?>"><?=$arEvent["EVENT_FORMATTED"]["TITLE_24_2"]?></div><?php
								}
							?></div><?php

							$title24_2 = ob_get_clean();
						}
					}

				?></div><?php // title

				// body

				$stub = false;
				if (
					array_key_exists("EVENT_FORMATTED", $arEvent)
					&& array_key_exists("STUB", $arEvent["EVENT_FORMATTED"])
					&& $arEvent["EVENT_FORMATTED"]["STUB"]
				)
				{
					$stub = true;
					?><?php
					$APPLICATION->IncludeComponent(
						"bitrix:socialnetwork.log.entry.stub",
						"",
						array(
							"EVENT" => $arEvent['EVENT'],
						),
						$component,
						array(
							"HIDE_ICONS" => "Y"
						)
					);
					?><?php
				}
				elseif ($important)
				{
					$APPLICATION->SetAdditionalCSS('/bitrix/components/bitrix/rating.vote/templates/like/popup.css');

					$classNameList = [
						'feed-post-text-block',
						'feed-info-block',
						'feed-post-block-background',
						'feed-post-block-important'
					];

					$outerBlockId = 'log_entry_outer_' . $arEvent['EVENT']['ID'];

					?><div class="<?=implode(' ', $classNameList)?>" id="<?= $outerBlockId ?>"><?php

						$classNameList = [ 'feed-post-contentview' ];
						if ($arParams["FROM_LOG"] === "Y")
						{
							$classNameList[] = 'feed-post-text-block-inner';
						}

						$contentViewBlockId = (
							!empty($arResult['CONTENT_ID'])
								? 'feed-post-contentview-' . $arResult['CONTENT_ID']
								: ''
						);

						?><div
						 class="<?=implode(' ', $classNameList)?>"
						 id="<?= htmlspecialcharsbx($contentViewBlockId) ?>"
						 bx-content-view-xml-id="<?=htmlspecialcharsBx($arResult["CONTENT_ID"])?>"
						 bx-content-view-key-signed="<?= htmlspecialcharsBx($arResult['CONTENT_VIEW_KEY_SIGNED']) ?>"><?php
							?><div class="feed-post-text-block-inner-inner" id="log_entry_body_<?=$arEvent["EVENT"]["ID"]?>"><?php

								?><div class="feed-important-icon"></div><?php

								?><?=$title24_2?><?php

								if ($hasTitle24_2)
								{
									?><div class="feed-post-item feed-post-item-title"><?php
										if ($url !== "")
										{
											?><a href="<?=$url?>" class="feed-post-title" target="_top"><?=$arEvent["EVENT_FORMATTED"]["TITLE_24_2"]?></a><?php
										}
										else
										{
											?><div class="feed-post-title"><?=$arEvent["EVENT_FORMATTED"]["TITLE_24_2"]?></div><?php
										}
									?></div><?php
								}
								?><?=$arEvent["EVENT_FORMATTED"]["MESSAGE"]?>

							</div><?php
						?></div><?php

						if ($arParams["FROM_LOG"] === 'Y')
						{
							?><div class="feed-post-text-more" id="log_entry_more_<?= $arEvent['EVENT']['ID'] ?>"><?php
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

									BX.Livefeed.FeedInstance.addMoreButton(
										'log_entry_<?= $arEvent['EVENT']['ID'] ?>',
										{
											outerBlockID: '<?= CUtil::JSEscape($outerBlockId) ?>',
											bodyBlockID: 'log_entry_body_<?= $arEvent['EVENT']['ID'] ?>',
											informerBlockID: 'log_entry_inform_<?= $arEvent['EVENT']['ID'] ?>',
										}
									);
								});
							</script><?php
						}
					?></div><?php
				}
				elseif (
					$EVENT_ID === "photo"
					|| $EVENT_ID === "photo_photo"
				)
				{

					?><div
						class="feed-post-item feed-post-contentview"
						id="<?=(!empty($arResult["CONTENT_ID"]) ? "feed-post-contentview-".htmlspecialcharsBx($arResult["CONTENT_ID"]) : "")?>"
						bx-content-view-xml-id="<?=htmlspecialcharsBx($arResult["CONTENT_ID"])?>"
						bx-content-view-key-signed="<?= htmlspecialcharsBx($arResult['CONTENT_VIEW_KEY_SIGNED'] ?? '') ?>"
					><?php

						?><?=$title24_2?><?php

						$arPhotoItems = array();
						$photo_section_id = false;
						if ($EVENT_ID === "photo")
						{
							$photo_section_id = $arEvent["EVENT"]["SOURCE_ID"];
							if ($arEvent["EVENT"]["PARAMS"] <> '')
							{
								$arEventParams = unserialize(htmlspecialcharsback($arEvent["EVENT"]["PARAMS"]), [ 'allowed_classes' => false ]);
								if (
									$arEventParams
									&& isset($arEventParams["arItems"])
									&& is_array($arEventParams["arItems"])
								)
								{
									$arPhotoItems = $arEventParams["arItems"];
								}
							}
						}
						elseif ($EVENT_ID === "photo_photo")
						{
							if ((int)$arEvent["EVENT"]["SOURCE_ID"] > 0)
							{
								$arPhotoItems = array($arEvent["EVENT"]["SOURCE_ID"]);
							}

							if ($arEvent["EVENT"]["PARAMS"] <> '')
							{
								$arEventParams = unserialize(htmlspecialcharsback($arEvent["EVENT"]["PARAMS"]), [ 'allowed_classes' => false ]);
								if (
									$arEventParams
									&& isset($arEventParams["SECTION_ID"])
									&& (int)$arEventParams["SECTION_ID"] > 0
								)
								{
									$photo_section_id = $arEventParams["SECTION_ID"];
								}
							}
						}

						if ($arEvent["EVENT"]["PARAMS"] <> '')
						{
							$arEventParams = unserialize(htmlspecialcharsback($arEvent["EVENT"]["PARAMS"]), [ 'allowed_classes' => false ]);

							$photo_iblock_type = $arEventParams["IBLOCK_TYPE"];
							$photo_iblock_id = $arEventParams["IBLOCK_ID"];
							$alias = ($arEventParams["ALIAS"] ?? false);

							if ($EVENT_ID === "photo")
							{
								$photo_detail_url = $arEventParams["DETAIL_URL"];
								if (
									$photo_detail_url
									&& $arEvent["EVENT"]["ENTITY_TYPE"] == SONET_ENTITY_GROUP
									&& (
										IsModuleInstalled("extranet")
										|| (mb_strpos($photo_detail_url, "#GROUPS_PATH#") !== false)
									)
								)
								{
									$photo_detail_url = str_replace("#GROUPS_PATH#", $arResult["WORKGROUPS_PAGE"], $photo_detail_url);
								}
							}
							elseif ($EVENT_ID === "photo_photo")
							{
								$photo_detail_url = $arEvent["EVENT"]["URL"];
							}

							if (!$photo_detail_url)
							{
								$photo_detail_url = $arParams["PATH_TO_".($arEvent["EVENT"]["ENTITY_TYPE"] === SONET_SUBSCRIBE_ENTITY_GROUP ? "GROUP" : "USER")."_PHOTO_ELEMENT"];
							}

							if (
								$photo_iblock_type <> ''
								&& (int)$photo_iblock_id > 0
								&& (int)$photo_section_id > 0
								&& count($arPhotoItems) > 0
							)
							{
								$photo_permission = "D";
								if ($arEvent["EVENT"]["ENTITY_TYPE"] === SONET_SUBSCRIBE_ENTITY_GROUP)
								{
									if (CSocNetFeaturesPerms::CanPerformOperation($USER->GetID(), SONET_ENTITY_GROUP, $arEvent["EVENT"]["ENTITY_ID"], "photo", "write", CSocNetUser::IsCurrentUserModuleAdmin()))
									{
										$photo_permission = "W";
									}
									elseif (CSocNetFeaturesPerms::CanPerformOperation($USER->GetID(), SONET_ENTITY_GROUP, $arEvent["EVENT"]["ENTITY_ID"], "photo", "view", CSocNetUser::IsCurrentUserModuleAdmin()))
									{
										$photo_permission = "R";
									}
								}
								elseif (CSocNetFeaturesPerms::CanPerformOperation($USER->GetID(), SONET_ENTITY_USER, $arEvent["EVENT"]["ENTITY_ID"], "photo", "write", CSocNetUser::IsCurrentUserModuleAdmin()))
								{
									$photo_permission = "W";
								}
								elseif (CSocNetFeaturesPerms::CanPerformOperation($USER->GetID(), SONET_ENTITY_USER, $arEvent["EVENT"]["ENTITY_ID"], "photo", "view", CSocNetUser::IsCurrentUserModuleAdmin()))
								{
									$photo_permission = "R";
								}

								$arParams["PHOTO_COUNT"] = $arParams["PHOTO_COUNT"] ?? null;

								$APPLICATION->IncludeComponent(
									"bitrix:photogallery.detail.list.ex",
									"",
									Array(
										"IBLOCK_TYPE" => $photo_iblock_type,
										"IBLOCK_ID" => $photo_iblock_id,
										"SHOWN_PHOTOS" => (
											count($arPhotoItems) > $arParams["PHOTO_COUNT"]
												? array_slice($arPhotoItems, 0, $arParams["PHOTO_COUNT"])
												: $arPhotoItems
										),
										"DRAG_SORT" => "N",
										"MORE_PHOTO_NAV" => "N",

										//"USE_PERMISSIONS" => "N",
										"PERMISSION" => $photo_permission,

										"THUMBNAIL_SIZE" => $arParams["PHOTO_THUMBNAIL_SIZE"] ?? null,
										"SHOW_CONTROLS" => "Y",
										"USE_RATING" => (
											(
												($arParams["PHOTO_USE_RATING"] ?? '') === "Y"
												|| ($arParams["SHOW_RATING"] ?? '') === "Y"
											) ? "Y" : "N"
										),
										"SHOW_RATING" => $arParams["SHOW_RATING"],
										"SHOW_SHOWS" => "N",
										"SHOW_COMMENTS" => "Y",
										"MAX_VOTE" => $arParams["PHOTO_MAX_VOTE"] ?? '',
										"VOTE_NAMES" => $arParams["PHOTO_VOTE_NAMES"] ?? [],
										"DISPLAY_AS_RATING" => ($arParams["SHOW_RATING"] === "Y" ? "rating_main" : ($arParams["PHOTO_DISPLAY_AS_RATING"] ?? "rating")),
										"RATING_MAIN_TYPE" => ($arParams["SHOW_RATING"] === "Y" ? $arParams["RATING_TYPE"] : ""),

										"BEHAVIOUR" => "SIMPLE",
										"SET_TITLE" => "N",
										"CACHE_TYPE" => "A",
										"CACHE_TIME" => $arParams["CACHE_TIME"] ?? null,
										"CACHE_NOTES" => "",
										"SECTION_ID" => $photo_section_id,
										"ELEMENT_LAST_TYPE"	=> "none",
										"ELEMENT_SORT_FIELD" => "ID",
										"ELEMENT_SORT_ORDER" => "asc",
										"ELEMENT_SORT_FIELD1" => "",
										"ELEMENT_SORT_ORDER1" => "asc",
										"PROPERTY_CODE" => array(),

										"INDEX_URL" => CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_"
											. ($arEvent["EVENT"]["ENTITY_TYPE"] === SONET_SUBSCRIBE_ENTITY_GROUP
												? "GROUP"
												: "USER")."_PHOTO"
										] ?? null,
											array(
												"user_id" => $arEvent["EVENT"]["ENTITY_ID"],
												"group_id" => $arEvent["EVENT"]["ENTITY_ID"]
											)
										),
										"DETAIL_URL" => CComponentEngine::MakePathFromTemplate(
											$photo_detail_url,
											array(
												"user_id" => $arEvent["EVENT"]["ENTITY_ID"],
												"group_id" => $arEvent["EVENT"]["ENTITY_ID"],
											)
										),
										"GALLERY_URL" => "",
										"SECTION_URL" => CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_"
											.($arEvent["EVENT"]["ENTITY_TYPE"] === SONET_SUBSCRIBE_ENTITY_GROUP
												? "GROUP"
												: "USER")."_PHOTO_SECTION"
											] ?? null,
											array(
												"user_id" => $arEvent["EVENT"]["ENTITY_ID"],
												"group_id" => $arEvent["EVENT"]["ENTITY_ID"],
												"section_id" => ($EVENT_ID === "photo_photo" ? $photo_section_id : $arEvent["EVENT"]["SOURCE_ID"])
											)
										),
										"PATH_TO_USER" => $arParams["PATH_TO_USER"],
										"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
										"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
										"GROUP_PERMISSIONS" => array(),
										"PAGE_ELEMENTS" => $arParams["PHOTO_COUNT"],
										"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT_DETAIL"] ?? '',
										"SET_STATUS_404" => "N",
										"ADDITIONAL_SIGHTS" => array(),
										"PICTURES_SIGHT" => "real",
										"USE_COMMENTS" => $arParams["PHOTO_USE_COMMENTS"] ?? null,
										"COMMENTS_TYPE" => (($arParams["PHOTO_COMMENTS_TYPE"] ?? null) === "blog"
											? "blog"
											: "forum"
										),
										"FORUM_ID" => $arParams["PHOTO_FORUM_ID"] ?? null,
										"BLOG_URL" => $arParams["PHOTO_BLOG_URL"] ?? null,
										"USE_CAPTCHA" => $arParams["PHOTO_USE_CAPTCHA"] ?? null,
										"SHOW_LINK_TO_FORUM" => "N",
										"IS_SOCNET" => "Y",
										"USER_ALIAS" => ($alias ?: ($arEvent["EVENT"]["ENTITY_TYPE"] === SONET_SUBSCRIBE_ENTITY_GROUP ? "group" : "user")."_".$arEvent["EVENT"]["ENTITY_ID"]),
										//these two params below used to set action url and unique id - for any ajax actions
										"~UNIQUE_COMPONENT_ID" => 'bxfg_ucid_from_req_'.$photo_iblock_id.'_'.($EVENT_ID === "photo_photo" ? $photo_section_id : $arEvent["EVENT"]["SOURCE_ID"])."_".$arEvent["EVENT"]["ID"],
										"ACTION_URL" => CComponentEngine::MakePathFromTemplate(
											$arParams['PATH_TO_'
											. ($arEvent["EVENT"]["ENTITY_TYPE"] === SONET_SUBSCRIBE_ENTITY_GROUP
												? "GROUP"
												: "USER") . "_PHOTO_SECTION"] ?? null,
											[
												"user_id" => $arEvent["EVENT"]["ENTITY_ID"],
												"group_id" => $arEvent["EVENT"]["ENTITY_ID"],
												"section_id" => (
													$EVENT_ID === "photo_photo"
														? $photo_section_id
														: $arEvent["EVENT"]["SOURCE_ID"]
												)
											]
										),
									),
									$component,
									array(
										"HIDE_ICONS" => "Y"
									)
								);
							}
						}

					?></div><?php
				}
				elseif ($EVENT_ID === "tasks")
				{
					?><div
					 class="feed-post-info-block-wrap feed-post-contentview"
					 id="feed-post-contentview-<?=htmlspecialcharsBx($arResult["CONTENT_ID"])?>"
					 bx-content-view-xml-id="<?=htmlspecialcharsBx($arResult["CONTENT_ID"])?>"
					 bx-content-view-key-signed="<?= htmlspecialcharsBx($arResult['CONTENT_VIEW_KEY_SIGNED']) ?>"><?php

						?><?=$title24_2?><?php
						?><?=$arEvent["EVENT_FORMATTED"]["MESSAGE"]?><?php

					?></div><?php
				}
				elseif (in_array($EVENT_ID, array("timeman_entry", "report")))
				{
					CJSCore::Init(array('timeman'));
					?><div
					 class="feed-post-text-block feed-post-contentview"
					 id="feed-post-contentview-<?=htmlspecialcharsBx($arResult["CONTENT_ID"])?>"
					 bx-content-view-xml-id="<?=htmlspecialcharsBx($arResult["CONTENT_ID"])?>"
					 bx-content-view-key-signed="<?= htmlspecialcharsBx($arResult['CONTENT_VIEW_KEY_SIGNED']) ?>"><?php

						?><?=$title24_2?><?php
						?><?=$arEvent["EVENT_FORMATTED"]["MESSAGE"]?><?php

					?></div><?php
				}
				elseif ($arEvent["EVENT_FORMATTED"]["MESSAGE"] <> '') // all other events
				{
					?><div class="feed-post-text-block" id="log_entry_outer_<?= $arEvent['EVENT']['ID'] ?>"><?php

						$classNameList = [ 'feed-post-contentview' ];
						if ($arParams["FROM_LOG"] === "Y")
						{
							$classNameList[] = 'feed-post-text-block-inner';
						}

						if ($arResult["CONTENT_ID"])
						{
							?><div
							 class="<?=implode(' ', $classNameList)?>"
							 id="feed-post-text-contentview-<?=htmlspecialcharsBx($arResult["CONTENT_ID"])?>"
							 bx-content-view-xml-id="<?=htmlspecialcharsBx($arResult["CONTENT_ID"])?>"
							 bx-content-view-key-signed="<?= htmlspecialcharsBx($arResult['CONTENT_VIEW_KEY_SIGNED']) ?>"><?php
								?><div class="feed-post-text-block-inner-inner" id="log_entry_body_<?=$arEvent["EVENT"]["ID"]?>"><?php

									?><?=$title24_2?><?php
									?><?=$arEvent["EVENT_FORMATTED"]["MESSAGE"]?><?php

							?></div><?php
							?></div><?php
						}
						else
						{
							?><div class="<?=implode(' ', $classNameList)?>"><?php
								?><div class="feed-post-text-block-inner-inner" id="log_entry_body_<?=$arEvent["EVENT"]["ID"]?>"><?php

									?><?=$title24_2?><?php
									?><?=$arEvent["EVENT_FORMATTED"]["MESSAGE"]?><?php

								?></div><?php
							?></div><?php
						}

						if ($arParams["FROM_LOG"] === 'Y')
						{
							?><div class="feed-post-text-more" id="log_entry_more_<?=$arEvent["EVENT"]["ID"]?>"><?php
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

									BX.Livefeed.FeedInstance.addMoreButton(
										'log_entry_<?= $arEvent['EVENT']['ID'] ?>',
										{
											outerBlockID : 'log_entry_outer_<?= $arEvent['EVENT']['ID'] ?>',
											bodyBlockID : 'log_entry_body_<?= $arEvent['EVENT']['ID'] ?>',
											informerBlockID: 'log_entry_inform_<?= $arEvent['EVENT']['ID'] ?>',
										}
									);
								});
							</script><?php
						}
					?></div><?php
				}

				if (
					is_array($arEvent["EVENT_FORMATTED"]["UF"])
					&& count($arEvent["EVENT_FORMATTED"]["UF"]) > 0
				)
				{
					$eventHandlerID = AddEventHandler("main", "system.field.view.file", Array("CSocNetLogTools", "logUFfileShow"));
					foreach ($arEvent["EVENT_FORMATTED"]["UF"] as $FIELD_NAME => $arUserField)
					{
						if (!empty($arUserField["VALUE"]))
						{
							$APPLICATION->IncludeComponent(
								"bitrix:system.field.view",
								$arUserField["USER_TYPE"]["USER_TYPE_ID"],
								array(
									"arUserField" => $arUserField,
									"LAZYLOAD" => $arParams["LAZYLOAD"],
									"GRID" => "Y",
									"USE_TOGGLE_VIEW" => ($arResult["isCurrentUserEventOwner"] ? 'Y' : 'N')
								),
								null,
								array("HIDE_ICONS"=>"Y")
							);
						}
					}
					if (
						$eventHandlerID !== false
						&& $eventHandlerID > 0
					)
					{
						RemoveEventHandler('main', 'system.field.view.file', $eventHandlerID);
					}
				}

				if (
					!in_array($arEvent["EVENT"]["EVENT_ID"], [ 'tasks', 'crm_activity_add' ], true)
					&& !empty($arEvent["TAGS"])
					&& is_array($arEvent["TAGS"])
				)
				{
					?><div class="feed-com-tags-block"><noindex>
						<div class="feed-com-files-title"><?=Loc::getMessage("SONET_C30_TAGS")?></div>
						<div class="feed-com-files-cont" id="logentry-tags-<?= (int)$arEvent["EVENT"]["ID"] ?>"><?php
							$i = 0;
							foreach ($arEvent["TAGS"] as $v)
							{
								if ($i!=0)
								{
									echo ",";
								}

								?> <a href="<?=$v["URL"]?>" rel="nofollow" class="feed-com-tag" bx-tag-value="<?=htmlspecialcharsbx($v["NAME"])?>"><?=htmlspecialcharsEx($v["NAME"])?></a><?php
								$i++;
							}
							?></div>
					</noindex></div><?php
				}

				// Used to display some HTML before informers
				if (($arEvent["EVENT_FORMATTED"]["FOOTER_MESSAGE"] ?? '') !== '')
				{
					echo $arEvent["EVENT_FORMATTED"]["FOOTER_MESSAGE"];
				}

				$tplID = 'SOCCOMMENT_'.$arEvent["COMMENTS_PARAMS"]["ENTITY_XML_ID"].'_';
				$separatorClassName = "feed-post-informers";

				?><div class="<?=$separatorClassName?>" id="log_entry_inform_<?= (int)$arEvent["EVENT"]["ID"] ?>"><div class="feed-post-informers-cont"><?php

					$voteId = false;
					if (
						$arParams["SHOW_RATING"] === "Y"
						&& $arEvent["EVENT"]["RATING_TYPE_ID"] !== ''
						&& (int)$arEvent["EVENT"]["RATING_ENTITY_ID"] > 0
					)
					{
						$voteId = $arEvent["EVENT"]["RATING_TYPE_ID"].'_'.$arEvent["EVENT"]["RATING_ENTITY_ID"].'-'.(time() + random_int(0, 1000));
						$emotion = (isset($arEvent["RATING"]) && !empty($arEvent["RATING"]["USER_REACTION"])? mb_strtoupper($arEvent["RATING"]["USER_REACTION"]) : 'LIKE');

						if ($arResult["bIntranetInstalled"])
						{
							$likeClassList = [
								'feed-inform-item',
								'bx-ilike-left-wrap'
							];

							if (
								isset($arEvent["RATING"]["USER_HAS_VOTED"])
								&& $arEvent["RATING"]["USER_HAS_VOTED"] === "Y"
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
									"ENTITY_TYPE_ID" => $arEvent["EVENT"]["RATING_TYPE_ID"],
									"ENTITY_ID" => $arEvent["EVENT"]["RATING_ENTITY_ID"],
									"OWNER_ID" => $arEvent["CREATED_BY"]["TOOLTIP_FIELDS"]["ID"],
									"USER_VOTE" => $arEvent["RATING"]["USER_VOTE"],
									"USER_REACTION" => $arEvent["RATING"]["USER_REACTION"],
									"USER_HAS_VOTED" => $arEvent["RATING"]["USER_HAS_VOTED"],
									"TOTAL_VOTES" => $arEvent["RATING"]["TOTAL_VOTES"],
									"TOTAL_POSITIVE_VOTES" => $arEvent["RATING"]["TOTAL_POSITIVE_VOTES"],
									"TOTAL_NEGATIVE_VOTES" => $arEvent["RATING"]["TOTAL_NEGATIVE_VOTES"],
									"TOTAL_VALUE" => $arEvent["RATING"]["TOTAL_VALUE"],
									"PATH_TO_USER_PROFILE" => $arParams["PATH_TO_USER"],
								),
								$component,
								array("HIDE_ICONS" => "Y")
							);
							?></span><?php
						}
					}

					if (
						isset($arEvent['HAS_COMMENTS'], $arEvent['CAN_ADD_COMMENTS'])
						&& $arEvent['HAS_COMMENTS'] === 'Y'
						&& $arEvent['CAN_ADD_COMMENTS'] === 'Y'
					)
					{
						$bHasComments = true;
						?><span class="feed-inform-comments"><?php
							?><a href="javascript:void(0);" onclick="BX('feed_comments_block_<?=$arEvent["EVENT"]["ID"]?>').style.display = 'block'; __logShowCommentForm('<?=$arEvent["COMMENTS_PARAMS"]["ENTITY_XML_ID"]?>')"><?=GetMessage("SONET_C30_COMMENT_ADD")?></a><?php
						?></span><?php

						?><div class="feed-inform-item feed-inform-comments feed-inform-comments-pinned">
							<?=Loc::getMessage('SONET_C30_PINNED_COMMENTS')?>
							<span class="feed-inform-comments-pinned-all"><?=$arResult['ALL_COMMENTS_COUNT']?></span>
							<span class="feed-inform-comments-pinned-old"><?=$arResult['ALL_COMMENTS_COUNT'] - $arResult['NEW_COMMENTS_COUNT']?></span><?php
							$classNameList = [ 'feed-inform-comments-pinned-new' ];
							if ($arResult['NEW_COMMENTS_COUNT'] > 0)
							{
								$classNameList[] = 'feed-inform-comments-pinned-new-active';
							}
							?><span class="<?= implode(' ', $classNameList) ?>"><?php
								?><svg width="6" height="6" viewBox="0 0 6 6" fill="none" xmlns="http://www.w3.org/2000/svg"><?php
									?><path opacity="0.840937" d="M3.36051 5.73145V3.76115H5.33081V2.70174H3.36051V0.731445H2.30111V2.70174H0.330811V3.76115H2.30111V5.73145H3.36051Z" fill="white"></path><?php
								?></svg><?php
								?><span class="feed-inform-comments-pinned-new-value"><?=$arResult['NEW_COMMENTS_COUNT']?></span><?php
							?></span><?php
						?></div><?php
					}
					else
					{
						$bHasComments = false;
					}

					if (
						$bHasComments
						&& array_key_exists("FOLLOW", $arEvent["EVENT"])
					)
					{
						?><span class="feed-inform-item feed-inform-follow" data-follow="<?= ($arEvent["EVENT"]["FOLLOW"] === "Y" ? "Y" : "N") ?>" id="log_entry_follow_<?=(int)$arEvent["EVENT"]["ID"]?>"onclick="__logSetFollow(<?=$arEvent["EVENT"]["ID"]?>)"><a href="javascript:void(0);"><?=GetMessage("SONET_LOG_T_FOLLOW_" . ($arEvent["EVENT"]["FOLLOW"] === "Y" ? "Y" : "N")) ?></a></span><?php
					}

					if ($USER->IsAuthorized())
					{
						if (
							is_set($arEvent)
							&& is_set($arEvent["MENU"])
							&& is_array($arEvent["MENU"])
							&& !empty($arEvent["MENU"])
						)
						{
							$arMenuItemsAdditional = $arEvent["MENU"];
						}
						else
						{
							$arMenuItemsAdditional = array();
						}

						$serverName = (CMain::IsHTTPS() ? "https" : "http")."://".Bitrix\Main\Text\HtmlFilter::encode((defined("SITE_SERVER_NAME") && SITE_SERVER_NAME <> '') ? SITE_SERVER_NAME : COption::GetOptionString("main", "server_name", ""));
						$strLogEntryURL = $serverName.CComponentEngine::MakePathFromTemplate(
							$arParams["PATH_TO_LOG_ENTRY"],
							array(
								"log_id" => $arEvent["EVENT"]["ID"]
							)
						);

						?><a
							id="feed-logentry-menuanchor-<?=$arEvent["EVENT"]["ID"]?>"
							href="#"
							data-log-entry-url="<?=$strLogEntryURL?>"
							data-log-entry-createtask="<?= (($arResult["canGetPostContent"] ?? null) && $arResult["bTasksAvailable"] && !$stub ? 'Y' : 'N') ?>"
							data-log-entry-entity-type="<?= (!empty($arResult["POST_CONTENT_TYPE_ID"]) ? htmlspecialcharsbx($arResult["POST_CONTENT_TYPE_ID"]) : "") ?>"
							data-log-entry-entity-id="<?=(!empty($arResult["POST_CONTENT_ID"]) ? (int)$arResult["POST_CONTENT_ID"] : "")?>"
							data-log-entry-log-id="<?=(int)$arEvent["EVENT"]["ID"]?>"
							data-log-entry-favorites="<?= (isset($arEvent['FAVORITES']) && $arEvent["FAVORITES"] === "Y" ? 'Y' : 'N') ?>"
							data-log-entry-items="<?= htmlspecialcharsbx(\Bitrix\Main\Web\Json::encode($arMenuItemsAdditional)) ?>"
							onclick="BX.Livefeed.Post.showMenu({
								bindElement: this,
								menuElement: this,
								ind: '<?= $ind ?>',
								entity_type: '<?= $arEvent["EVENT"]["ENTITY_TYPE"] ?>',
								entity_id: <?=$arEvent["EVENT"]["ENTITY_ID"] ?>,
								event_id: '<?=$arEvent["EVENT"]["EVENT_ID"] ?>',
								fullset_event_id: <?= ($arEvent["EVENT"]["EVENT_ID_FULLSET"] ? "'" . $arEvent["EVENT"]["EVENT_ID_FULLSET"] . "'" : "false") ?>,
								user_id: '<?=$arEvent["EVENT"]["USER_ID"] ?>',
								log_id: '<?=$arEvent["EVENT"]["ID"] ?>',
								bFavotites: <?= (array_key_exists("FAVORITES", $arEvent) && $arEvent["FAVORITES"] === "Y" ? "true" : "false") ?>,
								arMenuItemsAdditional: <?= CUtil::PhpToJSObject($arMenuItemsAdditional) ?>,
							}); return BX.PreventDefault(this);"
							class="feed-inform-item feed-post-more-link"><span class="feed-post-more-text"><?=GetMessage("SONET_LOG_T_BUTTON_MORE")?></span><span class="feed-post-more-arrow"></span></a><?php
					}

					?><span class="feed-inform-item feed-post-time-wrap feed-inform-contentview"><?php
						if (
							$arParams["PUBLIC_MODE"] !== 'Y'
							&& isset($arResult["CONTENT_ID"])
						)
						{
							$APPLICATION->IncludeComponent(
								"bitrix:socialnetwork.contentview.count", "",
								Array(
									"CONTENT_ID" => $arResult["CONTENT_ID"],
									"CONTENT_VIEW_CNT" => ($arResult["CONTENT_VIEW_CNT"] ?? 0),
									"PATH_TO_USER_PROFILE" => $arParams["PATH_TO_USER"]
								),
								$component,
								array("HIDE_ICONS" => "Y")
							);
						}
					?></span><?php

					if (
						$arResult["bIntranetInstalled"]
						&& $arParams["SHOW_RATING"] === "Y"
						&& $arEvent["EVENT"]["RATING_TYPE_ID"] <> ''
						&& (int)$arEvent["EVENT"]["RATING_ENTITY_ID"] > 0
					)
					{
						?><div class="feed-post-emoji-top-panel-outer"><?php
							$classNameList = [
								'feed-post-emoji-top-panel-box'
							];

							if ((int) ($arEvent["RATING"]["TOTAL_POSITIVE_VOTES"] ?? 0) > 0)
							{
								$classNameList[] = 'feed-post-emoji-top-panel-container-active';
							}

							?><div id="feed-post-emoji-top-panel-container-<?= htmlspecialcharsbx($voteId) ?>" class="<?= implode(' ', $classNameList) ?>"><?php
								$APPLICATION->IncludeComponent(
									"bitrix:rating.vote",
									"like_react",
									array(
										"ENTITY_TYPE_ID" => $arEvent["EVENT"]["RATING_TYPE_ID"],
										"ENTITY_ID" => $arEvent["EVENT"]["RATING_ENTITY_ID"],
										"OWNER_ID" => ($arEvent["CREATED_BY"]["TOOLTIP_FIELDS"]["ID"] ?? 0),
										"USER_VOTE" => $arEvent["RATING"]["USER_VOTE"] ?? '',
										"USER_REACTION" => $arEvent["RATING"]["USER_REACTION"] ?? '',
										"USER_HAS_VOTED" => $arEvent["RATING"]["USER_HAS_VOTED"] ?? '',
										"TOTAL_VOTES" => $arEvent["RATING"]["TOTAL_VOTES"] ?? '',
										"TOTAL_POSITIVE_VOTES" => $arEvent["RATING"]["TOTAL_POSITIVE_VOTES"] ?? '',
										"TOTAL_NEGATIVE_VOTES" => $arEvent["RATING"]["TOTAL_NEGATIVE_VOTES"] ?? '',
										"TOTAL_VALUE" => $arEvent["RATING"]["TOTAL_VALUE"] ?? '',
										"REACTIONS_LIST" => $arEvent["RATING"]["REACTIONS_LIST"] ?? [],
										"PATH_TO_USER_PROFILE" => $arParams["PATH_TO_USER"],
										'TOP_DATA' => (!empty($arResult['TOP_RATING_DATA']) ? $arResult['TOP_RATING_DATA'] : false),
										'VOTE_ID' => $voteId
									),
									$component,
									array("HIDE_ICONS" => "Y")
								);
							?></div><?php
						?></div><?php
					}
				?></div><?php // feed-post-informers

				if (($_REQUEST["action"] ?? '') === "get_entry")
				{
					$strEntryText = ob_get_clean();

					echo CUtil::PhpToJSObject(array(
						"ENTRY_HTML" => $strEntryText
					));
					die();
				}

			?></div></div><?php // cont_wrap

			if (
				isset($arEvent["HAS_COMMENTS"])
				&& $arEvent["HAS_COMMENTS"] === "Y"
			)
			{
				?><div class="feed-comments-block-wrap"><?php
/*
				if (
					isset($arParams['TASK_RESULT_TASK_ID'])
					&& (int)$arParams['TASK_RESULT_TASK_ID'] > 0
					&& \Bitrix\Main\ModuleManager::isModuleInstalled('tasks')
				)
				{
					$APPLICATION->IncludeComponent(
						'bitrix:tasks.widget.result',
						'.default',
						[
							'TASK_ID' => (int)$arParams['TASK_RESULT_TASK_ID'],
							'SHOW_RESULT_FIELD' => 'N',
						],
					);
				}
*/
				?><script>
					BX.viewElementBind(
						'feed_comments_block_<?=$arEvent["EVENT"]["ID"]?>',
						{},
						function(node){
							return BX.type.isElementNode(node) && (node.getAttribute('data-bx-viewer') || node.getAttribute('data-bx-image'));
						}
					);
					top.postFollow<?= ($arParams["ID"] ?? '') ?> = postFollow<?= ($arParams["ID"] ?? '') ?> = '<?= ($arParams["FOLLOW"] ?? '') ?>';
				</script><?php

				$arRecords = [];
				if (!!$component && $component->__parent && $component->__parent->arResult)
				{
					$component->__parent->arResult["ENTITIES_XML_ID"] = ($component->__parent->arResult["ENTITIES_XML_ID"] ?? []);
					$component->__parent->arResult["ENTITIES_XML_ID"][$arEvent["COMMENTS_PARAMS"]["ENTITY_XML_ID"]] = array($arEvent["COMMENTS_PARAMS"]["ENTITY_TYPE"], $arEvent["EVENT"]["SOURCE_ID"]);
					$component->__parent->arResult["ENTITIES_CORRESPONDENCE"] = ($component->__parent->arResult["ENTITIES_CORRESPONDENCE"] ?: []);
					$component->__parent->arResult["ENTITIES_CORRESPONDENCE"][$arEvent["COMMENTS_PARAMS"]["ENTITY_XML_ID"]."-0"] = array($arEvent["EVENT"]["ID"], 0);
				}

				$commentRatingEntityTypeId = false;
				if (!empty($arEvent["COMMENTS"]))
				{
					foreach ($arEvent["COMMENTS"] as $key => $arComment)
					{
						$commentId = (int)($arComment["EVENT"]["SOURCE_ID"] ?: $arComment["EVENT"]["ID"]);

						if (
							($arResult['RESULT'] ?? 0)
							&& (int)$arComment["EVENT"]["ID"] === ($arResult['RESULT'] ?? 0)
						)
						{
							$arResult['RESULT'] = $commentId;
						}

						if (!!$component && !!$component->__parent && !!$component->__parent->arResult)
						{
							$component->__parent->arResult["ENTITIES_CORRESPONDENCE"][$arEvent["COMMENTS_PARAMS"]["ENTITY_XML_ID"]."-".$commentId] = [
								$arEvent["EVENT"]["ID"],
								$arComment["EVENT"]["ID"]
							];
						}

						$event_date_log_ts = ($arComment["EVENT"]["LOG_DATE_TS"] ?? (MakeTimeStamp($arComment["EVENT"]["LOG_DATE"]) - (int)$arResult["TZ_OFFSET"]));
						$isNew = (
							$USER->isAuthorized()
							&& ($arEvent['EVENT']['FOLLOW'] ?? null) !== "N"
							&& (int)$arComment['EVENT']['USER_ID'] !== (int)$USER->getId()
							&& (int)$arResult['LAST_LOG_TS'] > 0
							&& $event_date_log_ts > $arResult['LAST_LOG_TS']
							&& (
								$arResult['COUNTER_TYPE'] === "**"
								|| $arResult['COUNTER_TYPE'] === "CRM_**"
								|| $arResult['COUNTER_TYPE'] === "blog_post"
							)
							&& (
								!is_array($arParams['UNREAD_COMMENTS_ID_LIST'] ?? null)
								|| in_array((int)$arComment['EVENT']['ID'], $arParams['UNREAD_COMMENTS_ID_LIST'], true)
							)
						);

						$arRecords[$commentId] = [
							"ID" => $commentId,
							'NEW' => ($isNew ? 'Y' : 'N'),
							"AUTHOR" => [
								"ID" => $arComment["CREATED_BY"]["TOOLTIP_FIELDS"]["ID"],
								"NAME" => $arComment["CREATED_BY"]["TOOLTIP_FIELDS"]["NAME"],
								"LAST_NAME" => $arComment["CREATED_BY"]["TOOLTIP_FIELDS"]["LAST_NAME"],
								"SECOND_NAME" => $arComment["CREATED_BY"]["TOOLTIP_FIELDS"]["SECOND_NAME"],
								"LOGIN" => $arComment["CREATED_BY"]["TOOLTIP_FIELDS"]["LOGIN"],
								"PERSONAL_GENDER" => $arComment["CREATED_BY"]["TOOLTIP_FIELDS"]["PERSONAL_GENDER"],
								"AVATAR" => $arComment["AVATAR_SRC"],
								"EXTERNAL_AUTH_ID" => ($arComment["CREATED_BY"]["TOOLTIP_FIELDS"]["EXTERNAL_AUTH_ID"] ?? false),
								"UF_USER_CRM_ENTITY" => ($arComment["CREATED_BY"]["TOOLTIP_FIELDS"]["UF_USER_CRM_ENTITY"] ?? false)
							],
							"APPROVED" => "Y",
							"POST_TIMESTAMP" => $arComment["LOG_DATE_TS"],
							'POST_MESSAGE_TEXT' => ($arComment['EVENT_FORMATTED']['FULL_MESSAGE_CUT'] ?? ''),
							"~POST_MESSAGE_TEXT" => (htmlspecialcharsback(($arComment['EVENT']['MESSAGE'] ?? '')) ?? ''),
							"AUX" => (!empty($arComment["AUX"]) ? $arComment["AUX"] : ''),
							"AUX_LIVE_PARAMS" => (!empty($arComment["AUX_LIVE_PARAMS"]) ? $arComment["AUX_LIVE_PARAMS"] : []),
							"CAN_DELETE" => (!empty($arComment["CAN_DELETE"]) ? $arComment["CAN_DELETE"] : 'Y'),
							"CLASSNAME" => "",
						];

						if (
							(string)$arComment["EVENT"]["RATING_TYPE_ID"] !== ''
							&& $arComment["EVENT"]["RATING_ENTITY_ID"] > 0
							&& $arParams["SHOW_RATING"] === "Y"
						)
						{
							if (!$commentRatingEntityTypeId)
							{
								$commentRatingEntityTypeId = $arComment["EVENT"]["RATING_TYPE_ID"];
							}

							if ($arResult["bIntranetInstalled"])
							{
								ob_start();

								$RATING_ENTITY_ID = $arComment["EVENT"]["RATING_ENTITY_ID"];
								$voteId = $arComment["EVENT"]["RATING_TYPE_ID"].'_'.$RATING_ENTITY_ID.'-'.(time() + random_int(0, 1000));

								$arRecords[$commentId]["RATING_VOTE_ID"] = $voteId;
								$arRecords[$commentId]["RATING_USER_HAS_VOTED"] = ($arResult["RATING_COMMENTS"][$RATING_ENTITY_ID]["USER_HAS_VOTED"] ?? '');
								$arRecords[$commentId]["RATING_USER_REACTION"] = ($arResult["RATING_COMMENTS"][$RATING_ENTITY_ID]["USER_REACTION"] ?? '');

								$APPLICATION->IncludeComponent(
									"bitrix:rating.vote",
									"like_react",
									Array(
										"COMMENT" => "Y",
										"ENTITY_TYPE_ID" => $arComment["EVENT"]["RATING_TYPE_ID"],
										"ENTITY_ID" => $RATING_ENTITY_ID,
										"OWNER_ID" => $arComment["CREATED_BY"]["TOOLTIP_FIELDS"]["ID"],
										"USER_VOTE" => $arResult["RATING_COMMENTS"][$RATING_ENTITY_ID]["USER_VOTE"] ?? '',
										"USER_REACTION" => $arResult["RATING_COMMENTS"][$RATING_ENTITY_ID]["USER_REACTION"] ?? '',
										"USER_HAS_VOTED" => $arResult["RATING_COMMENTS"][$RATING_ENTITY_ID]["USER_HAS_VOTED"] ?? '',
										"TOTAL_VOTES" => $arResult["RATING_COMMENTS"][$RATING_ENTITY_ID]["TOTAL_VOTES"] ?? '',
										"TOTAL_POSITIVE_VOTES" => $arResult["RATING_COMMENTS"][$RATING_ENTITY_ID]["TOTAL_POSITIVE_VOTES"] ?? '',
										"TOTAL_NEGATIVE_VOTES" => $arResult["RATING_COMMENTS"][$RATING_ENTITY_ID]["TOTAL_NEGATIVE_VOTES"] ?? '',
										"TOTAL_VALUE" => $arResult["RATING_COMMENTS"][$RATING_ENTITY_ID]["TOTAL_VALUE"] ?? '',
										"REACTIONS_LIST" => $arResult["RATING_COMMENTS"][$RATING_ENTITY_ID]["REACTIONS_LIST"] ?? [],
										"PATH_TO_USER_PROFILE" => $arParams["PATH_TO_USER"],
										"VOTE_ID" => $voteId,
									),
									$component,
									array("HIDE_ICONS" => "Y")
								);

								$APPLICATION->AddViewContent(implode('_', array($tplID, 'ID', $commentId, "LIKE_REACT")), ob_get_clean(), 100);
							}
							else
							{
								ob_start();
								$RATING_ENTITY_ID = $arComment["EVENT"]["RATING_ENTITY_ID"];

								?><span class="sonet-log-comment-like rating_vote_text"><?php
								$APPLICATION->IncludeComponent(
									"bitrix:rating.vote",
									$arParams["RATING_TYPE"],
									Array(
										"ENTITY_TYPE_ID" => $arComment["EVENT"]["RATING_TYPE_ID"],
										"ENTITY_ID" => $RATING_ENTITY_ID,
										"OWNER_ID" => $arComment["CREATED_BY"]["TOOLTIP_FIELDS"]["ID"],
										"USER_VOTE" => $arResult["RATING_COMMENTS"][$RATING_ENTITY_ID]["USER_VOTE"],
										"USER_HAS_VOTED" => $arResult["RATING_COMMENTS"][$RATING_ENTITY_ID]["USER_HAS_VOTED"],
										"TOTAL_VOTES" => $arResult["RATING_COMMENTS"][$RATING_ENTITY_ID]["TOTAL_VOTES"],
										"TOTAL_POSITIVE_VOTES" => $arResult["RATING_COMMENTS"][$RATING_ENTITY_ID]["TOTAL_POSITIVE_VOTES"],
										"TOTAL_NEGATIVE_VOTES" => $arResult["RATING_COMMENTS"][$RATING_ENTITY_ID]["TOTAL_NEGATIVE_VOTES"],
										"TOTAL_VALUE" => $arResult["RATING_COMMENTS"][$RATING_ENTITY_ID]["TOTAL_VALUE"],
										"PATH_TO_USER_PROFILE" => $arParams["PATH_TO_USER"],
									),
									$component,
									array("HIDE_ICONS" => "Y")
								);
								?></span><?php
								$APPLICATION->AddViewContent(implode('_', array($tplID, 'ID', $commentId, "BEFORE_ACTIONS")), ob_get_clean(), 50);
							}
						}

						if (
							!empty($arComment['UF'])
							&& is_array($arComment['UF'])
						)
						{
							$arRecords[$commentId]['UF'] = $arComment['UF'];
						}

						static $request = null;
						if ($request === null)
						{
							$request = \Bitrix\Main\Context::getCurrent()->getRequest();
						}

						if (
							isset($arComment['UF_HIDDEN']['UF_SONET_COM_URL_PRV'])
							&& $request->getPost('ACTION') === 'GET'
							&& $request->getPost('MODE') === 'RECORD'
						)
						{
							$arRecords[$commentId]['UF']['UF_SONET_COM_URL_PRV'] = $arComment['UF_HIDDEN']['UF_SONET_COM_URL_PRV'];
						}

						if (
							!empty($arComment["EVENT_FORMATTED"]["URLPREVIEW"])
							&& $arComment["EVENT_FORMATTED"]["URLPREVIEW"] === true
						)
						{
							$arRecords[$commentId]["CLASSNAME"] .= " feed-com-block-urlpreview";
						}
					}
				}

				$tmp = $nav_result = null;
				if (($arEvent["COMMENTS_COUNT"] - count($arRecords)) > 0)
				{
					$tmp = reset($arEvent["COMMENTS"]);
				}

				$commentUrl = $arResult["COMMENT_URL"];
				if (empty($arResult['COMMENT_URL']))
				{
					$commentUrl = (
						isset($arParams['PATH_TO_LOG_ENTRY'])
						&& (string)$arParams['PATH_TO_LOG_ENTRY'] !== ''
							? CComponentEngine::MakePathFromTemplate($arParams['PATH_TO_LOG_ENTRY'], [
								'log_id' => $arEvent["EVENT"]["ID"],
							]) . '?commentId=#ID#'
							: ''
					);
				}

				$commentUrl = (new Uri($commentUrl))->deleteParams([
					'sessid',
					'AJAX_POST',
					'ENTITY_XML_ID',
					'ENTITY_TYPE',
					'ENTITY_ID',
					'REVIEW_ACTION',
					'ACTION',
					'MODE',
					'FILTER',
					'result',
				]);

				$canComment = (
					isset($arEvent['HAS_COMMENTS'], $arEvent['CAN_ADD_COMMENTS'])
					&& $arEvent['CAN_ADD_COMMENTS'] === 'Y'
				);

				$taskId = 0;

				if (
					($isTaskCommentsFeed = preg_match('/^TASK_(\d+)$/i', $arEvent["COMMENTS_PARAMS"]["ENTITY_XML_ID"], $matches))
					&& $arResult['bTasksInstalled']
				)
				{
					$taskId = (int)$matches[1];
//					$tasksResultManager = new \Bitrix\Tasks\Internals\Task\Result\ResultManager((int)$USER->getId());
//					$commentsAsResult = $tasksResultManager->getTaskResults();
				}

				$arResult["OUTPUT_LIST"] = $APPLICATION->IncludeComponent(
					"bitrix:main.post.list",
					"",
					array(
						"bPublicPage" => (isset($arParams["PUBLIC_MODE"]) && $arParams["PUBLIC_MODE"] === "Y"),
						"TEMPLATE_ID" => $tplID,
						"CONTENT_TYPE_ID" => ($commentRatingEntityTypeId ?: ""),
						"ENTITY_XML_ID" => $arEvent["COMMENTS_PARAMS"]["ENTITY_XML_ID"],
						"POST_CONTENT_TYPE_ID" => $arResult["POST_CONTENT_TYPE_ID"],
						"COMMENT_CONTENT_TYPE_ID" => $arResult["COMMENT_CONTENT_TYPE_ID"],
						"RECORDS" => array_reverse($arRecords, true),
						"NAV_STRING" => ($tmp ? str_replace('#log_id#', (int)$arEvent["EVENT"]["ID"], $arParams['PATH_TO_LOG_ENTRY']) : ''),
						"NAV_RESULT" => $arResult['NAV_RESULT'],
						"PREORDER" => "N",
						"RIGHTS" => array(
							"MODERATE" => "N",
							"EDIT" => $arResult["COMMENT_RIGHTS_EDIT"],
							"DELETE" => $arResult["COMMENT_RIGHTS_DELETE"],
							"CREATETASK" => (
								$arResult['bTasksAvailable']
								&& $arResult['canGetCommentContent']
									? 'Y'
									: 'N'
							),
							'CREATESUBTASK' => (
								$arResult['bTasksAvailable']
								&& $arResult['canGetCommentContent']
								&& $isTaskCommentsFeed
									? 'Y'
									: 'N'
							)
						),
						"VISIBLE_RECORDS_COUNT" => count($arRecords),

						"ERROR_MESSAGE" => $arResult["ERROR_MESSAGE"] ?? '',
						"OK_MESSAGE" => $arResult["OK_MESSAGE"] ?? '',
						"RESULT" => $arResult["RESULT"] ?? '',
						"PUSH&PULL" => array (
							"ACTION" => $arResult['PUSH&PULL_ACTION'] ?? '',
							"ID" => $arResult["RESULT"] ?? ''
						),
						"VIEW_URL" => $commentUrl->getUri(),
						"MODERATE_URL" => "",
						"DELETE_URL" => '/bitrix/components/bitrix/socialnetwork.log.entry/ajax.php?lang='.LANGUAGE_ID.'&action=delete_comment&delete_comment_id=#ID#&post_id='.$arEvent["EVENT"]["ID"].'&site='.SITE_ID,
						"AUTHOR_URL" => ($arParams["PUBLIC_MODE"] === "Y" ? "javascript:void(0);" : $arParams["PATH_TO_USER"]),

						"AVATAR_SIZE" => $arParams["AVATAR_SIZE_COMMENT"],
						"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
						"SHOW_LOGIN" => $arParams['SHOW_LOGIN'],

						"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT"],
						"DATE_TIME_FORMAT_WITHOUT_YEAR" => $arParams["DATE_TIME_FORMAT_WITHOUT_YEAR"],
						"LAZYLOAD" => $arParams["LAZYLOAD"],

						"NOTIFY_TAG" => $arEvent["COMMENTS_PARAMS"]["NOTIFY_TAGS"],
						"NOTIFY_TEXT" => TruncateText(str_replace(Array("\r\n", "\n"), " ", $arEvent["EVENT"]["MESSAGE"]), 100),
						"SHOW_MINIMIZED" => "Y",
						"SHOW_POST_FORM" => ($canComment ? 'Y' : 'N'),
						"IMAGE_SIZE" => $arParams["IMAGE_SIZE"] ?? 0,
						"mfi" => $arParams["mfi"] ?? '',
						"AUTHOR_URL_PARAMS" => [
							'entityType' => 'LOG_ENTRY',
							'entityId' => $arEvent['EVENT']['ID'],
						],
						'FORM_ID' => ($canComment ? ($arParams['FORM_ID'] ?? '') : ''),
					),
					$this->__component
				);

				$blockClassName = "feed-comments-block";
				if (
					!empty($arResult["OUTPUT_LIST"]["DATA"])
					&& !empty($arResult["OUTPUT_LIST"]["DATA"]["NAV_STRING_COUNT_MORE"])
					&& (int)$arResult["OUTPUT_LIST"]["DATA"]["NAV_STRING_COUNT_MORE"] > 0
				)
				{
					$blockClassName .= " feed-comments-block-nav";
				}

				?><div
				 class="<?=$blockClassName?>"
				 id="feed_comments_block_<?=$arEvent["EVENT"]["ID"]?>"
				 data-bx-comments-entity-xml-id="<?= \Bitrix\Main\Text\HtmlFilter::encode($arEvent["COMMENTS_PARAMS"]["ENTITY_XML_ID"]) ?>"
				 data-bx-follow="<?=(($arEvent['EVENT']['FOLLOW'] ?? null) === 'Y' ? 'Y' : 'N')?>"
				><?php
					?><?=$arResult["OUTPUT_LIST"]["HTML"]?><?php
					?><script>
						BX.ready(function(){
							BX.UserContentView.init();
							BX.SocialnetworkLogEntry.registerViewAreaList({
								containerId: 'feed_comments_block_<?=$arEvent["EVENT"]["ID"]?>',
								className: 'feed-com-text-inner',
								fullContentClassName: 'feed-com-text-inner-inner'
							});

							__logCommentsListRedefine("<?=$arEvent["COMMENTS_PARAMS"]["ENTITY_XML_ID"]?>", "sonet_log_day_item_<?= $ind ?>", "anchor_<?=CUtil::JSEscape($anchor_id)?>", "<?=$arEvent["EVENT"]["ID"]?>");
							<?php

							if (
								$USER->IsAuthorized()
								&& CModule::IncludeModule("pull")
								&& CPullOptions::GetNginxStatus()
							)
							{
								?>
								BX.Event.EventEmitter.incrementMaxListeners('OnUCCommentWasPulled');
								BX.addCustomEvent(window, "OnUCCommentWasPulled", function(id) { if (id && id[0] == '<?=$arEvent["COMMENTS_PARAMS"]["ENTITY_XML_ID"]?>') { BX.show(BX('feed_comments_block_<?=$arEvent["EVENT"]["ID"]?>')); } });
								<?php
							}
							?>
						});

					</script><?php
					?><div class="feed-com-corner"></div>
				</div><?php
				?></div><?php // feed-comments-block-wrap
			}

			?><div class="feed-post-right-top-corner"><?php

				if ($USER->IsAuthorized())
				{
					$pinned = (
						!empty($arParams['PINNED_PANEL_DATA'])
						|| (isset($arParams['EVENT']['PINNED']) && $arParams['EVENT']['PINNED'] === 'Y')
					);

					?><a href="#" class="feed-post-pinned-link feed-post-pinned-link-collapse"><?=Loc::getMessage('SONET_C30_FEED_PINNED_COLLAPSE')?></a><?php
					?><div id="feed-logentry-menuanchor-right-<?=$arEvent["EVENT"]["ID"]?>" class="feed-post-right-top-menu"></div><?php
					?>
					<script>
						BX.bind(BX('feed-logentry-menuanchor-right-<?=$arEvent["EVENT"]["ID"]?>'), 'click', function(e) {
							BX.Livefeed.Post.showMenu({
								bindElement: BX('feed-logentry-menuanchor-right-<?= $arEvent["EVENT"]["ID"] ?>'),
								menuElement: BX('feed-logentry-menuanchor-<?= $arEvent["EVENT"]["ID"] ?>'),
								ind: '<?= $ind ?>',
							});
							return BX.PreventDefault(e);
						});
					</script>
					<?php
					?><div bx-data-pinned="<?=($pinned ? 'Y' : 'N')?>" class="feed-post-pin"></div><?php
				}

			?></div><?php


		?></div><?php

		?></div><?php // feed-item-wrap
	}
}
