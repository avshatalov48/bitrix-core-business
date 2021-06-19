<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

use Bitrix\Main\UI;
use Bitrix\Main\Localization\Loc;

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

if ($arResult["FatalError"] <> '')
{
	?><span class='errortext'><?=$arResult["FatalError"]?></span><br /><br /><?
}
else
{
	$jsAjaxPage = CUtil::JSEscape($APPLICATION->GetCurPageParam("", array("bxajaxid", "logajax", "logout")));
	$randomString = RandString(8);
	$randomId = 0;

	if($arResult["ErrorMessage"] <> '')
	{
		?><span class='errortext'><?=$arResult["ErrorMessage"]?></span><br /><br /><?
	}

	if (
		$arResult["Event"]
		&& is_array($arResult["Event"])
		&& !empty($arResult["Event"])
	)
	{
		?><div class="feed-item-wrap" data-livefeed-id="<?=(int)$arEvent["EVENT"]["ID"]?>"><?

		if (!defined("SONET_LOG_JS"))
		{
			define("SONET_LOG_JS", true);

			$message = array(
				'sonetLEGetPath' => '/bitrix/components/bitrix/socialnetwork.log.entry/ajax.php',
				'sonetLESetPath' => '/bitrix/components/bitrix/socialnetwork.log.entry/ajax.php',
				'sonetLPathToUser' => $arParams["PATH_TO_USER"],
				'sonetLPathToGroup' => $arParams["PATH_TO_GROUP"],
				'sonetLPathToDepartment' => $arParams["PATH_TO_CONPANY_DEPARTMENT"],
				'sonetLPathToSmile' => $arParams["PATH_TO_SMILE"],
				'sonetLShowRating' => $arParams["SHOW_RATING"],
				'sonetLTextLikeY' => COption::GetOptionString("main", "rating_text_like_y", GetMessage("SONET_C30_TEXT_LIKE_Y")),
				'sonetLTextLikeN' => COption::GetOptionString("main", "rating_text_like_n", GetMessage("SONET_C30_TEXT_LIKE_N")),
				'sonetLTextLikeD' => COption::GetOptionString("main", "rating_text_like_d", GetMessage("SONET_C30_TEXT_LIKE_D")),
				'sonetLTextPlus' => GetMessage("SONET_C30_TEXT_PLUS"),
				'sonetLTextMinus' => GetMessage("SONET_C30_TEXT_MINUS"),
				'sonetLTextCancel' => GetMessage("SONET_C30_TEXT_CANCEL"),
				'sonetLTextAvailable' => GetMessage("SONET_C30_TEXT_AVAILABLE"),
				'sonetLTextDenied' => GetMessage("SONET_C30_TEXT_DENIED"),
				'sonetLTextRatingY' => GetMessage("SONET_C30_TEXT_RATING_YES"),
				'sonetLTextRatingN' => GetMessage("SONET_C30_TEXT_RATING_NO"),
				'sonetLTextCommentError' => GetMessage("SONET_COMMENT_ERROR"),
				'sonetLPathToUserBlogPost' => $arParams["PATH_TO_USER_BLOG_POST"],
				'sonetLPathToGroupBlogPost' => $arParams["PATH_TO_GROUP_BLOG_POST"],
				'sonetLPathToUserMicroblogPost' => $arParams["PATH_TO_USER_MICROBLOG_POST"],
				'sonetLPathToGroupMicroblogPost' => $arParams["PATH_TO_GROUP_MICROBLOG_POST"],
				'sonetLNameTemplate' => $arParams["NAME_TEMPLATE"],
				'sonetLDateTimeFormat' => $arParams["DATE_TIME_FORMAT"],
				'sonetLShowLogin' => $arParams["SHOW_LOGIN"],
				'sonetLRatingType' => $arParams["RATING_TYPE"],
				'sonetLCurrentUserID' => intval($USER->GetID()),
				'sonetLAvatarSize' => $arParams["AVATAR_SIZE"],
				'sonetLAvatarSizeComment' => $arParams["AVATAR_SIZE_COMMON"],
				'sonetLBlogAllowPostCode' => $arParams["BLOG_ALLOW_POST_CODE"],
				'sonetLDestinationHidden1' => GetMessage("SONET_C30_DESTINATION_HIDDEN_1"),
				'sonetLDestinationHidden2' => GetMessage("SONET_C30_DESTINATION_HIDDEN_2"),
				'sonetLDestinationHidden3' => GetMessage("SONET_C30_DESTINATION_HIDDEN_3"),
				'sonetLDestinationHidden4' => GetMessage("SONET_C30_DESTINATION_HIDDEN_4"),
				'sonetLDestinationHidden5' => GetMessage("SONET_C30_DESTINATION_HIDDEN_5"),
				'sonetLDestinationHidden6' => GetMessage("SONET_C30_DESTINATION_HIDDEN_6"),
				'sonetLDestinationHidden7' => GetMessage("SONET_C30_DESTINATION_HIDDEN_7"),
				'sonetLDestinationHidden8' => GetMessage("SONET_C30_DESTINATION_HIDDEN_8"),
				'sonetLDestinationHidden9' => GetMessage("SONET_C30_DESTINATION_HIDDEN_9"),
				'sonetLDestinationHidden0' => GetMessage("SONET_C30_DESTINATION_HIDDEN_0"),
				'sonetLDestinationLimit' => intval($arParams["DESTINATION_LIMIT_SHOW"]),
			);
			if ($arParams["USE_FOLLOW"] === "Y")
			{
				$message['sonetLFollowY'] = GetMessage("SONET_LOG_T_FOLLOW_Y");
				$message['sonetLFollowN'] = GetMessage("SONET_LOG_T_FOLLOW_N");
			}
			?><script>
				BX.message(<?echo CUtil::PhpToJSObject($message)?>);
			</script>
			<?
		}

		$arEvent = &$arResult["Event"];

		$ind = $arParams["IND"];
		$is_unread = $arParams["EVENT"]["IS_UNREAD"];

		$important = (
			array_key_exists("EVENT_FORMATTED", $arEvent)
			&& array_key_exists("IS_IMPORTANT", $arEvent["EVENT_FORMATTED"])
			&& $arEvent["EVENT_FORMATTED"]["IS_IMPORTANT"]
		);

		if (
			$arParams["PUBLIC_MODE"] != "Y"
			&& isset($arEvent["EVENT_FORMATTED"]["URL"])
			&& $arEvent["EVENT_FORMATTED"]["URL"] !== ""
			&& $arEvent["EVENT_FORMATTED"]["URL"] !== false
		)
		{
			$url = $arEvent["EVENT_FORMATTED"]["URL"];
		}
		elseif (
			$arParams["PUBLIC_MODE"] != "Y"
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
		</script><?

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
				isset($arResult["EVENT_FORMATTED"])
				&& isset($arResult["EVENT_FORMATTED"]["UF"])
				&& isset($arResult["EVENT_FORMATTED"]["UF"]["UF_SONET_LOG_FILE"])
				&& !empty($arResult["EVENT_FORMATTED"]["UF"]["UF_SONET_LOG_FILE"]["VALUE"])
			)
			|| (
				isset($arResult["EVENT_FORMATTED"])
				&& isset($arResult["EVENT_FORMATTED"]["UF"])
				&& isset($arResult["EVENT_FORMATTED"]["UF"]["UF_SONET_LOG_DOC"])
				&& !empty($arResult["EVENT_FORMATTED"]["UF"]["UF_SONET_LOG_DOC"]["VALUE"])
			)
		)
		{
			$classNameList[] = 'feed-post-block-files';
		}

		$EVENT_ID = $arEvent["EVENT"]["EVENT_ID"];

		if (
			$arParams["FROM_LOG"] != 'Y'
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

		?><div
			 class="<?=implode(' ', $classNameList)?>"
			 id="log-entry-<?=$arEvent["EVENT"]["ID"]?>"
			 ondragenter="BX('feed_comments_block_<?=$arEvent["EVENT"]["ID"]?>').style.display = 'block';__logShowCommentForm('<?=$arEvent["COMMENTS_PARAMS"]["ENTITY_XML_ID"]?>')"
			 data-livefeed-id="<?=(int)$arEvent["EVENT"]["ID"]?>"
			 data-menu-id="post-menu-<?=$ind?>"
			<?
			if (isset($pinned))
			{
				?>
				 data-livefeed-post-pinned="<?=($pinned ? 'Y' : 'N')?>"
				<?
			}
			?>
		><?
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

			?><div id="sonet_log_day_item_<?=$ind?>" class="<?=implode(' ', $aditStylesList)?>"><?

				if ($_REQUEST["action"] == "get_entry")
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

				$style = ($avatar ? "background: url('".\CHTTP::urnEncode($avatar)."'); background-size: cover;" : "");

				?><div class="ui-icon ui-icon-common-user feed-user-avatar"><i style="<?=$style?>"></i></div><?

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
						?><a href="#" class="feed-post-pinned-link feed-post-pinned-link-expand"><?=Loc::getMessage('SONET_C30_FEED_PINNED_EXPAND')?></a><?
					?></div><?
				?></div><?

				?><div class="feed-post-title-block"><?
					$strDestination = "";

					if (
						isset($arEvent["EVENT_FORMATTED"]["DESTINATION"])
						&& is_array($arEvent["EVENT_FORMATTED"]["DESTINATION"])
						&& !empty($arEvent["EVENT_FORMATTED"]["DESTINATION"])
					)
					{
						$strDestination .= ' <span class="feed-add-post-destination-icon"></span> ';

						$i = 0;
						foreach($arEvent["EVENT_FORMATTED"]["DESTINATION"] as $arDestination)
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
									&& $arDestination["IS_EXTRANET"] === "Y"
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

						$iMoreDest = intval($arEvent["EVENT_FORMATTED"]["DESTINATION_MORE"]);

						if ($iMoreDest > 0)
						{
							if (
								isset($arEvent["EVENT_FORMATTED"]["DESTINATION_HIDDEN"])
								&& (int)$arEvent["EVENT_FORMATTED"]["DESTINATION_HIDDEN"] > 0
							)
							{
								$iMoreDest += intval($arEvent["EVENT_FORMATTED"]["DESTINATION_HIDDEN"]);
							}

							if (
								($iMoreDest % 100) > 10
								&& ($iMoreDest % 100) < 20
							)
								$suffix = 5;
							else
								$suffix = $iMoreDest % 10;

							$strDestination .= '<a class="feed-post-link-new" onclick="__logShowHiddenDestination('.$arEvent["EVENT"]["ID"].', '.(
								isset($arEvent["CREATED_BY"])
								&& is_array($arEvent["CREATED_BY"])
								&& isset($arEvent["CREATED_BY"]["TOOLTIP_FIELDS"])
								&& is_array($arEvent["CREATED_BY"]["TOOLTIP_FIELDS"])
								&& isset($arEvent["CREATED_BY"]["TOOLTIP_FIELDS"]["ID"])
									? intval($arEvent["CREATED_BY"]["TOOLTIP_FIELDS"]["ID"])
									: "false"
								).', this)" href="javascript:void(0)">'.str_replace("#COUNT#", $iMoreDest, GetMessage("SONET_C30_DESTINATION_MORE_".$suffix)).'</a>';
						}
						elseif (
							isset($arEvent["EVENT_FORMATTED"]["DESTINATION_HIDDEN"])
							&& intval($arEvent["EVENT_FORMATTED"]["DESTINATION_HIDDEN"]) > 0
						)
						{
							if (
								($arEvent["EVENT_FORMATTED"]["DESTINATION_HIDDEN"] % 100) > 10
								&& ($arEvent["EVENT_FORMATTED"]["DESTINATION_HIDDEN"] % 100) < 20
							)
								$suffix = 5;
							else
								$suffix = $arEvent["EVENT_FORMATTED"]["DESTINATION_HIDDEN"] % 10;

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
							if ($arParams["PUBLIC_MODE"] != 'Y')
							{
								$classNameList = [ 'feed-post-user-name' ];
								if (
									array_key_exists("IS_EXTRANET", $arEvent["CREATED_BY"])
									&& $arEvent["CREATED_BY"]["IS_EXTRANET"] == "Y"
								)
								{
									$classNameList[] = 'feed-post-user-name-extranet';
								}
								$href = str_replace(array("#user_id#", "#USER_ID#", "#id#", "#ID#"), $arEvent["CREATED_BY"]["TOOLTIP_FIELDS"]["ID"], $arEvent["CREATED_BY"]["TOOLTIP_FIELDS"]["PATH_TO_SONET_USER_PROFILE"]);
								$anchor_id = $randomString.($randomId++);

								$strCreatedBy .= '<a class="'.implode(' ', $classNameList).'"'.
									' id="anchor_'.$anchor_id.'"'.
									' bx-post-author-id="'.$arEvent["CREATED_BY"]["TOOLTIP_FIELDS"]["ID"].'"'.
									' bx-post-author-gender="'.$arEvent["CREATED_BY"]["TOOLTIP_FIELDS"]["PERSONAL_GENDER"].'"'.
									' bx-tooltip-user-id="'.$arEvent["CREATED_BY"]["TOOLTIP_FIELDS"]["ID"].'"'.
									' href="'.$href.'">'.CUser::FormatName($arParams["NAME_TEMPLATE"], $arEvent["CREATED_BY"]["TOOLTIP_FIELDS"], ($arParams["SHOW_LOGIN"] != "N" ? true : false)).'</a>';
							}
							else
							{
								$strCreatedBy .= '<span class="feed-post-user-name'.(array_key_exists("IS_EXTRANET", $arEvent["CREATED_BY"]) && $arEvent["CREATED_BY"]["IS_EXTRANET"] == "Y" ? " feed-post-user-name-extranet" : "").'">'.CUser::FormatName($arParams["NAME_TEMPLATE"], $arEvent["CREATED_BY"]["TOOLTIP_FIELDS"], ($arParams["SHOW_LOGIN"] != "N" ? true : false)).'</span>';
							}
						}
						elseif (
							array_key_exists("FORMATTED", $arEvent["CREATED_BY"])
							&& $arEvent["CREATED_BY"]["FORMATTED"] <> ''
						)
						{
							$strCreatedBy .= '<span class="feed-post-user-name'.(array_key_exists("IS_EXTRANET", $arEvent["CREATED_BY"]) && $arEvent["CREATED_BY"]["IS_EXTRANET"] == "Y" ? " feed-post-user-name-extranet" : "").'">'.$arEvent["CREATED_BY"]["FORMATTED"].'</span>';
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
							if (isset($arEvent["CREATED_BY"]["IS_EXTRANET"]) && $arEvent["CREATED_BY"]["IS_EXTRANET"] == "Y")
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
								' href="'.$href.'">'.CUser::FormatName($arParams["NAME_TEMPLATE"], $arEvent["ENTITY"]["TOOLTIP_FIELDS"], ($arParams["SHOW_LOGIN"] != "N" ? true : false)).'</a>';
						}
						elseif (
							array_key_exists("FORMATTED", $arEvent["ENTITY"])
							&& array_key_exists("NAME", $arEvent["ENTITY"]["FORMATTED"])
						)
						{
							if (array_key_exists("URL", $arEvent["ENTITY"]["FORMATTED"]) && $arEvent["ENTITY"]["FORMATTED"]["URL"] <> '')
							{
								$strCreatedBy .= '<a href="'.$arEvent["ENTITY"]["FORMATTED"]["URL"].'" class="feed-post-user-name'.(isset($arEvent["CREATED_BY"]["IS_EXTRANET"]) && $arEvent["CREATED_BY"]["IS_EXTRANET"] == "Y" ? " feed-post-user-name-extranet" : "").'">'.$arEvent["ENTITY"]["FORMATTED"]["NAME"].'</a>';
							}
							else
							{
								$strCreatedBy .= '<span class="feed-post-user-name'.(isset($arEvent["CREATED_BY"]["IS_EXTRANET"]) && $arEvent["CREATED_BY"]["IS_EXTRANET"] == "Y" ? " feed-post-user-name-extranet" : "").'">'.$arEvent["ENTITY"]["FORMATTED"]["NAME"].'</span>';
							}
						}
					}

					?><?=($strCreatedBy != "" ? $strCreatedBy : "")?><?
					?><span><?=$strDestination?></span><?

					?><div class="feed-post-time-wrap"><?

						$timestamp = (
							isset($arEvent["EVENT_FORMATTED"])
							&& isset($arEvent["EVENT_FORMATTED"]["LOG_DATE_FORMAT"])
								? MakeTimeStamp($arEvent["EVENT_FORMATTED"]["LOG_DATE_FORMAT"])
								: (
							array_key_exists("LOG_DATE_FORMAT", $arEvent)
								? MakeTimeStamp($arEvent["LOG_DATE_FORMAT"])
								: $arEvent["LOG_DATE_TS"]
							)
						);

						$datetime_detail = \CComponentUtil::getDateTimeFormatted(array(
							'TIMESTAMP' => $timestamp,
							'DATETIME_FORMAT' => $arParams["DATE_TIME_FORMAT"],
							'DATETIME_FORMAT_WITHOUT_YEAR' => (isset($arParams["DATE_TIME_FORMAT_WITHOUT_YEAR"]) ? $arParams["DATE_TIME_FORMAT_WITHOUT_YEAR"] : false),
							'TZ_OFFSET' => $arResult["TZ_OFFSET"]
						));

						if (!empty($url))
						{
							?><a href="<?=htmlspecialcharsbx($url)?>"><div class="feed-time"><?=$datetime_detail?></div></a><?
						}
						else
						{
							?><div class="feed-time"><?=$datetime_detail?></div><?
						}

					?></div><?

					$title24_2 = '';

					if (
						array_key_exists("EVENT_FORMATTED", $arEvent)
						&& ( $hasTitle24 || $hasTitle24_2 )
					)
					{
						if ($hasTitle24)
						{
							?><div class="feed-post-item"><?
							switch ($arEvent["EVENT"]["EVENT_ID"])
							{
							case "photo":
								?><div class="feed-add-post-destination-title"><span class="feed-add-post-files-title feed-add-post-p"><?=$arEvent["EVENT_FORMATTED"]["TITLE_24"]?></span></div><?
								break;
							case "timeman_entry":
								?><div class="feed-add-post-files-title"><?=$arEvent["EVENT_FORMATTED"]["TITLE_24"]?><a href="<?=htmlspecialcharsbx($arEvent['ENTITY']['FORMATTED']['URL'])?>" class="feed-work-time-link"><?=GetMessage("SONET_C30_MENU_ENTRY_TIMEMAN")?><span class="feed-work-time-icon"></span></a></div><?
								break;
							case "report":
								?><div class="feed-add-post-files-title"><?=$arEvent["EVENT_FORMATTED"]["TITLE_24"]?><a href="<?=htmlspecialcharsbx($arEvent['ENTITY']['FORMATTED']['URL'])?>" class="feed-work-time-link"><?=GetMessage("SONET_C30_MENU_ENTRY_REPORTS")?><span class="feed-work-time-icon"></span></a></div><?
								break;
							case "tasks":
								?><div class="feed-add-post-destination-title"><?=$arEvent["EVENT_FORMATTED"]["TITLE_24"]?><span class="feed-work-time"><?=GetMessage("SONET_C30_MENU_ENTRY_TASKS")?><span class="feed-work-time-icon"></span></span></div><?
								break;
							default:
								?><div class="feed-add-post-destination-title"><?=$arEvent["EVENT_FORMATTED"]["TITLE_24"]?></div><?
								break;
							}
							?></div><?
						}

						if (
							!$important
							&& $hasTitle24_2
						)
						{
							ob_start();

							if ($url !== "")
							{
								?><div class="feed-post-title<?=(isset($arEvent["EVENT_FORMATTED"]["TITLE_24_2_STYLE"]) ? " ".$arEvent["EVENT_FORMATTED"]["TITLE_24_2_STYLE"] : "")?>"><a href="<?=$url?>"><?=$arEvent["EVENT_FORMATTED"]["TITLE_24_2"]?></a></div><?
							}
							else
							{
								?><div class="feed-post-title<?=(isset($arEvent["EVENT_FORMATTED"]["TITLE_24_2_STYLE"]) ? " ".$arEvent["EVENT_FORMATTED"]["TITLE_24_2_STYLE"] : "")?>"><?=$arEvent["EVENT_FORMATTED"]["TITLE_24_2"]?></div><?
							}

							$title24_2 = ob_get_clean();
						}
					}

				?></div><? // title

				// body

				$stub = false;
				if (
					array_key_exists("EVENT_FORMATTED", $arEvent)
					&& array_key_exists("STUB", $arEvent["EVENT_FORMATTED"])
					&& $arEvent["EVENT_FORMATTED"]["STUB"]
				)
				{
					$stub = true;
					?><?$APPLICATION->IncludeComponent(
						"bitrix:socialnetwork.log.entry.stub",
						"",
						array(
							"EVENT" => $arEvent['EVENT'],
						),
						$component,
						array(
							"HIDE_ICONS" => "Y"
						)
					);?><?
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

					?><div class="<?=implode(' ', $classNameList)?>"><?

						$classNameList = [ 'feed-post-contentview' ];
						if ($arParams["FROM_LOG"] === "Y")
						{
							$classNameList[] = 'feed-post-text-block-inner';
						}

						?><div
						 class="<?=implode(' ', $classNameList)?>"
						 id="<?=(!empty($arResult["CONTENT_ID"]) ? "feed-post-contentview-".htmlspecialcharsBx($arResult["CONTENT_ID"]) : "")?>"
						 bx-content-view-xml-id="<?=htmlspecialcharsBx($arResult["CONTENT_ID"])?>"><?
							?><div class="feed-post-text-block-inner-inner" id="log_entry_body_<?=$arEvent["EVENT"]["ID"]?>"><?

								?><div class="feed-important-icon"></div><?

								?><?=$title24_2?><?

								if ($hasTitle24_2)
								{
									if ($url !== "")
									{
										?><a href="<?=$url?>" class="feed-post-title" target="_top"><?=$arEvent["EVENT_FORMATTED"]["TITLE_24_2"]?></a><?
									}
									else
									{
										?><div class="feed-post-title"><?=$arEvent["EVENT_FORMATTED"]["TITLE_24_2"]?></div><?
									}
									?><br /><?
								}
								?><?=$arEvent["EVENT_FORMATTED"]["MESSAGE"]?>

							</div><?
						?></div><?

						if($arParams["FROM_LOG"] === 'Y')
						{
							?><div class="feed-post-text-more" onclick="BX.UI.Animations.expand({
								moreButtonNode: this,
								type: 'post',
								classBlock: 'feed-post-text-block',
								classOuter: 'feed-post-text-block-inner',
								classInner: 'feed-post-text-block-inner-inner',
								heightLimit: 300,
								callback: oLF.expandPost
								})" id="log_entry_more_<?=$arEvent["EVENT"]["ID"]?>"><?
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
											bodyBlockID: 'log_entry_body_<?=$arEvent["EVENT"]["ID"]?>',
											moreButtonBlockID: 'log_entry_more_<?=$arEvent["EVENT"]["ID"]?>',
											informerBlockID: 'log_entry_inform_<?=$arEvent["EVENT"]["ID"]?>'
										});
									}
								});
							</script><?
						}
					?></div><?
				}
				elseif (
					$EVENT_ID === "photo"
					|| $EVENT_ID === "photo_photo"
				)
				{
					?><div class="feed-post-item feed-post-contentview" id="<?=(!empty($arResult["CONTENT_ID"]) ? "feed-post-contentview-".htmlspecialcharsBx($arResult["CONTENT_ID"]) : "")?>" bx-content-view-xml-id="<?=htmlspecialcharsBx($arResult["CONTENT_ID"])?>"><?

						?><?=$title24_2?><?

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
							$alias = (isset($arEventParams["ALIAS"]) ? $arEventParams["ALIAS"] : false);

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
								$photo_detail_url = $arParams["PATH_TO_".($arEvent["EVENT"]["ENTITY_TYPE"] == SONET_SUBSCRIBE_ENTITY_GROUP ? "GROUP" : "USER")."_PHOTO_ELEMENT"];
							}

							if (
								$photo_iblock_type <> ''
								&& intval($photo_iblock_id) > 0
								&& intval($photo_section_id) > 0
								&& count($arPhotoItems) > 0
							)
							{
								$photo_permission = "D";
								if ($arEvent["EVENT"]["ENTITY_TYPE"] == SONET_SUBSCRIBE_ENTITY_GROUP)
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
								else
								{
									if (CSocNetFeaturesPerms::CanPerformOperation($USER->GetID(), SONET_ENTITY_USER, $arEvent["EVENT"]["ENTITY_ID"], "photo", "write", CSocNetUser::IsCurrentUserModuleAdmin()))
									{
										$photo_permission = "W";
									}
									elseif (CSocNetFeaturesPerms::CanPerformOperation($USER->GetID(), SONET_ENTITY_USER, $arEvent["EVENT"]["ENTITY_ID"], "photo", "view", CSocNetUser::IsCurrentUserModuleAdmin()))
									{
										$photo_permission = "R";
									}
								}

								?><?$APPLICATION->IncludeComponent(
									"bitrix:photogallery.detail.list.ex",
									"",
									Array(
										"IBLOCK_TYPE" => $photo_iblock_type,
										"IBLOCK_ID" => $photo_iblock_id,
										"SHOWN_PHOTOS" => (count($arPhotoItems) > $arParams["PHOTO_COUNT"]
											? array_slice($arPhotoItems, 0, $arParams["PHOTO_COUNT"])
											: $arPhotoItems
										),
										"DRAG_SORT" => "N",
										"MORE_PHOTO_NAV" => "N",

										//"USE_PERMISSIONS" => "N",
										"PERMISSION" => $photo_permission,

										"THUMBNAIL_SIZE" => $arParams["PHOTO_THUMBNAIL_SIZE"],
										"SHOW_CONTROLS" => "Y",
										"USE_RATING" => ($arParams["PHOTO_USE_RATING"] == "Y" || $arParams["SHOW_RATING"] == "Y" ? "Y" : "N"),
										"SHOW_RATING" => $arParams["SHOW_RATING"],
										"SHOW_SHOWS" => "N",
										"SHOW_COMMENTS" => "Y",
										"MAX_VOTE" => $arParams["PHOTO_MAX_VOTE"],
										"VOTE_NAMES" => isset($arParams["PHOTO_VOTE_NAMES"])? $arParams["PHOTO_VOTE_NAMES"]: Array(),
										"DISPLAY_AS_RATING" => ($arParams["SHOW_RATING"] == "Y" ? "rating_main" : (isset($arParams["PHOTO_DISPLAY_AS_RATING"])? $arParams["PHOTO_DISPLAY_AS_RATING"]: "rating")),
										"RATING_MAIN_TYPE" => ($arParams["SHOW_RATING"] == "Y" ? $arParams["RATING_TYPE"] : ""),

										"BEHAVIOUR" => "SIMPLE",
										"SET_TITLE" => "N",
										"CACHE_TYPE" => "A",
										"CACHE_TIME" => $arParams["CACHE_TIME"],
										"CACHE_NOTES" => "",
										"SECTION_ID" => $photo_section_id,
										"ELEMENT_LAST_TYPE"	=> "none",
										"ELEMENT_SORT_FIELD" => "ID",
										"ELEMENT_SORT_ORDER" => "asc",
										"ELEMENT_SORT_FIELD1" => "",
										"ELEMENT_SORT_ORDER1" => "asc",
										"PROPERTY_CODE" => array(),

										"INDEX_URL" => CComponentEngine::MakePathFromTemplate(
											$arParams["PATH_TO_".($arEvent["EVENT"]["ENTITY_TYPE"] == SONET_SUBSCRIBE_ENTITY_GROUP ? "GROUP" : "USER")."_PHOTO"],
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
										"SECTION_URL" => CComponentEngine::MakePathFromTemplate(
											$arParams["PATH_TO_".($arEvent["EVENT"]["ENTITY_TYPE"] == SONET_SUBSCRIBE_ENTITY_GROUP ? "GROUP" : "USER")."_PHOTO_SECTION"],
											array(
												"user_id" => $arEvent["EVENT"]["ENTITY_ID"],
												"group_id" => $arEvent["EVENT"]["ENTITY_ID"],
												"section_id" => ($EVENT_ID == "photo_photo" ? $photo_section_id : $arEvent["EVENT"]["SOURCE_ID"])
											)
										),
										"PATH_TO_USER" => $arParams["PATH_TO_USER"],
										"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
										"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
										"GROUP_PERMISSIONS" => array(),
										"PAGE_ELEMENTS" => $arParams["PHOTO_COUNT"],
										"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT_DETAIL"],
										"SET_STATUS_404" => "N",
										"ADDITIONAL_SIGHTS" => array(),
										"PICTURES_SIGHT" => "real",
										"USE_COMMENTS" => $arParams["PHOTO_USE_COMMENTS"],
										"COMMENTS_TYPE" => ($arParams["PHOTO_COMMENTS_TYPE"] == "blog" ? "blog" : "forum"),
										"FORUM_ID" => $arParams["PHOTO_FORUM_ID"],
										"BLOG_URL" => $arParams["PHOTO_BLOG_URL"],
										"USE_CAPTCHA" => $arParams["PHOTO_USE_CAPTCHA"],
										"SHOW_LINK_TO_FORUM" => "N",
										"IS_SOCNET" => "Y",
										"USER_ALIAS" => ($alias ? $alias : ($arEvent["EVENT"]["ENTITY_TYPE"] == SONET_SUBSCRIBE_ENTITY_GROUP ? "group" : "user")."_".$arEvent["EVENT"]["ENTITY_ID"]),
										//these two params below used to set action url and unique id - for any ajax actions
										"~UNIQUE_COMPONENT_ID" => 'bxfg_ucid_from_req_'.$photo_iblock_id.'_'.($EVENT_ID == "photo_photo" ? $photo_section_id : $arEvent["EVENT"]["SOURCE_ID"])."_".$arEvent["EVENT"]["ID"],
										"ACTION_URL" => CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_".($arEvent["EVENT"]["ENTITY_TYPE"] == SONET_SUBSCRIBE_ENTITY_GROUP ? "GROUP" : "USER")."_PHOTO_SECTION"], array("user_id" => $arEvent["EVENT"]["ENTITY_ID"],"group_id" => $arEvent["EVENT"]["ENTITY_ID"],"section_id" => ($EVENT_ID == "photo_photo" ? $photo_section_id : $arEvent["EVENT"]["SOURCE_ID"]))),
									),
									$component,
									array(
										"HIDE_ICONS" => "Y"
									)
								);?><?
							}
						}

					?></div><?
				}
				elseif ($EVENT_ID === "tasks")
				{
					?><div class="feed-post-info-block-wrap feed-post-contentview" id="feed-post-contentview-<?=htmlspecialcharsBx($arResult["CONTENT_ID"])?>" bx-content-view-xml-id="<?=htmlspecialcharsBx($arResult["CONTENT_ID"])?>"><?

						?><?=$title24_2?><?
						?><?=$arEvent["EVENT_FORMATTED"]["MESSAGE"]?><?

					?></div><?
				}
				elseif (in_array($EVENT_ID, array("timeman_entry", "report")))
				{
					CJSCore::Init(array('timeman'));
					?><div class="feed-post-text-block feed-post-contentview" id="feed-post-contentview-<?=htmlspecialcharsBx($arResult["CONTENT_ID"])?>" bx-content-view-xml-id="<?=htmlspecialcharsBx($arResult["CONTENT_ID"])?>"><?

						?><?=$title24_2?><?
						?><?=$arEvent["EVENT_FORMATTED"]["MESSAGE"]?><?

					?></div><?
				}
				elseif ($arEvent["EVENT_FORMATTED"]["MESSAGE"] <> '') // all other events
				{
					?><div class="feed-post-text-block"><?

						$classNameList = [ 'feed-post-contentview' ];
						if ($arParams["FROM_LOG"] === "Y")
						{
							$classNameList[] = 'feed-post-text-block-inner';
						}

						if ($arResult["CONTENT_ID"])
						{
							?><div class="<?=implode(' ', $classNameList)?>" id="feed-post-text-contentview-<?=htmlspecialcharsBx($arResult["CONTENT_ID"])?>" bx-content-view-xml-id="<?=htmlspecialcharsBx($arResult["CONTENT_ID"])?>"><?
								?><div class="feed-post-text-block-inner-inner" id="log_entry_body_<?=$arEvent["EVENT"]["ID"]?>"><?

									?><?=$title24_2?><?
									?><?=$arEvent["EVENT_FORMATTED"]["MESSAGE"]?><?

							?></div><?
							?></div><?
						}
						else
						{
							?><div class="<?=implode(' ', $classNameList)?>"><?
								?><div class="feed-post-text-block-inner-inner" id="log_entry_body_<?=$arEvent["EVENT"]["ID"]?>"><?

									?><?=$title24_2?><?
									?><?=$arEvent["EVENT_FORMATTED"]["MESSAGE"]?><?

								?></div><?
							?></div><?
						}

						if($arParams["FROM_LOG"] === 'Y')
						{
							?><div class="feed-post-text-more" id="log_entry_more_<?=$arEvent["EVENT"]["ID"]?>" onclick="BX.UI.Animations.expand({
								moreButtonNode: this,
								type: 'post',
								classBlock: 'feed-post-text-block',
								classOuter: 'feed-post-text-block-inner',
								classInner: 'feed-post-text-block-inner-inner',
								heightLimit: 300,
								callback: oLF.expandPost
								})"><?
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
											bodyBlockID : 'log_entry_body_<?=$arEvent["EVENT"]["ID"]?>',
											moreButtonBlockID : 'log_entry_more_<?=$arEvent["EVENT"]["ID"]?>',
											informerBlockID: 'log_entry_inform_<?=$arEvent["EVENT"]["ID"]?>'
										});
									}
								});
							</script><?
						}
					?></div><?
				}

				if (
					is_array($arEvent["EVENT_FORMATTED"]["UF"])
					&& count($arEvent["EVENT_FORMATTED"]["UF"]) > 0
				)
				{
					$eventHandlerID = AddEventHandler("main", "system.field.view.file", Array("CSocNetLogTools", "logUFfileShow"));
					foreach ($arEvent["EVENT_FORMATTED"]["UF"] as $FIELD_NAME => $arUserField)
					{
						if(!empty($arUserField["VALUE"]))
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
						&& intval($eventHandlerID) > 0
					)
					{
						RemoveEventHandler('main', 'system.field.view.file', $eventHandlerID);
					}
				}

				if (
					$arEvent["EVENT"]["EVENT_ID"] != 'tasks'
					&& !empty($arEvent["TAGS"])
					&& is_array($arEvent["TAGS"])
				)
				{
					?><div class="feed-com-tags-block"><noindex>
						<div class="feed-com-files-title"><?=Loc::getMessage("SONET_C30_TAGS")?></div>
						<div class="feed-com-files-cont" id="logentry-tags-<?=intval($arEvent["EVENT"]["ID"])?>"><?
							$i=0;
							foreach($arEvent["TAGS"] as $v)
							{
								if($i!=0)
									echo ",";
								?> <a href="<?=$v["URL"]?>" rel="nofollow" class="feed-com-tag" bx-tag-value="<?=htmlspecialcharsbx($v["NAME"])?>"><?=htmlspecialcharsEx($v["NAME"])?></a><?
								$i++;
							}
							?></div>
					</noindex></div><?
				}

				// Used to display some HTML before informers
				if ($arEvent["EVENT_FORMATTED"]["FOOTER_MESSAGE"] != '')
				{
					echo $arEvent["EVENT_FORMATTED"]["FOOTER_MESSAGE"];
				}

				$tplID = 'SOCCOMMENT_'.$arEvent["COMMENTS_PARAMS"]["ENTITY_XML_ID"].'_';
				$separatorClassName = "feed-post-informers";

				?><div class="<?=$separatorClassName?>" id="log_entry_inform_<?=intval($arEvent["EVENT"]["ID"])?>"><div class="feed-post-informers-cont"><?

					$voteId = false;
					if (
						$arParams["SHOW_RATING"] == "Y"
						&& $arEvent["EVENT"]["RATING_TYPE_ID"] <> ''
						&& intval($arEvent["EVENT"]["RATING_ENTITY_ID"]) > 0
					)
					{
						$voteId = $arEvent["EVENT"]["RATING_TYPE_ID"].'_'.$arEvent["EVENT"]["RATING_ENTITY_ID"].'-'.(time()+rand(0, 1000));
						$emotion = (isset($arEvent["RATING"]) && !empty($arEvent["RATING"]["USER_REACTION"])? mb_strtoupper($arEvent["RATING"]["USER_REACTION"]) : 'LIKE');

						if ($arResult["bIntranetInstalled"])
						{
							$likeClassList = [
								'feed-inform-item',
								'bx-ilike-left-wrap'
							];

							if(
								isset($arEvent["RATING"])
								&& isset($arEvent["RATING"]["USER_HAS_VOTED"])
								&& $arEvent["RATING"]["USER_HAS_VOTED"] === "Y"
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
							?></span><?
						}
					}

					if (
						array_key_exists("HAS_COMMENTS", $arEvent)
						&& $arEvent["HAS_COMMENTS"] == "Y"
						&& array_key_exists("CAN_ADD_COMMENTS", $arEvent)
						&& $arEvent["CAN_ADD_COMMENTS"] == "Y"
					)
					{
						$bHasComments = true;
						?><span class="feed-inform-comments"><?
							?><a href="javascript:void(0);" onclick="BX('feed_comments_block_<?=$arEvent["EVENT"]["ID"]?>').style.display = 'block'; __logShowCommentForm('<?=$arEvent["COMMENTS_PARAMS"]["ENTITY_XML_ID"]?>')"><?=GetMessage("SONET_C30_COMMENT_ADD")?></a><?
						?></span><?

						?><div class="feed-inform-item feed-inform-comments feed-inform-comments-pinned">
							<?=Loc::getMessage('SONET_C30_PINNED_COMMENTS')?>
							<span class="feed-inform-comments-pinned-all"><?=$arResult['ALL_COMMENTS_COUNT']?></span>
							<span class="feed-inform-comments-pinned-old"><?=$arResult['ALL_COMMENTS_COUNT'] - $arResult['NEW_COMMENTS_COUNT']?></span><?
							$classList = [ 'feed-inform-comments-pinned-new' ];
							if ($arResult['NEW_COMMENTS_COUNT'] > 0)
							{
								$classList[] = 'feed-inform-comments-pinned-new-active';
							}
							?><span class="<?=implode(' ', $classList)?>"><?
								?><svg width="6" height="6" viewBox="0 0 6 6" fill="none" xmlns="http://www.w3.org/2000/svg"><?
									?><path opacity="0.840937" d="M3.36051 5.73145V3.76115H5.33081V2.70174H3.36051V0.731445H2.30111V2.70174H0.330811V3.76115H2.30111V5.73145H3.36051Z" fill="white"></path><?
								?></svg><?
								?><span class="feed-inform-comments-pinned-new-value"><?=$arResult['NEW_COMMENTS_COUNT']?></span><?
							?></span><?
						?></div><?
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
						?><span class="feed-inform-item feed-inform-follow" data-follow="<?=($arEvent["EVENT"]["FOLLOW"] == "Y" ? "Y" : "N")?>" id="log_entry_follow_<?=intval($arEvent["EVENT"]["ID"])?>" onclick="__logSetFollow(<?=$arEvent["EVENT"]["ID"]?>)"><a href="javascript:void(0);"><?=GetMessage("SONET_LOG_T_FOLLOW_".($arEvent["EVENT"]["FOLLOW"] == "Y" ? "Y" : "N"))?></a></span><?
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
							data-log-entry-createtask="<?=($arResult["canGetPostContent"]) && $arResult["bTasksAvailable"] && !$stub ? 'Y' : 'N'?>"
							data-log-entry-entity-type="<?=(!empty($arResult["POST_CONTENT_TYPE_ID"]) ? htmlspecialcharsbx($arResult["POST_CONTENT_TYPE_ID"]) : "")?>"
							data-log-entry-entity-id="<?=(!empty($arResult["POST_CONTENT_ID"]) ? (int)$arResult["POST_CONTENT_ID"] : "")?>"
							data-log-entry-log-id="<?=(int)$arEvent["EVENT"]["ID"]?>"
							data-log-entry-favorites="<?=(array_key_exists("FAVORITES", $arEvent) && $arEvent["FAVORITES"] === "Y" ? 'Y' : 'N')?>"
							data-log-entry-items="<?=htmlspecialcharsbx(\Bitrix\Main\Web\Json::encode($arMenuItemsAdditional))?>"
							onclick="__logShowPostMenu(
								this,
								'<?=$ind?>',
								'<?=$arEvent["EVENT"]["ENTITY_TYPE"] ?>',
								<?=$arEvent["EVENT"]["ENTITY_ID"] ?>,
								'<?=$arEvent["EVENT"]["EVENT_ID"] ?>',
								<?=($arEvent["EVENT"]["EVENT_ID_FULLSET"] ? "'".$arEvent["EVENT"]["EVENT_ID_FULLSET"]."'" : "false")?>,
								'<?=$arEvent["EVENT"]["USER_ID"] ?>',
								'<?=$arEvent["EVENT"]["ID"] ?>',
								<?=(array_key_exists("FAVORITES", $arEvent) && $arEvent["FAVORITES"] === "Y" ? "true" : "false")?>,
								<?=CUtil::PhpToJSObject($arMenuItemsAdditional)?>
							); return BX.PreventDefault(this);"
							class="feed-inform-item feed-post-more-link"><span class="feed-post-more-text"><?=GetMessage("SONET_LOG_T_BUTTON_MORE")?></span><span class="feed-post-more-arrow"></span></a><?
					}

					?><span class="feed-inform-item feed-post-time-wrap feed-inform-contentview"><?
						if (
							$arParams["PUBLIC_MODE"] != 'Y'
							&& isset($arResult["CONTENT_ID"])
						)
						{
							$APPLICATION->IncludeComponent(
								"bitrix:socialnetwork.contentview.count", "",
								Array(
									"CONTENT_ID" => $arResult["CONTENT_ID"],
									"CONTENT_VIEW_CNT" => (isset($arResult["CONTENT_VIEW_CNT"]) ? $arResult["CONTENT_VIEW_CNT"] : 0),
									"PATH_TO_USER_PROFILE" => $arParams["PATH_TO_USER"]
								),
								$component,
								array("HIDE_ICONS" => "Y")
							);
						}
					?></span><?

					if (
						$arResult["bIntranetInstalled"]
						&& $arParams["SHOW_RATING"] == "Y"
						&& $arEvent["EVENT"]["RATING_TYPE_ID"] <> ''
						&& intval($arEvent["EVENT"]["RATING_ENTITY_ID"]) > 0
					)
					{
						?><div class="feed-post-emoji-top-panel-outer"><?
							?><div id="feed-post-emoji-top-panel-container-<?=htmlspecialcharsbx($voteId)?>" class="feed-post-emoji-top-panel-box <?=(intval($arEvent["RATING"]["TOTAL_POSITIVE_VOTES"]) > 0 ? 'feed-post-emoji-top-panel-container-active' : '')?>"><?
								$APPLICATION->IncludeComponent(
									"bitrix:rating.vote",
									"like_react",
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
										"REACTIONS_LIST" => $arEvent["RATING"]["REACTIONS_LIST"],
										"PATH_TO_USER_PROFILE" => $arParams["PATH_TO_USER"],
										'TOP_DATA' => (!empty($arResult['TOP_RATING_DATA']) ? $arResult['TOP_RATING_DATA'] : false),
										'VOTE_ID' => $voteId
									),
									$component,
									array("HIDE_ICONS" => "Y")
								);
							?></div><?
						?></div><?
					}
				?></div><? // feed-post-informers

				if ($_REQUEST["action"] == "get_entry")
				{
					$strEntryText = ob_get_contents();
					ob_end_clean();

					echo CUtil::PhpToJSObject(array(
						"ENTRY_HTML" => $strEntryText
					));
					die();
				}

			?></div></div><? // cont_wrap

			if (
				isset($arEvent["HAS_COMMENTS"])
				&& $arEvent["HAS_COMMENTS"] == "Y"
			)
			{
				?><div class="feed-comments-block-wrap"><?
				?><script>
					BX.viewElementBind(
						'feed_comments_block_<?=$arEvent["EVENT"]["ID"]?>',
						{},
						function(node){
							return BX.type.isElementNode(node) && (node.getAttribute('data-bx-viewer') || node.getAttribute('data-bx-image'));
						}
					);
					top.postFollow<?=$arParams["ID"]?> = postFollow<?=$arParams["ID"]?> = '<?=$arParams["FOLLOW"]?>';
				</script><?

				$arRecords = array();
				if (!!$component && !!$component->__parent && !!$component->__parent->arResult)
				{
					$component->__parent->arResult["ENTITIES_XML_ID"] = (!!$component->__parent->arResult["ENTITIES_XML_ID"] ? $component->__parent->arResult["ENTITIES_XML_ID"] : array());
					$component->__parent->arResult["ENTITIES_XML_ID"][$arEvent["COMMENTS_PARAMS"]["ENTITY_XML_ID"]] = array($arEvent["COMMENTS_PARAMS"]["ENTITY_TYPE"], $arEvent["EVENT"]["SOURCE_ID"]);
					$component->__parent->arResult["ENTITIES_CORRESPONDENCE"] = (!!$component->__parent->arResult["ENTITIES_CORRESPONDENCE"] ? $component->__parent->arResult["ENTITIES_CORRESPONDENCE"] : array());
					$component->__parent->arResult["ENTITIES_CORRESPONDENCE"][$arEvent["COMMENTS_PARAMS"]["ENTITY_XML_ID"]."-0"] = array($arEvent["EVENT"]["ID"], 0);
				}

				$commentRatingEntityTypeId = false;
				if (!empty($arEvent["COMMENTS"]))
				{
					foreach($arEvent["COMMENTS"] as $key => $arComment)
					{
						$commentId = (!!$arComment["EVENT"]["SOURCE_ID"] ? $arComment["EVENT"]["SOURCE_ID"] : $arComment["EVENT"]["ID"]);
						if (!!$component && !!$component->__parent && !!$component->__parent->arResult)
							$component->__parent->arResult["ENTITIES_CORRESPONDENCE"][$arEvent["COMMENTS_PARAMS"]["ENTITY_XML_ID"]."-".$commentId] =
								array($arEvent["EVENT"]["ID"], $arComment["EVENT"]["ID"]);

						$event_date_log_ts = (isset($arComment["EVENT"]["LOG_DATE_TS"]) ? $arComment["EVENT"]["LOG_DATE_TS"] : (MakeTimeStamp($arComment["EVENT"]["LOG_DATE"]) - intval($arResult["TZ_OFFSET"])));
						$arRecords[$commentId] = array(
							"ID" => $commentId,
							"NEW" => ($USER->IsAuthorized()
								&& $arEvent["EVENT"]["FOLLOW"] != "N"
								&& $arComment["EVENT"]["USER_ID"] != $USER->GetID()
								&& intval($arResult["LAST_LOG_TS"]) > 0
								&& $event_date_log_ts > $arResult["LAST_LOG_TS"]
								&& (
									$arResult["COUNTER_TYPE"] == "**"
									|| $arResult["COUNTER_TYPE"] == "CRM_**"
									|| $arResult["COUNTER_TYPE"] == "blog_post"
								)
									? "Y"
									: "N"
							),
							"AUTHOR" => array(
								"ID" => $arComment["CREATED_BY"]["TOOLTIP_FIELDS"]["ID"],
								"NAME" => $arComment["CREATED_BY"]["TOOLTIP_FIELDS"]["NAME"],
								"LAST_NAME" => $arComment["CREATED_BY"]["TOOLTIP_FIELDS"]["LAST_NAME"],
								"SECOND_NAME" => $arComment["CREATED_BY"]["TOOLTIP_FIELDS"]["SECOND_NAME"],
								"LOGIN" => $arComment["CREATED_BY"]["TOOLTIP_FIELDS"]["LOGIN"],
								"PERSONAL_GENDER" => $arComment["CREATED_BY"]["TOOLTIP_FIELDS"]["PERSONAL_GENDER"],
								"AVATAR" => $arComment["AVATAR_SRC"],
								"EXTERNAL_AUTH_ID" => (isset($arComment["CREATED_BY"]["TOOLTIP_FIELDS"]["EXTERNAL_AUTH_ID"]) ? $arComment["CREATED_BY"]["TOOLTIP_FIELDS"]["EXTERNAL_AUTH_ID"] : false),
								"UF_USER_CRM_ENTITY" => (isset($arComment["CREATED_BY"]["TOOLTIP_FIELDS"]["UF_USER_CRM_ENTITY"]) ? $arComment["CREATED_BY"]["TOOLTIP_FIELDS"]["UF_USER_CRM_ENTITY"] : false)
							),
							"APPROVED" => "Y",
							"POST_TIMESTAMP" => $arComment["LOG_DATE_TS"],
							"POST_MESSAGE_TEXT" => (array_key_exists("FULL_MESSAGE_CUT", $arComment["EVENT_FORMATTED"]) ? $arComment["EVENT_FORMATTED"]["FULL_MESSAGE_CUT"] : ""),
							"~POST_MESSAGE_TEXT" => "",
							"AUX" => (!empty($arComment["AUX"]) ? $arComment["AUX"] : ''),
							"AUX_LIVE_PARAMS" => (!empty($arComment["AUX_LIVE_PARAMS"]) ? $arComment["AUX_LIVE_PARAMS"] : array()),
							"CAN_DELETE" => (!empty($arComment["CAN_DELETE"]) ? $arComment["CAN_DELETE"] : 'Y'),
							"CLASSNAME" => ""
						);

						if (
							$arComment["EVENT"]["RATING_TYPE_ID"] <> ''
							&& $arComment["EVENT"]["RATING_ENTITY_ID"] > 0
							&& $arParams["SHOW_RATING"] == "Y"
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
								$voteId = $arComment["EVENT"]["RATING_TYPE_ID"].'_'.$RATING_ENTITY_ID.'-'.(time()+rand(0, 1000));

								$arRecords[$commentId]["RATING_VOTE_ID"] = $voteId;
								$arRecords[$commentId]["RATING_USER_HAS_VOTED"] = $arResult["RATING_COMMENTS"][$RATING_ENTITY_ID]["USER_HAS_VOTED"];
								$arRecords[$commentId]["RATING_USER_REACTION"] = $arResult["RATING_COMMENTS"][$RATING_ENTITY_ID]["USER_REACTION"];

								$APPLICATION->IncludeComponent(
									"bitrix:rating.vote",
									"like_react",
									Array(
										"COMMENT" => "Y",
										"ENTITY_TYPE_ID" => $arComment["EVENT"]["RATING_TYPE_ID"],
										"ENTITY_ID" => $RATING_ENTITY_ID,
										"OWNER_ID" => $arComment["CREATED_BY"]["TOOLTIP_FIELDS"]["ID"],
										"USER_VOTE" => $arResult["RATING_COMMENTS"][$RATING_ENTITY_ID]["USER_VOTE"],
										"USER_REACTION" => $arResult["RATING_COMMENTS"][$RATING_ENTITY_ID]["USER_REACTION"],
										"USER_HAS_VOTED" => $arResult["RATING_COMMENTS"][$RATING_ENTITY_ID]["USER_HAS_VOTED"],
										"TOTAL_VOTES" => $arResult["RATING_COMMENTS"][$RATING_ENTITY_ID]["TOTAL_VOTES"],
										"TOTAL_POSITIVE_VOTES" => $arResult["RATING_COMMENTS"][$RATING_ENTITY_ID]["TOTAL_POSITIVE_VOTES"],
										"TOTAL_NEGATIVE_VOTES" => $arResult["RATING_COMMENTS"][$RATING_ENTITY_ID]["TOTAL_NEGATIVE_VOTES"],
										"TOTAL_VALUE" => $arResult["RATING_COMMENTS"][$RATING_ENTITY_ID]["TOTAL_VALUE"],
										"REACTIONS_LIST" => $arResult["RATING_COMMENTS"][$RATING_ENTITY_ID]["REACTIONS_LIST"],
										"PATH_TO_USER_PROFILE" => $arParams["PATH_TO_USER"],
										"VOTE_ID" => ($arResult["bIntranetInstalled"] ? $voteId : false)
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

								?><span class="sonet-log-comment-like rating_vote_text"><?
								$APPLICATION->IncludeComponent(
									"bitrix:rating.vote", $arParams["RATING_TYPE"],
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
								?></span><?
								$APPLICATION->AddViewContent(implode('_', array($tplID, 'ID', $commentId, "BEFORE_ACTIONS")), ob_get_clean(), 50);
							}
						}

						if (
							is_array($arComment["UF"])
							&& count($arComment["UF"]) > 0
						)
						{
							ob_start();
							$eventHandlerID = AddEventHandler("main", "system.field.view.file", Array("CSocNetLogTools", "logUFfileShow"));
							foreach ($arComment["UF"] as $FIELD_NAME => $arUserField)
							{
								if(!empty($arUserField["VALUE"]))
								{
									$APPLICATION->IncludeComponent(
										"bitrix:system.field.view",
										$arUserField["USER_TYPE"]["USER_TYPE_ID"],
										array(
											"LAZYLOAD" => $arParams["LAZYLOAD"],
											"arUserField" => $arUserField
										),
										null,
										array("HIDE_ICONS"=>"Y")
									);
									$arRecords[$commentId]["CLASSNAME"] = "feed-com-block-uf";
								}
							}
							if (
								$eventHandlerID !== false
								&& intval($eventHandlerID) > 0
							)
							{
								RemoveEventHandler('main', 'system.field.view.file', $eventHandlerID);
							}

							$APPLICATION->AddViewContent(implode('_', array($tplID, 'ID', $commentId, "AFTER")), ob_get_clean(), 50);
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
					$nav_result = new CDBResult();
					$nav_result->InitFromArray(array_fill(0, $arEvent["COMMENTS_COUNT"], null));
					$nav_result->NavRecordCount = $arEvent["COMMENTS_COUNT"];
					$nav_result->NavNum = 1;
					$nav_result->NavStart(20, false);
				}

				$arResult["OUTPUT_LIST"] = $APPLICATION->IncludeComponent(
					"bitrix:main.post.list",
					"",
					array(
						"bPublicPage" => (isset($arParams["PUBLIC_MODE"]) && $arParams["PUBLIC_MODE"] == "Y"),
						"TEMPLATE_ID" => $tplID,
						"CONTENT_TYPE_ID" => ($commentRatingEntityTypeId ? $commentRatingEntityTypeId : ""),
						"ENTITY_XML_ID" => $arEvent["COMMENTS_PARAMS"]["ENTITY_XML_ID"],
						"POST_CONTENT_TYPE_ID" => $arResult["POST_CONTENT_TYPE_ID"],
						"COMMENT_CONTENT_TYPE_ID" => $arResult["COMMENT_CONTENT_TYPE_ID"],
						"RECORDS" => array_reverse($arRecords, true),
						"NAV_STRING" => ($tmp ? "/bitrix/components/bitrix/socialnetwork.log.entry/ajax.php?".http_build_query(array(
								"action" => "get_comments",
								"logid" => $arEvent["EVENT"]["ID"],
								"commentID" => $tmp["EVENT"]["ID"],
								"commentTS" => $tmp["LOG_DATE_TS"],
								"lastLogTs" => $arResult["LAST_LOG_TS"],
								"et" => $arEvent["EVENT"]["ENTITY_TYPE"],
								"exmlid" => $arEvent["COMMENTS_PARAMS"]["ENTITY_XML_ID"],
								"p_user" => $arParams["PATH_TO_USER"],
								"p_le" => $arParams["PATH_TO_LOG_ENTRY"],
								"p_group" => $arParams["PATH_TO_GROUP"],
								"p_dep" => $arParams["PATH_TO_CONPANY_DEPARTMENT"],
								"nt" => $arParams["NAME_TEMPLATE"],
								"sl" => $arParams["SHOW_LOGIN"],
								"dtf" => $arParams["DATE_TIME_FORMAT"],
								"dtfwoy" => $arParams["DATE_TIME_FORMAT_WITHOUT_YEAR"],
								"tf" => $arParams["TIME_FORMAT"],
								"as" => $arParams["AVATAR_SIZE"],
								"lang" => LANGUAGE_ID,
								"site" => SITE_ID,
								"follow" => ($arEvent["EVENT"]["FOLLOW"] == "Y" ? "Y" : "N"),
								"ct" => $arResult["COUNTER_TYPE"]
							)) : ""),
						"NAV_RESULT" => $nav_result,
						"PREORDER" => "N",
						"RIGHTS" => array(
							"MODERATE" => "N",
							"EDIT" => $arResult["COMMENT_RIGHTS_EDIT"],
							"DELETE" => $arResult["COMMENT_RIGHTS_DELETE"],
							"CREATETASK" => ($arResult["bTasksAvailable"] && $arResult["canGetCommentContent"] ? "Y" : "N")
						),
						"VISIBLE_RECORDS_COUNT" => count($arRecords),

						"ERROR_MESSAGE" => $arResult["ERROR_MESSAGE"],
						"OK_MESSAGE" => $arResult["OK_MESSAGE"],
						"RESULT" => $arResult["RESULT"],
						"PUSH&PULL" => array (
							"ACTION" => $_REQUEST['REVIEW_ACTION'],
							"ID" => $arResult["RESULT"]
						),
						"VIEW_URL" => (
							!empty($arResult["COMMENT_URL"])
								? $arResult["COMMENT_URL"]
								: (
									isset($arParams["PATH_TO_LOG_ENTRY"])
									&& $arParams["PATH_TO_LOG_ENTRY"] <> ''
										? CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_LOG_ENTRY"], array("log_id" => $arEvent["EVENT"]["ID"]))."?commentId=#ID#"
										: ""
								)
						),
						"EDIT_URL" => "__logEditComment('".$arEvent["COMMENTS_PARAMS"]["ENTITY_XML_ID"]."', '#ID#', '".$arEvent["EVENT"]["ID"]."');",
						"MODERATE_URL" => "",
						"DELETE_URL" => '/bitrix/components/bitrix/socialnetwork.log.entry/ajax.php?lang='.LANGUAGE_ID.'&action=delete_comment&delete_comment_id=#ID#&post_id='.$arEvent["EVENT"]["ID"].'&site='.SITE_ID,
						"AUTHOR_URL" => ($arParams["PUBLIC_MODE"] == "Y" ? "javascript:void(0);" : $arParams["PATH_TO_USER"]),

						"AVATAR_SIZE" => $arParams["AVATAR_SIZE_COMMENT"],
						"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
						"SHOW_LOGIN" => $arParams['SHOW_LOGIN'],

						"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT"],
						"DATE_TIME_FORMAT_WITHOUT_YEAR" => $arParams["DATE_TIME_FORMAT_WITHOUT_YEAR"],
						"LAZYLOAD" => $arParams["LAZYLOAD"],

						"NOTIFY_TAG" => $arEvent["COMMENTS_PARAMS"]["NOTIFY_TAGS"],
						"NOTIFY_TEXT" => TruncateText(str_replace(Array("\r\n", "\n"), " ", $arEvent["EVENT"]["MESSAGE"]), 100),
						"SHOW_MINIMIZED" => "Y",
						"SHOW_POST_FORM" => (
							isset($arEvent["HAS_COMMENTS"])
							&& $arEvent["HAS_COMMENTS"] === "Y"
							&& isset($arEvent["CAN_ADD_COMMENTS"])
							&& $arEvent["CAN_ADD_COMMENTS"] === "Y"
								? "Y"
								: "N"
						),
						"IMAGE_SIZE" => $arParams["IMAGE_SIZE"],
						"mfi" => $arParams["mfi"],
						"AUTHOR_URL_PARAMS" => array(
							"entityType" => 'LOG_ENTRY',
							"entityId" => $arEvent["EVENT"]["ID"]
						),
					),
					$this->__component
				);

				$blockClassName = "feed-comments-block";
				if (
					!empty($arResult["OUTPUT_LIST"]["DATA"])
					&& !empty($arResult["OUTPUT_LIST"]["DATA"]["NAV_STRING_COUNT_MORE"])
					&& intval($arResult["OUTPUT_LIST"]["DATA"]["NAV_STRING_COUNT_MORE"]) > 0
				)
				{
					$blockClassName .= " feed-comments-block-nav";
				}

				?><div
				 class="<?=$blockClassName?>"
				 id="feed_comments_block_<?=$arEvent["EVENT"]["ID"]?>"
				 data-bx-comments-entity-xml-id="<?=\Bitrix\Main\Text\HtmlFilter::encode($arEvent["COMMENTS_PARAMS"]["ENTITY_XML_ID"])?>"
				 data-bx-follow="<?=($arEvent['EVENT']['FOLLOW'] === 'Y' ? 'Y' : 'N')?>"
				><?
					?><?=$arResult["OUTPUT_LIST"]["HTML"]?><?
					?><script type="text/javascript">
						BX.ready(function(){
							BX.UserContentView.init();
							BX.SocialnetworkLogEntry.registerViewAreaList({
								containerId: 'feed_comments_block_<?=$arEvent["EVENT"]["ID"]?>',
								className: 'feed-com-text-inner',
								fullContentClassName: 'feed-com-text-inner-inner'
							});

							__logCommentsListRedefine("<?=$arEvent["COMMENTS_PARAMS"]["ENTITY_XML_ID"]?>", "sonet_log_day_item_<?=$ind?>", "anchor_<?=CUtil::JSEscape($anchor_id)?>", "<?=$arEvent["EVENT"]["ID"]?>");
							<?
							if (
								$USER->IsAuthorized()
								&& CModule::IncludeModule("pull")
								&& CPullOptions::GetNginxStatus())
							{
								?>
								BX.Event.EventEmitter.incrementMaxListeners('OnUCCommentWasPulled');
								BX.addCustomEvent(window, "OnUCCommentWasPulled", function(id) { if (id && id[0] == '<?=$arEvent["COMMENTS_PARAMS"]["ENTITY_XML_ID"]?>') { BX.show(BX('feed_comments_block_<?=$arEvent["EVENT"]["ID"]?>')); } });
								<?
							}
							?>
						});

					</script><?
					?><div class="feed-com-corner"></div>
				</div><?
				?></div><? // feed-comments-block-wrap
			}

			?><div class="feed-post-right-top-corner"><?

				if ($USER->IsAuthorized())
				{
					$pinned = (
						!empty($arParams['PINNED_PANEL_DATA'])
						|| (isset($arParams['EVENT']['PINNED']) && $arParams['EVENT']['PINNED'] === 'Y')
					);

					?><a href="#" class="feed-post-pinned-link feed-post-pinned-link-collapse"><?=Loc::getMessage('SONET_C30_FEED_PINNED_COLLAPSE')?></a><?
					?><div id="feed-logentry-menuanchor-right-<?=$arEvent["EVENT"]["ID"]?>" class="feed-post-right-top-menu"></div><?
					?>
					<script>
						BX.bind(BX('feed-logentry-menuanchor-right-<?=$arEvent["EVENT"]["ID"]?>'), 'click', function(e) {
							__logShowPostMenu({
								bindElement: BX('feed-logentry-menuanchor-right-<?=$arEvent["EVENT"]["ID"]?>'),
								menuElement: BX('feed-logentry-menuanchor-<?=$arEvent["EVENT"]["ID"]?>'),
								ind: '<?=$ind?>'
							});
							return BX.PreventDefault(e);
						});
					</script>
					<?
					?><div bx-data-pinned="<?=($pinned ? 'Y' : 'N')?>" class="feed-post-pin"></div><?
				}

			?></div><?


		?></div><?

		?></div><? // feed-item-wrap
	}
}
?>