<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

use Bitrix\Main\ModuleManager;
use Bitrix\Main\Update\Stepper;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI;

UI\Extension::load("socialnetwork.common");

$bodyClass = $APPLICATION->GetPageProperty("BodyClass");
$bodyClass = $bodyClass ? $bodyClass." no-paddings" : "no-paddings";
$APPLICATION->SetPageProperty("BodyClass", $bodyClass);

if(strlen($arResult["FatalError"])>0)
{
	?><span class='errortext'><?=$arResult["FatalError"]?></span><br /><br /><?
}
else
{
	if (!$arResult["AJAX_CALL"])
	{
		?><script>
		BX.message({
			SONET_C33_T_F_REQUEST_ERROR: '<?=GetMessageJS('SONET_C33_T_F_REQUEST_ERROR')?>',
			SONET_C33_T_F_SORT_ALPHA: '<?=GetMessageJS('SONET_C33_T_F_SORT_ALPHA')?>',
			SONET_C33_T_F_SORT_DATE_REQUEST: '<?=GetMessageJS('SONET_C33_T_F_SORT_DATE_REQUEST')?>',
			SONET_C33_T_F_SORT_DATE_VIEW: '<?=GetMessageJS('SONET_C33_T_F_SORT_DATE_VIEW')?>',
			SONET_C33_T_F_SORT_MEMBERS_COUNT: '<?=GetMessageJS('SONET_C33_T_F_SORT_MEMBERS_COUNT')?>',
			SONET_C33_T_F_SORT_DATE_ACTIVITY: '<?=GetMessageJS('SONET_C33_T_F_SORT_DATE_ACTIVITY')?>',
			SONET_C33_T_F_SORT_DATE_CREATE: '<?=GetMessageJS('SONET_C33_T_F_SORT_DATE_CREATE')?>',
			filterAlphaUrl: '<?=CUtil::JSEscape($APPLICATION->GetCurPageParam('order=alpha&refreshAjax=Y', array('order', 'refreshAjax')))?>',
			filterDateRequestUrl: '<?=CUtil::JSEscape($APPLICATION->GetCurPageParam('order=date_request&refreshAjax=Y', array('order', 'refreshAjax')))?>',
			filterDateViewUrl: '<?=CUtil::JSEscape($APPLICATION->GetCurPageParam('order=date_view&refreshAjax=Y', array('order', 'refreshAjax')))?>',
			filterMembersCountUrl: '<?=CUtil::JSEscape($APPLICATION->GetCurPageParam('order=members_count&refreshAjax=Y', array('order', 'refreshAjax')))?>',
			filterDateActivityUrl: '<?=CUtil::JSEscape($APPLICATION->GetCurPageParam('order=date_activity&refreshAjax=Y', array('order', 'refreshAjax')))?>',
			filterDateCreateUrl: '<?=CUtil::JSEscape($APPLICATION->GetCurPageParam('order=date_create&refreshAjax=Y', array('order', 'refreshAjax')))?>',
			SONET_C33_T_ACT_FAVORITES_ADD: '<?=GetMessageJS("SONET_C33_T_ACT_FAVORITES_ADD")?>',
			SONET_C33_T_ACT_FAVORITES_REMOVE: '<?=GetMessageJS("SONET_C33_T_ACT_FAVORITES_REMOVE")?>',
			SONET_C36_T_NO_GROUPS: '<?=GetMessageJS("SONET_C36_T_NO_GROUPS")?>',
			SONET_C36_T_NO_GROUPS_PROJECT: '<?=GetMessageJS("SONET_C36_T_NO_GROUPS_PROJECT")?>'
		});
	</script><?

		$APPLICATION->IncludeComponent(
			"bitrix:socialnetwork.group.iframe.popup",
			".default",
			array(
				"PATH_TO_GROUP" => $arParams["PATH_TO_GROUP"],
				"PATH_TO_GROUP_CREATE" => $arResult["Urls"]["GroupsAdd"],
				"ON_GROUP_ADDED" => "BX.DoNothing",
				"ON_GROUP_CHANGED" => "BX.DoNothing",
				"ON_GROUP_DELETED" => "BX.DoNothing"
			),
			null,
			array("HIDE_ICONS" => "Y")
		);

		if(strlen($arResult["ErrorMessage"])>0)
		{
			?><span class='errortext'><?=$arResult["ErrorMessage"]?></span><br /><br /><?
		}

		if (
			$arParams["PAGE"] == "groups_list"
			|| (
				in_array($arParams["PAGE"], array("user_groups", "user_projects"))
				&& isset($arResult["CurrentUserPerms"])
				&& isset($arResult["CurrentUserPerms"]["IsCurrentUser"])
				&& $arResult["CurrentUserPerms"]["IsCurrentUser"]
			)
		)
		{
			if (SITE_TEMPLATE_ID != "bitrix24")
			{
				$APPLICATION->IncludeComponent(
					"bitrix:socialnetwork.user_groups.link.add",
					".default",
					array(
						"FILTER_ID" => $arParams["FILTER_ID"],
						"HREF" => $arResult["Urls"]["GroupsAdd"],
						"PATH_TO_GROUP_CREATE" => $arResult["Urls"]["GroupsAdd"],
						"ALLOW_CREATE_GROUP" => ($arResult["CurrentUserPerms"]["IsCurrentUser"] && $arResult["ALLOW_CREATE_GROUP"] ? "Y" : "N"),
						"LIST_NAV_ID" => $arResult["NAV_ID"],
						"PROJECT" => ($arParams["PAGE"] == 'user_projects' || $arResult["filter_project"] == 'Y' ? 'Y' : 'N')
					),
					null,
					array("HIDE_ICONS" => "Y")
				);

				$APPLICATION->IncludeComponent(
					"bitrix:main.interface.buttons",
					"",
					array(
						"ID" => $arResult["menuId"],
						"ITEMS" => $arResult['menuItems'],
					)
				);
			}

			?><div class="sonet-groups-separator"></div><?

			if (
				$arParams["USE_KEYWORDS"] != "N"
				&& $arResult["filter_tags"] == "Y"
			)
			{
				if (IsModuleInstalled("search"))
				{
					?><div class="sonet-groups-tags-block"><?
					$arrFilterAdd = array("PARAMS" => array("entity" => "sonet_group"));
					$APPLICATION->IncludeComponent(
						"bitrix:search.tags.cloud",
						"",
						Array(
							"FONT_MAX" => (IntVal($arParams["FONT_MAX"]) >0 ? $arParams["FONT_MAX"] : 20),
							"FONT_MIN" => (IntVal($arParams["FONT_MIN"]) >0 ? $arParams["FONT_MIN"] : 10),
							"COLOR_NEW" => (strlen($arParams["COLOR_NEW"]) >0 ? $arParams["COLOR_NEW"] : "3f75a2"),
							"COLOR_OLD" => (strlen($arParams["COLOR_OLD"]) >0 ? $arParams["COLOR_OLD"] : "8D8D8D"),
							"ANGULARITY" => $arParams["ANGULARITY"],
							"PERIOD_NEW_TAGS" => $arResult["PERIOD_NEW_TAGS"],
							"SHOW_CHAIN" => "N",
							"COLOR_TYPE" => $arParams["COLOR_TYPE"],
							"WIDTH" => $arParams["WIDTH"],
							"SEARCH" => "",
							"TAGS" => "",
							"SORT" => "NAME",
							"PAGE_ELEMENTS" => "150",
							"PERIOD" => $arParams["PERIOD"],
							"URL_SEARCH" => $arResult["PATH_TO_GROUP_SEARCH"],
							"TAGS_INHERIT" => "N",
							"CHECK_DATES" => "Y",
							"FILTER_NAME" => "arrFilterAdd",
							"arrFILTER" => Array("socialnetwork"),
							"CACHE_TYPE" => "A",
							"CACHE_TIME" => "3600"
						),
						$component
					);
					?></div><?
				}
				else
				{
					echo "<br /><span class='errortext'>".GetMessage("SONET_C36_T_NO_SEARCH_MODULE")."</span><br /><br />";
				}
			}
		}

		?><script>
			BX.ready(function() {
				oSUG.init({
					keyboardPageNavigation: <?=(SITE_TEMPLATE_ID == 'bitrix24' ? 'true' : 'false')?>,
					navId: '<?=CUtil::JSEscape($arResult["NAV_ID"])?>',
					project: '<?=(isset($arResult["filter_project"]) && $arResult["filter_project"] == 'Y' ? 'Y' : 'N')?>',
					refreshUrl: '<?=$APPLICATION->GetCurPageParam("refreshAjax=Y", array(
						"sessid",
						"bxajaxid",
						"refreshAjax",
						"useBXMainFilter"
					), false)?>'
				});
				BX.bind(BX('bx-sonet-groups-sort'), 'click', function(e) {
					oSUG.showSortMenu({
						bindNode: BX('bx-sonet-groups-sort'),
						valueNode: BX('bx-sonet-groups-sort-value'),
						userId: <?=intval($arParams['USER_ID'])?>,
						showMembersCountItem: <?=($arParams["PAGE"] == 'groups_list' && !$arResult["filter_my"] ? 'true' : 'false')?>
					});
					return BX.PreventDefault(e);
				});
			});
		</script><?
	}

	$available = (
		in_array($arParams["PAGE"], array("groups_list", "groups_subject"))
		|| (
			$arResult["CurrentUserPerms"]["Operations"]["viewprofile"]
			&& $arResult["CurrentUserPerms"]["Operations"]["viewgroups"]
		)
	);

	$notEmptyList = ($available && $arResult["Groups"] && $arResult["Groups"]["List"]);

	?><div id="sonet-groups-content-wrap" class="sonet-groups-content-wrap<?=(!$notEmptyList ? " no-groups" : "")?>">
		<div class="sonet-groups-content-sort-container"><?
			?><span id="bx-sonet-groups-sort"><?
				?><?=GetMessage('SONET_C33_T_F_SORT')?><?
				?><span class="sonet-groups-content-sort-btn" id="bx-sonet-groups-sort-value"><?=GetMessage('SONET_C33_T_F_SORT_'.strtoupper($arResult["ORDER_KEY"]))?></span><?
			?></span><?
			?><span class="sonet-groups-search"><?
				$APPLICATION->IncludeComponent(
					"bitrix:socialnetwork.user_groups.search_form",
					".default",
					array(),
					null,
					array("HIDE_ICONS" => "Y")
				);
			?></span><?
		?></div><?

		if (
			!$arResult["AJAX_CALL"]
			&& SITE_TEMPLATE_ID == "bitrix24"
			&& (
				(
					ModuleManager::isModuleInstalled('bitrix24')
					&& \CBitrix24::isPortalAdmin($USER->getId())
				)
				|| (
					!ModuleManager::isModuleInstalled('bitrix24')
					&& $USER->isAdmin()
				)
			)
		)
		{
			echo Stepper::getHtml(array(
				'socialnetwork' => array('Bitrix\Socialnetwork\Update\WorkgroupIndex')
			), Loc::getMessage('SONET_C33_T_STEPPER_WORKGROUP'));
		}

		if ($available)
		{
			?><div id="sonet-groups-content-container"><?

				if ($arResult["AJAX_CALL"])
				{
					ob_end_clean();
					$APPLICATION->RestartBuffer();
				}

				?><div id="sonet-groups-list-container" class="sonet-groups-list-container"><?

					?><div class="sonet-groups-loader-container" id="sonet-groups-loader-container"><?
						?><svg class="sonet-groups-loader-circular" viewBox="25 25 50 50"><?
							?><circle class="sonet-groups-loader-path" cx="50" cy="50" r="20" fill="none" stroke-miterlimit="10"/><?
						?></svg><?
					?></div><?


				if ($notEmptyList)
				{
					?><div class="sonet-groups-group-block-shift">
						<div class="sonet-groups-group-block-row"><?

					/**/$i = 1;/**/
					foreach ($arResult["Groups"]["List"] as $group)
					{
						/**/if ($i > 1 && $i % 2)
						{
							?></div><div class="sonet-groups-group-block-row"><?
						}/**/

						?><div class="sonet-groups-group-block"><?
							?><span class="sonet-groups-group-img"<?=($group["GROUP_PHOTO_RESIZED_COMMON"] ? " style=\"background:#fff url('".$group["GROUP_PHOTO_RESIZED_COMMON"]["src"]."') no-repeat; background-size: cover;\"" : "")?>></span><?
							?><span class="sonet-groups-group-text"><?
								?><span class="sonet-groups-group-title<?=($group["IS_EXTRANET"] == "Y" ? " sonet-groups-group-title-extranet" : "")?>"><?
									?><span class="sonet-groups-group-title-text"><?
										?><a href="<?=$group["GROUP_URL"]?>" class="sonet-groups-group-link"><?=$group["GROUP_NAME"]?></a><?
										?><?=($group["IS_EXTRANET"] == "Y" && SITE_TEMPLATE_ID != "bitrix24" ? '<span class="sonet-groups-group-signature">'.GetMessage("SONET_C33_T_IS_EXTRANET").'</span>' : '')?><?
									?></span><?

									$isFav = (isset($group['IN_FAVORITES']) && $group['IN_FAVORITES'] == 'Y');
									?><span title="<?=GetMessage('SONET_C33_T_ACT_FAVORITES_'.($isFav ? 'REMOVE' : 'ADD'))?>" id="bx-sonet-groups-favorites-<?=intval($group['GROUP_ID'])?>" class="sonet-groups-group-title-favorites<?=($isFav ? ' sonet-groups-group-title-favorites-active' : '')?>"></span><?
									?><script>
										BX.bind(BX('bx-sonet-groups-favorites-<?=intval($group['GROUP_ID'])?>'), 'click', function(e) {
											var star = BX('bx-sonet-groups-favorites-<?=intval($group['GROUP_ID'])?>');
											var isActive = BX.hasClass(BX('bx-sonet-groups-favorites-<?=intval($group['GROUP_ID'])?>'), 'sonet-groups-group-title-favorites-active');

											oSUG.setFavorites(star, !isActive);

											oSUG.sendRequest({
												action: 'FAVORITES',
												groupId: <?=intval($group['GROUP_ID'])?>,
												value: (isActive ? 'N' : 'Y'),
												callback_success: function(data)
												{
													if (
														typeof data.NAME != 'undefined'
														&& typeof data.URL != 'undefined'
													)
													{
														BX.onCustomEvent(window, 'BX.Socialnetwork.WorkgroupFavorites:onSet', [{
															id: <?=intval($group['GROUP_ID'])?>,
															name: data.NAME,
															url: data.URL,
															extranet: (typeof data.EXTRANET != 'undefined' ? data.EXTRANET : 'N')
														}, !isActive]);
													}
												},
												callback_failure: function(errorText)
												{
													oSUG.setFavorites(star, isActive);
												}
											});
											return BX.PreventDefault(e);
										});
									</script><?
								?></span><?
								?><?=(strlen($group["GROUP_DESCRIPTION_FULL"]) > 0 ? '<span class="sonet-groups-group-description">'.$group["GROUP_DESCRIPTION_FULL"].'</span>' : "")?><?
								$membersCount = $group["NUMBER_OF_MEMBERS"];
								$suffix = (
									($membersCount % 100) > 10
									&& ($membersCount % 100) < 20
										? 5
										: $membersCount % 10
								);
								?><span class="sonet-groups-group-users"><?=GetMessage('SONET_C33_T_F_MEMBERS_'.$suffix, array('#NUM#' => $membersCount))?></span><?

								if (
									$USER->isAuthorized()
									&& (!isset($group['GROUP_CLOSED']) || $group['GROUP_CLOSED'] != "Y")
								)
								{
									?><span class="sonet-groups-group-btn-container"><?

										$requestSent = (isset($group['ROLE']) && $group['ROLE'] == \Bitrix\Socialnetwork\UserToGroupTable::ROLE_REQUEST);
										?><span id="bx-sonet-groups-request-sent-<?=intval($group['GROUP_ID'])?>" class="sonet-groups-group-desc-container<?=($requestSent ? " sonet-groups-group-desc-container-active" : "")?>"><span class="sonet-groups-group-desc-check"></span><?=GetMessage('SONET_C33_T_F_DO_REQUEST_SENT')?></span><?

										if (
											isset($group['ROLE'])
											&& empty($group['ROLE'])
										)
										{
											?><span id="bx-sonet-groups-request-<?=intval($group['GROUP_ID'])?>" class="popup-window-button"><?=GetMessage('SONET_C33_T_F_DO_REQUEST')?></span><?
											?><script>
												BX.bind(BX('bx-sonet-groups-request-<?=intval($group['GROUP_ID'])?>'), 'click', function(e) {
													var button = BX('bx-sonet-groups-request-<?=intval($group['GROUP_ID'])?>');
													var requestSentNode = BX('bx-sonet-groups-request-sent-<?=intval($group['GROUP_ID'])?>');

													oSUG.showRequestWait(button);
													oSUG.sendRequest({
														action: 'REQUEST',
														groupId: <?=intval($group['GROUP_ID'])?>,
														callback_success: function(response)
														{
															oSUG.closeRequestWait(button);
															oSUG.setRequestSent(button, requestSentNode, (typeof response != 'undefined' && typeof response.ROLE != 'undefined' ? response.ROLE : null));
														},
														callback_failure: function(errorText)
														{
															oSUG.closeRequestWait(button);
															oSUG.showError(errorText);
														}
													});
													return BX.PreventDefault(e);
												});
											</script><?
										}

									?></span><?
								}

							?></span><?
						?></div><?

						/**/$i++;/**/
					}
					/**/?></div></div><?/**/
				}
				else
				{
					if ($arResult["AJAX_CALL"])
					{
						ob_get_clean();
						echo CUtil::PhpToJSObject(array(
							"PROPS" => array(
								"EMPTY" => "Y"
							),
						));

						die();
					}

					?><div class="sonet-groups-group-message"><div class="sonet-groups-group-message-text"><?=Loc::getMessage($arParams["PAGE"] == 'user_projects' || $arResult["filter_project"] == 'Y' ? "SONET_C36_T_NO_GROUPS_PROJECT" : "SONET_C36_T_NO_GROUPS");?></div></div><?
				}

				?></div><? // sonet-groups-list-container

				if ($notEmptyList)
				{
					if (StrLen($arResult["NAV_STRING"]) > 0)
					{
						?><div id="sonet-groups-nav-container"><?=$arResult["NAV_STRING"]?></div><br /><br /><?
					}

					if ($arResult["AJAX_CALL"])
					{
						$strText = ob_get_clean();
						echo CUtil::PhpToJSObject(array(
							"PROPS" => array(
								"CONTENT" => $strText,
								"STRINGS" => array(),
								"JS" => array(),
								"CSS" => array()
							)
						));

						die();
					}
				}


			?></div><? // sonet-groups-content-container
		}
		else
		{
			?><div class="sonet-groups-group-message"><div class="sonet-groups-group-message-text"><?=GetMessage("SONET_C36_T_GR_UNAVAIL");?></div></div><?
		}
		?>
	</div><?
	if (
		SITE_TEMPLATE_ID === "bitrix24"
		&& !empty($arResult["SIDEBAR_GROUPS"])
	)
	{
		$this->SetViewTarget("sidebar");

		?><div class="sonet-sidebar">
			<div class="sonet-groups-sidebar-content">
				<div class="sonet-groups-sidebar-title">
					<span class="sonet-groups-sidebar-status-text"><?=GetMessage("SONET_C33_T_F_LAST_VIEW")?></span>
				</div>

				<div class="sonet-groups-sidebar-items"><?
					foreach ($arResult["SIDEBAR_GROUPS"] as $group)
					{
						?><div class="sonet-groups-group-block"><?
							?><span class="sonet-groups-group-img"<?=($group["IMAGE_RESIZED"] ? " style=\"background:#fff url('".$group["IMAGE_RESIZED"]["src"]."') no-repeat; background-size: cover;\"" : "") ?>></span><?
							?><span class="sonet-groups-group-text"><?
								?><span class="sonet-groups-group-title<?=($group["IS_EXTRANET"] == "Y" ? " sonet-groups-group-title-extranet" : "") ?>"><?
									?><a href="<?=$group["URL"]?>" class="sonet-groups-group-link"><?=$group["NAME"] ?></a><?
								?></span><?
								?><span class="sonet-groups-group-description"><?=(strlen($group["DESCRIPTION"]) > 0 ? $group["DESCRIPTION"] : "&nbsp;") ?></span><?
							?></span><?
						?></div><?
					}
				?></div>
			</div>
		</div>
		<?
		$this->EndViewTarget();
	}
}
?>