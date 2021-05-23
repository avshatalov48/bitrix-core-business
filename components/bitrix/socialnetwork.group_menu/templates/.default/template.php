<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */
CUtil::InitJSCore(array("ajax", "popup"));
?>
<div style="margin-bottom: 1em;"><?
	?><div style="float: left;"><?
		$APPLICATION->IncludeComponent(
			"bitrix:socialnetwork.menu", 
			"", 
			Array(
				"MAX_ITEMS" => $arParams["MAX_ITEMS"],
				"ENTITY_TYPE" => SONET_ENTITY_GROUP,
				"ENTITY_ID" => $arParams["GROUP_ID"],
				"PAGE_ID" => $arParams["PAGE_ID"],
				"USE_MAIN_MENU" => $arParams["USE_MAIN_MENU"],
				"MAIN_MENU_TYPE" => $arParams["MAIN_MENU_TYPE"],
				"arResult" => $arResult,
				"GeneralName" => GetMessage("SONET_UM_GENERAL"),
				"UsersName" => GetMessage("SONET_UM_USERS"),
			),
			$component,
			array("HIDE_ICONS" => "Y")
		);
	?></div><?
	?><div style="float: left;"><?
		?><script>
			BX.message({
				SGMErrorSessionWrong: '<?=GetMessageJS("SONET_SGM_T_SESSION_WRONG")?>',
				SGMErrorCurrentUserNotAuthorized: '<?=GetMessageJS("SONET_SGM_T_NOT_ATHORIZED")?>',
				SGMErrorModuleNotInstalled: '<?=GetMessageJS("SONET_SGM_T_MODULE_NOT_INSTALLED")?>',
				SGMWaitTitle: '<?=GetMessageJS("SONET_SGM_T_WAIT")?>',
				SGMSubscribeButtonHintOn: '<?=GetMessageJS("SONET_SGM_T_NOTIFY_HINT_ON")?>',
				SGMSubscribeButtonHintOff: '<?=GetMessageJS("SONET_SGM_T_NOTIFY_HINT_OFF")?>',
				SGMSubscribeButtonTitleOn: '<?=GetMessageJS("SONET_SGM_T_NOTIFY_TITLE_ON")?>',
				SGMSubscribeButtonTitleOff: '<?=GetMessageJS("SONET_SGM_T_NOTIFY_TITLE_OFF")?>'
			});
		</script><?
		if (in_array($arResult["CurrentUserPerms"]["UserRole"], array(SONET_ROLES_OWNER, SONET_ROLES_MODERATOR, SONET_ROLES_USER)))
		{
			?><a id="group_menu_subscribe_button" class="profile-menu-notify-btn<?=($arResult["bSubscribed"] ? " profile-menu-notify-btn-active" : "")?>" title="<?=GetMessage("SONET_SGM_T_NOTIFY_TITLE_".($arResult["bSubscribed"] ? "ON" : "OFF"))?>" href="#" onclick="__SGMSetSubscribe(<?=$arParams["GROUP_ID"]?>, event);" style="position: relative; bottom: -2px; margin-left: -44px;"></a><?
		}
	?></div><?
	?><div style="clear: both;"></div><?
?></div>