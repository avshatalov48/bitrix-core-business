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

$extensions = array('ajax', 'viewer', 'popup', 'clipboard');
if ($arResult["bTasksAvailable"])
{
	$extensions[] = 'tasks_util_query';
}

CJSCore::Init($extensions);
UI\Extension::load("ui.buttons");
UI\Extension::load("ui.animations");
UI\Extension::load("ui.tooltip");
UI\Extension::load("main.rating");

$bodyClass = $APPLICATION->GetPageProperty("BodyClass");
$bodyClass = $bodyClass ? $bodyClass." no-all-paddings" : "no-all-paddings";
$APPLICATION->SetPageProperty("BodyClass", $bodyClass);

?><script>
	BX.message({
		sonetBPSetPath: '<?=CUtil::JSEscape("/bitrix/components/bitrix/socialnetwork.log.ex/ajax.php")?>',
		sonetBPSiteId: '<?=CUtil::JSEscape(SITE_ID)?>',
		sonetBPPathToPost: '<?=CUtil::JSEscape($arParams["PATH_TO_POST"])?>',
		BLOG_POST_LINK_COPIED: '<?=GetMessageJS("BLOG_POST_LINK_COPIED")?>',
		sonetLMenuFavoritesTitleY: '<?=GetMessageJS("BLOG_POST_MENU_TITLE_FAVORITES_Y")?>',
		sonetLMenuFavoritesTitleN: '<?=GetMessageJS("BLOG_POST_MENU_TITLE_FAVORITES_N")?>',
		BLOG_HREF: '<?=GetMessageJS("BLOG_HREF")?>',
		BLOG_LINK: '<?=GetMessageJS("BLOG_LINK2")?>',
		BLOG_SHARE: '<?=GetMessageJS("BLOG_SHARE")?>',
		BLOG_BLOG_BLOG_EDIT: '<?=GetMessageJS("BLOG_BLOG_BLOG_EDIT")?>',
		BLOG_BLOG_BLOG_DELETE: '<?=GetMessageJS("BLOG_BLOG_BLOG_DELETE")?>',
		BLOG_MES_DELETE_POST_CONFIRM: '<?=GetMessageJS("BLOG_MES_DELETE_POST_CONFIRM")?>',
		BLOG_POST_CREATE_TASK: '<?=GetMessageJS("BLOG_POST_CREATE_TASK")?>',
		BLOG_POST_VOTE_EXPORT: '<?=GetMessageJS("BLOG_POST_VOTE_EXPORT")?>',
		BLOG_MES_HIDE: '<?=GetMessageJS("BLOG_MES_HIDE")?>',
		BLOG_MES_HIDE_POST_CONFIRM: '<?=GetMessageJS("BLOG_MES_HIDE_POST_CONFIRM")?>'
		<?
		if (!$arResult["bFromList"])
		{
			?>,
			sonetLESetPath: '<?=CUtil::JSEscape('/bitrix/components/bitrix/socialnetwork.log.entry/ajax.php')?>',
			sonetLSessid: '<?=bitrix_sessid_get()?>',
			sonetLLangId: '<?=CUtil::JSEscape(LANGUAGE_ID)?>',
			sonetLSiteId: '<?=CUtil::JSEscape(SITE_ID)?>',
			sonetLFCreateTaskWait: '<?=GetMessageJS("BLOG_POST_CREATE_TASK_WAIT")?>',
			sonetLFCreateTaskButtonTitle: '<?=GetMessageJS("BLOG_POST_CREATE_TASK_BUTTON_TITLE")?>',
			sonetLFCreateTaskSuccessTitle: '<?=GetMessageJS("BLOG_POST_CREATE_TASK_SUCCESS_TITLE")?>',
			sonetLFCreateTaskFailureTitle: '<?=GetMessageJS("BLOG_POST_CREATE_TASK_FAILURE_TITLE")?>',
			sonetLFCreateTaskSuccessDescription: '<?=GetMessageJS("BLOG_POST_CREATE_TASK_SUCCESS_DESCRIPTION")?>',
			sonetLFCreateTaskErrorGetData: '<?=GetMessageJS("BLOG_POST_CREATE_TASK_ERROR_GET_DATA")?>',
			sonetLFCreateTaskTaskPath: '<?=CUtil::JSEscape(\Bitrix\Main\Config\Option::get('socialnetwork', 'user_page', SITE_DIR.'company/personal/'))?>user/#user_id#/tasks/task/view/#task_id#/'
			<?
		}

		if ($arResult["canDelete"])
		{
			?>,
			sonetBPDeletePath: '<?=CUtil::JSEscape($arResult["urlToDelete"])?>'
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
				tagLinkPattern: '<?=(!empty($arParams["PATH_TO_LOG_TAG"]) ? CUtil::JSEscape($arParams["PATH_TO_LOG_TAG"]) : '')?>'
			});
		}
	});
</script><?

?><div class="feed-item-wrap"><?
if(strlen($arResult["MESSAGE"])>0)
{
	?><div class="feed-add-successfully">
		<span class="feed-add-info-text"><span class="feed-add-info-icon"></span><?=$arResult["MESSAGE"]?></span>
	</div><?
}
if(strlen($arResult["ERROR_MESSAGE"])>0)
{
	?><div class="feed-add-error">
		<span class="feed-add-info-text"><span class="feed-add-info-icon"></span><?=$arResult["ERROR_MESSAGE"]?></span>
	</div><?
}
if(strlen($arResult["FATAL_MESSAGE"])>0)
{
	if(!$arResult["bFromList"])
	{
		?><div class="feed-add-error">
			<span class="feed-add-info-text"><span class="feed-add-info-icon"></span><?=$arResult["FATAL_MESSAGE"]?></span>
		</div><?
	}
}
elseif(strlen($arResult["NOTE_MESSAGE"])>0)
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

		$className = "feed-post-block";

		if(
//			true || $arResult["Post"]["new"] == "Y"
			$arResult["Post"]["new"] == "Y"
		)
		{
			$className .= " feed-post-block-new";
		}

		if ($arResult["Post"]["IS_IMPORTANT"])
		{
			$className .= " feed-imp-post";
		}

		if (
			$arResult["Post"]["HAS_TAGS"] == "Y"
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
			$className .= " feed-post-block-files";
		}

		if (
			isset($arResult["POST_PROPERTIES"]["DATA"]["UF_BLOG_POST_VOTE"])
			&& !empty($arResult["POST_PROPERTIES"]["DATA"]["UF_BLOG_POST_VOTE"]["VALUE"])
		)
		{
			$className .= " feed-post-block-vote";
		}

		if (!empty($arResult["GRATITUDE"]))
		{
			$className .= " feed-post-block-grat";
		}

		if (
			!$arResult["ReadOnly"]
			&& array_key_exists("FOLLOW", $arParams)
			&& strlen($arParams["FOLLOW"]) > 0
			&& intval($arParams["LOG_ID"]) > 0
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

			<?
			$postDest = array();

			if (
				!$arResult["bPublicPage"]
				&& is_set($arResult["Post"]["SPERM"])
				&& is_array($arResult["Post"]["SPERM"])
			)
			{
				foreach($arResult["Post"]["SPERM"] as $type => $ar)
				{
					if (!in_array($type, array('U', 'SG', 'DR', 'G')))
					{
						continue;
					}

					$typeText = "users";
					if($type == "SG")
						$typeText = "sonetgroups";
					elseif($type == "G")
						$typeText = "groups";
					elseif($type == "DR")
						$typeText = "department";

					foreach($ar as $id => $val)
					{
						$postDest[] = (
							$type == "U"
							&& IntVal($val["ID"]) <= 0
								? array("id" => "UA", "name" => ($arResult["bIntranetInstalled"] ? GetMessage("BLOG_DESTINATION_ALL") : GetMessage("BLOG_DESTINATION_ALL_BSM")), "type" => "groups")
								: array("id" => $type.$id, "name" => $val["NAME"], "type" => $typeText, "entityId" => $type.$id)
						);
					}
				}
			}

			?>
			var postDest<?=$arResult["Post"]["ID"]?> = <?=\CUtil::phpToJSObject($postDest)?>;

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
		</script>
		<div class="<?=$className?>" id="blg-post-<?=$arResult["Post"]["ID"]?>">
			<?
			$aditStyles = ($arResult["Post"]["hidden"] == "Y" ? " feed-hidden-post" : "");

			if (array_key_exists("USER_ID", $arParams) && intval($arParams["USER_ID"]) > 0)
				$aditStyles .= " sonet-log-item-createdby-".$arParams["USER_ID"];

			if (array_key_exists("ENTITY_TYPE", $arParams) && strlen($arParams["ENTITY_TYPE"]) > 0 && array_key_exists("ENTITY_ID", $arParams) && intval($arParams["ENTITY_ID"]) > 0 )
			{
				$aditStyles .= " sonet-log-item-where-".$arParams["ENTITY_TYPE"]."-".$arParams["ENTITY_ID"]."-all";
				if (array_key_exists("EVENT_ID", $arParams) && strlen($arParams["EVENT_ID"]) > 0)
				{
					$aditStyles .= " sonet-log-item-where-".$arParams["ENTITY_TYPE"]."-".$arParams["ENTITY_ID"]."-".str_replace("_", '-', $arParams["EVENT_ID"]);
					if (array_key_exists("EVENT_ID_FULLSET", $arParams) && strlen($arParams["EVENT_ID_FULLSET"]) > 0)
						$aditStyles .= " sonet-log-item-where-".$arParams["ENTITY_TYPE"]."-".$arParams["ENTITY_ID"]."-".str_replace("_", '-', $arParams["EVENT_ID_FULLSET"]);
				}
			}

			$avatar = false;
			if (
				isset($arResult["arUser"]["PERSONAL_PHOTO_resized"]["src"]) &&
				strlen($arResult["arUser"]["PERSONAL_PHOTO_resized"]["src"]) > 0
			)
			{
				$avatar = $arResult["arUser"]["PERSONAL_PHOTO_resized"]["src"];
			}
			?>
			<div class="feed-post-cont-wrap<?=$aditStyles?>" id="blg-post-img-<?=$arResult["Post"]["ID"]?>">
				<div class="feed-user-avatar"
					<? if ($avatar):?>
						style="background: url('<?=$avatar?>'); background-size: cover;"
					<? endif ?>
				></div>
				<div class="feed-post-title-block"><?
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

					if (!empty($arResult["Post"]["SPERM_SHOW"]))
					{
						?><span class="feed-add-post-destination-cont<?=($arResult["Post"]["LIMITED_VIEW"] ? ' feed-add-post-destination-limited-view' : '')?>"><?

						?><span class="feed-add-post-destination-icon"><span style="position: absolute; left: -3000px; overflow: hidden;">&nbsp;-&gt;&nbsp;</span></span><?

						$cnt =
							count($arResult["Post"]["SPERM_SHOW"]["U"]) +
							count($arResult["Post"]["SPERM_SHOW"]["SG"]) +
							count($arResult["Post"]["SPERM_SHOW"]["DR"]) +
							(isset($arResult["Post"]["SPERM_SHOW"]["CRMCONTACT"]) ? count($arResult["Post"]["SPERM_SHOW"]["CRMCONTACT"]) : 0);

						$showCnt = (
							(!empty($arResult["Post"]["SPERM_SHOW"]["U"]) ? count($arResult["Post"]["SPERM_SHOW"]["U"]) : 0)
							+ (!empty($arResult["Post"]["SPERM_SHOW"]["SG"]) ? count($arResult["Post"]["SPERM_SHOW"]["SG"]) : 0)
							+ (!empty($arResult["Post"]["SPERM_SHOW"]["DR"]) ? count($arResult["Post"]["SPERM_SHOW"]["DR"]) : 0)
							+ (!empty($arResult["Post"]["SPERM_SHOW"]["CRMCONTACT"]) ? count($arResult["Post"]["SPERM_SHOW"]["CRMCONTACT"]) : 0)
						);

						$showMaxCount = ($showCnt > 4 ? 4 : 5);

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
									$className = "feed-add-post-destination-new";
									$arTooltipParams = array();

									if (
										array_key_exists("IS_EXTRANET", $val)
										&& $val["IS_EXTRANET"] == "Y"
									)
									{
										$className .= " feed-add-post-destination-new-extranet";
									}
									elseif ($val["IS_EMAIL"] == "Y")
									{
										$className .= " feed-add-post-destination-new-email";
										$arTooltipParams = array(
											'entityType' => 'LOG_ENTRY',
											'entityId' => intval($arParams['LOG_ID'])
										);
									}

									if ($arResult["bPublicPage"])
									{
										?><span
										 id="dest_<?=$anchor_id?>"
										 class="<?=$className?>"
										 bx-tooltip-user-id="<?=$val["ID"]?>"
										 bx-tooltip-params="<?=htmlspecialcharsbx(\Bitrix\Main\Web\Json::encode($arTooltipParams))?>"
										><?=$val["NAME"]?></span><?
									}
									else
									{
										?><a
										 id="dest_<?=$anchor_id?>"
										 href="<?=$val["URL"]?>"
										 class="<?=$className?>"
										 bx-tooltip-user-id="<?=$val["ID"]?>"
										 bx-tooltip-params="<?=htmlspecialcharsbx(\Bitrix\Main\Web\Json::encode($arTooltipParams))?>"
										><?=$val["NAME"]?></a><?
									}
								}
								else
								{
									if (
										strlen($val["URL"]) > 0
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
									strlen($val["URL"]) > 0
									&& !$arResult["bPublicPage"]
								)
								{
									?><a href="<?=$val["URL"]?>" class="feed-add-post-destination-new<?=(array_key_exists("IS_EXTRANET", $val) && $val["IS_EXTRANET"] == "Y" ? " feed-add-post-destination-new-extranet" : "")?>"><?=$val["NAME"]?></a><?
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
									strlen($val["URL"]) > 0
									&& !$arResult["bExtranetSite"]
								)
								{
									?><a href="<?=$val["URL"]?>" class="feed-add-post-destination-new"><?=$val["NAME"]?></a><?
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
									strlen($val["URL"]) > 0
									&& !$arResult["bPublicPage"]
								)
								{
									?><a href="<?=$val["URL"]?>" class="feed-add-post-destination-new"><?=$val["NAME"]?></a><?
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
						strlen($arResult["urlToEdit"]) > 0
						&& (
							$arResult["PostPerm"] > BLOG_PERMS_MODERATE
							|| (
								$arResult["PostPerm"] >= BLOG_PERMS_WRITE
								&& $arResult["Post"]["AUTHOR_ID"] == $arResult["USER_ID"]
							)
						)
					)
					{
						?><a href="<?=$arResult["urlToEdit"]?>" title="<?=GetMessage("BLOG_BLOG_BLOG_EDIT")?>"><span class="feed-destination-edit" onclick="BX.addClass(this, 'feed-destination-edit-pressed');"></span></a><?
					}

					$datetime_detail = CComponentUtil::GetDateTimeFormatted(MakeTimeStamp($arResult["Post"]["DATE_PUBLISH"]), $arParams["DATE_TIME_FORMAT"], CTimeZone::GetOffset());

					?><div class="feed-post-time-wrap"><?
						if ($arResult["bPublicPage"])
						{
							?><div class="feed-time"><?=$datetime_detail?></div><?
						}
						else
						{
							?><a href="<?=$arResult["Post"]["urlToPost"]?>"><div class="feed-time"><?=$datetime_detail?></div></a><?
						}
					?></div><?

					if($arResult["Post"]["MICRO"] != "Y")
					{
						if ($arResult["bPublicPage"])
						{
							?><div class="feed-post-item"><span class="feed-post-title"><?=$arResult["Post"]["TITLE"]?></span></div><?
						}
						else
						{
							?><div class="feed-post-item"><a class="feed-post-title" href="<?=$arResult["Post"]["urlToPost"]?>"><?=$arResult["Post"]["TITLE"]?></a></div><?
						}
					}

				?></div><? // feed-post-title-block
				?><div class="feed-post-text-block<?=($arResult["Post"]["IS_IMPORTANT"] ? " feed-info-block" : "")?>" id="blog_post_outer_<?=$arResult["Post"]["ID"]?>"><?
					$className = "";
					if ($arResult["bFromList"])
					{
						$className .= " feed-post-contentview feed-post-text-block-inner";
					}
					?><div class="<?=$className?>"<?if($arResult["bFromList"]) {?> id="feed-post-contentview-BLOG_POST-<?=intval($arResult["Post"]["ID"])?>" bx-content-view-xml-id="BLOG_POST-<?=intval($arResult["Post"]["ID"])?>"<? }?>>
						<div class="feed-post-text-block-inner-inner" id="blog_post_body_<?=$arResult["Post"]["ID"]?>"><?=$arResult["Post"]["textFormated"]?><?

						if (
							$arResult["POST_PROPERTIES"]["SHOW"] == "Y"
							&& array_key_exists("UF_BLOG_POST_VOTE", $arResult["POST_PROPERTIES"]["DATA"])
						)
						{
							$arPostField = $arResult["POST_PROPERTIES"]["DATA"]["UF_BLOG_POST_VOTE"];
							if(!empty($arPostField["VALUE"]))
							{
								$voteId = $arPostField["VALUE"];
								$arPostField['BLOG_DATE_PUBLISH'] = $arResult["Post"]["DATE_PUBLISH"];
							}
						}

						if ($arResult["Post"]["CUT"] == "Y")
						{
							?><div><a class="blog-postmore-link" href="<?=$arResult["Post"]["urlToPost"]?>"><?=GetMessage("BLOG_BLOG_BLOG_MORE")?></a></div><?
						}
						if (!empty($arResult["Post"]["IMPORTANT"]))
						{
							?><div class="feed-imp-post-footer"><?
								?><span class="feed-imp-btn-main-wrap"><?
									if ($arResult["Post"]["IMPORTANT"]["IS_READ"] == "Y")
									{
										?><span class="feed-imp-btn-wrap">
											<span class="have-read-text-block"><i></i><?=GetMessage('BLOG_ALREADY_READ')?><span class="feed-imp-post-footer-comma">,</span></span>
										</span><?
									}
									else
									{
										?><span class="feed-imp-btn-wrap"><?
											?><button
												class="ui-btn ui-btn-lg ui-btn-success"
												id="blog-post-readers-btn-<?=$arResult["Post"]["ID"]?>"
												bx-blog-post-id="<?=$arResult["Post"]["ID"]?>"
												bx-url="<?=htmlspecialcharsbx($arResult["arUser"]["urlToPostImportant"])?>"
												onclick="new SBPImpPost(this); return false;"
											><?=GetMessage(trim("BLOG_READ_".$arResult["Post"]["IMPORTANT"]["USER"]["PERSONAL_GENDER"]))?></button><?
										?></span><?
									}
								?></span><?
								?><span <?
									?>id="blog-post-readers-count-<?=$arResult["Post"]["ID"]?>" <?
									?>class="feed-imp-post-footer-text"<?
									if($arResult["Post"]["IMPORTANT"]["COUNT"]<=0)
									{
										?> style="display:none;"<?
									}
									?>><?=GetMessage("BLOG_USERS_ALREADY_READ")?> <a class="feed-imp-post-user-link" href="javascript:void(0);"><?
									?><span><?=$arResult["Post"]["IMPORTANT"]["COUNT"]?></span> <?=GetMessage("BLOG_READERS")?></a></span>
							</div>
							<script type="text/javascript">
								BX.ready(function(){
									var sbpimp<?=$arResult["Post"]["ID"]?> =  new SBPImpPostCounter(
										BX('blog-post-readers-count-<?=$arResult["Post"]["ID"]?>'),
										<?=$arResult["Post"]["ID"]?>, { 'pathToUser' : '<?=CUtil::JSEscape($arParams["~PATH_TO_USER"])?>', 'nameTemplate' : '<?=CUtil::JSEscape($arParams["NAME_TEMPLATE"])?>' }
									);
									BX.addCustomEvent(BX('blog-post-readers-btn-<?=$arResult["Post"]["ID"]?>'), "onInit", BX.proxy(sbpimp<?=$arResult["Post"]["ID"]?>.click, sbpimp<?=$arResult["Post"]["ID"]?>));
									BX.message({'BLOG_ALREADY_READ' : '<?=GetMessageJS('BLOG_ALREADY_READ')?>'});
								});
							</script><?
						}
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
							if (typeof oLF != 'undefined')
							{
								BX.ready(function() {
									oLF.arMoreButtonID.push({
										outerBlockID : 'blog_post_outer_<?=$arResult["Post"]["ID"]?>',
										bodyBlockID : 'blog_post_body_<?=$arResult["Post"]["ID"]?>',
										moreButtonBlockID : 'blog_post_more_<?=$arResult["Post"]["ID"]?>',
										informerBlockID : 'blg-post-inform-<?=intval($arResult["Post"]["ID"])?>'
									});
								});
							}
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

				if($arResult["POST_PROPERTIES"]["SHOW"] == "Y")
				{
					$eventHandlerID = false;
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
									)
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

				if (!empty($arResult["GRATITUDE"]))
				{
					$grat_users_count = count($arResult["GRATITUDE"]["USERS_FULL"]);

					?><div class="feed-grat-block feed-info-block<?=($grat_users_count > 4 ? " feed-grat-block-small" : " feed-grat-block-large")?>"><?

					if ($grat_users_count <= 4)
					{
						?><span class="feed-workday-left-side"><?
							?><div class="feed-grat-img<?=(is_array($arResult["GRATITUDE"]["TYPE"]) ? " feed-grat-img-".htmlspecialcharsbx($arResult["GRATITUDE"]["TYPE"]["XML_ID"]) : "")?>"></div><?
							?><div class="feed-grat-block-arrow"></div><?
							?><div class="feed-user-name-wrap-outer"><?
								foreach($arResult["GRATITUDE"]["USERS_FULL"] as $arGratUser)
								{
									$anchor_id = 'post_grat_'.$arGratUser["ID"].'_'.RandString(5);
									$avatar = false;
									if (isset($arGratUser["AVATAR_SRC"]) && strlen($arGratUser["AVATAR_SRC"]) > 0)
									{
										$avatar = $arGratUser["AVATAR_SRC"];
									}
									?><span class="feed-user-name-wrap">
										<div class="feed-user-avatar"
											<? if ($avatar):?>
												style="background: url('<?=$avatar?>'); background-size: cover;"
											<? endif ?>>
										</div>
										<div class="feed-user-name-wrap-inner"><?
											?><a class="feed-workday-user-name" href="<?=($arGratUser['URL'] ? $arGratUser['URL'] : 'javascript:void(0);')?>"
											 id="<?=$anchor_id?>"
											 bx-tooltip-user-id="<?=($arGratUser['URL'] ? $arGratUser['ID'] : "")?>"
											><?=CUser::FormatName($arParams['NAME_TEMPLATE'], $arGratUser)?></a><?
											?><span class="feed-workday-user-position"><?=htmlspecialcharsbx($arGratUser['WORK_POSITION'])?></span>
										</div><?
									?></span><?
								}
							?></div><?
						?></span><?
					}
					else
					{
						?><div class="feed-grat-small-left"><?
							?><div class="feed-grat-img<?=(is_array($arResult["GRATITUDE"]["TYPE"]) ? " feed-grat-img-".htmlspecialcharsbx($arResult["GRATITUDE"]["TYPE"]["XML_ID"]) : "")?>"></div><?
							?><div class="feed-grat-block-arrow"></div><?
						?></div><?
						?><div class="feed-grat-small-block-names"><?
							foreach($arResult["GRATITUDE"]["USERS_FULL"] as $arGratUser)
							{
								$anchor_id = 'post_grat_'.$arGratUser["ID"].'_'.RandString(5);
								$avatar = false;
								if (isset($arGratUser["AVATAR_SRC"]) && strlen($arGratUser["AVATAR_SRC"]) > 0)
								{
									$avatar = $arGratUser["AVATAR_SRC"];
								}
								?><span class="feed-user-name-wrap">
									<div class="feed-user-avatar"
										<? if ($avatar):?>
											style="background: url('<?=$avatar?>'); background-size: cover;"
										<? endif ?>>
									</div><?
									?><a class="feed-workday-user-name"
									 href="<?=($arGratUser['URL'] ? $arGratUser['URL'] : 'javascript:void(0);')?>"
									 id="<?=$anchor_id?>"
									 bx-tooltip-user-id="<?=($arGratUser['URL'] ? $arGratUser['ID'] : "")?>"
									><?=CUser::FormatName($arParams['NAME_TEMPLATE'], $arGratUser)?></a><?
									?><!--<span class="feed-workday-user-position"><?=htmlspecialcharsbx($arGratUser['WORK_POSITION'])?></span>--><?
								?></span><?
							}
						?></div><?
					}
					?></div><?
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
									"arUserField" => $arPostField,
								), 
								null, 
								array("HIDE_ICONS"=>"Y")
							);?><?
							echo "</div>";
						}
					}
				}
				?><div id="blg-post-destcont-<?=$arResult["Post"]["ID"]?>"></div><?

				$separatorClassName = 'feed-post-informers';
				if (
					!$arResult["bFromList"]
					|| $arResult["Post"]["HAS_TAGS"] == "Y"
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
					$separatorClassName .= ' feed-post-informers-separator';
				}

				?><div class="<?=$separatorClassName?>" id="blg-post-inform-<?=$arResult["Post"]["ID"]?>"><div class="feed-post-informers-cont"><?

					$voteId = false;
					if ($arParams["SHOW_RATING"] == "Y")
					{
						$voteId = "BLOG_POST".'_'.$arResult["Post"]["ID"].'-'.(time()+rand(0, 1000));
						$emotion = (!empty($arResult["RATING"][$arResult["Post"]["ID"]]["USER_REACTION"]) ? strtoupper($arResult["RATING"][$arResult["Post"]["ID"]]["USER_REACTION"]) : 'LIKE');


						if ($arResult["bIntranetInstalled"])
						{
							?><span id="bx-ilike-button-<?=htmlspecialcharsbx($voteId)?>" class="feed-inform-ilike feed-new-like"><?
							?><span class="bx-ilike-left-wrap<?=(isset($arResult["RATING"]) && isset($arResult["RATING"][$arResult["Post"]["ID"]]) && isset($arResult["RATING"][$arResult["Post"]["ID"]]["USER_HAS_VOTED"]) && $arResult["RATING"][$arResult["Post"]["ID"]]["USER_HAS_VOTED"] == "Y" ? ' bx-you-like-button' : '')?>"><a href="#like" class="bx-ilike-text"><?=\CRatingsComponentsMain::getRatingLikeMessage($emotion)?></a></span><?
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

					if(!in_array($arParams["TYPE"], array("DRAFT", "MODERATION")))
					{
						$bHasComments = (IntVal($arResult["PostSrc"]["NUM_COMMENTS"]) > 0);
						?><span class="feed-inform-comments"><?
							?><a href="javascript:void(0);" id="blog-post-addc-add-<?=$arResult["Post"]["ID"]?>"><?=GetMessage("BLOG_COMMENTS_ADD")?></a><?
						?></span><?
					}

					if (
						!$arResult["ReadOnly"]
						&& array_key_exists("FOLLOW", $arParams)
						&& strlen($arParams["FOLLOW"]) > 0
						&& intval($arParams["LOG_ID"]) > 0
					)
					{
						?><span class="feed-inform-follow" data-follow="<?=($arParams["FOLLOW"] == "Y" ? "Y" : "N")?>" id="log_entry_follow_<?=intval($arParams["LOG_ID"])?>" onclick="__blogPostSetFollow(<?=intval($arParams["LOG_ID"])?>)"><a href="javascript:void(0);"><?=GetMessage("BLOG_POST_FOLLOW_".($arParams["FOLLOW"] == "Y" ? "Y" : "N"))?></a></span><?
					}

					?><a id="feed-post-menuanchor-<?=$arResult["Post"]["ID"]?>" href="#" class="feed-post-more-link"><span class="feed-post-more-text" id="feed-post-more-<?=$arResult["Post"]["ID"]?>"><?=GetMessage("BLOG_POST_BUTTON_MORE")?></span><span class="feed-post-more-arrow"></span></a><?
					?><script>
						BX.bind(BX('feed-post-menuanchor-<?=$arResult["Post"]["ID"]?>'), 'click', function(e) {
							BX.SBPostMenu.showMenu({
								event: e,
								bindNode: this,
								postId: <?=$arResult["Post"]["ID"]?>,
								pathToPost: '<?=CUtil::JSEscape($arParams["PATH_TO_POST"])?>',
								publicPage: <?=($arResult["bPublicPage"] ? 'true' : 'false')?>,
								tasksAvailable: <?=($arResult["bTasksAvailable"] ? 'true' : 'false')?>,
								urlToEdit: '<?=(strlen($arResult["urlToEdit"]) > 0 ? CUtil::JSEscape($arResult["urlToEdit"]) : '')?>',
								urlToHide: '<?=(strlen($arResult["urlToHide"]) > 0 ? CUtil::JSEscape($arResult["urlToHide"]) : '')?>',
								urlToDelete: '<?=(!$arResult["bFromList"] && strlen($arResult["urlToDelete"]) > 0 ? CUtil::JSEscape($arResult["urlToDelete"]) : '')?>',
								voteId: <?=(intval($voteId) > 0 ? intval($voteId) : 'false')?>,
								postType: '<?=CUtil::JSEscape($arParams["TYPE"])?>',
								group_readonly: <?=($arResult["ReadOnly"] ? 'true' : 'false')?>,
								serverName: '<?=CUtil::JSEscape((CMain::IsHTTPS() ? "https" : "http")."://".((defined("SITE_SERVER_NAME") && strlen(SITE_SERVER_NAME) > 0) ? SITE_SERVER_NAME : COption::GetOptionString("main", "server_name", "")))?>',
								items: <?=\CUtil::phpToJSObject(!empty($arParams["ADIT_MENU"]) ? $arParams["ADIT_MENU"] : array())?>
							});
							return BX.PreventDefault(e);
						});
					</script><?

					?><span class="feed-post-time-wrap"><?
						if (
							!$arResult["bPublicPage"]
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
				?></div><?

			?></div></div><?


		if (!in_array($arParams["TYPE"], array("DRAFT", "MODERATION")))
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

			if ($arResult["CommentPerm"] >= BLOG_PERMS_READ)
			{
				$APPLICATION->IncludeComponent(
					"bitrix:socialnetwork.blog.post.comment",
					"",
					Array(
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
						"RATING_TYPE" => ($arParams["RATING_TYPE"] == "like" ? "like_react" : $arParams["RATING_TYPE"]),
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
						"CREATED_BY_ID" => $arParams["CREATED_BY_ID"],
						"MOBILE" => $arParams["MOBILE"],
						"LAZYLOAD" => $arParams["LAZYLOAD"],
						"CAN_USER_COMMENT" => (!isset($arResult["CanComment"]) || $arResult["CanComment"] ? 'Y' : 'N'),
						"NAV_TYPE_NEW" => "Y"
					),
					$component
				);
			}
		}

		if (
			!$arResult["bPublicPage"]
			&& !$arResult["ReadOnly"]
			&& intval($arParams["LOG_ID"]) > 0
			&& array_key_exists("FAVORITES_USER_ID", $arParams)
		)
		{
			$bFavorites = (intval($arParams["FAVORITES_USER_ID"]) > 0);
			?><div id="log_entry_favorites_<?=intval($arParams["LOG_ID"])?>" onmousedown="__logChangeFavorites(<?=$arParams["LOG_ID"]?>, this, '<?=($bFavorites ? "N" : "Y")?>'); this.blur(); return BX.PreventDefault(this);" class="feed-post-important-switch<?=($bFavorites ? " feed-post-important-switch-active" : "")?>" title="<?=GetMessage("BLOG_POST_MENU_TITLE_FAVORITES_N")?>"></div><?
		}

		?></div><?
	}
	elseif(!$arResult["bFromList"])
	{
		echo GetMessage("BLOG_BLOG_BLOG_NO_AVAIBLE_MES");
	}
}
?></div>