<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if (
	$arReturnGroupMenu
	&& array_key_exists("CURRENT_USER_PERMS", $arReturnGroupMenu)
	&& isset($arReturnGroupMenu["CURRENT_USER_PERMS"]["UserRole"])
	&& in_array($arReturnGroupMenu["CURRENT_USER_PERMS"]["UserRole"], array(SONET_ROLES_OWNER, SONET_ROLES_MODERATOR, SONET_ROLES_USER))
)
{
	$file = trim(preg_replace("'[\\\\/]+'", "/", (dirname(__FILE__)."/lang/".LANGUAGE_ID."/util_community.php")));
	__IncludeLang($file);

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

	CUtil::InitJSCore(array("ajax", "popup"));
	$GLOBALS["APPLICATION"]->AddHeadScript("/bitrix/components/bitrix/socialnetwork.group_menu/templates/.default/script.js");
	$GLOBALS["APPLICATION"]->SetAdditionalCSS("/bitrix/components/bitrix/socialnetwork.group_menu/templates/.default/style.css");

	if (
		strpos(SITE_TEMPLATE_ID, "stretchy") === 0
		|| strpos(SITE_TEMPLATE_ID, "taby") === 0
	)
	{
		$this->SetViewTarget("sidebar", 5);
		?><style>
			#sidebar { position: relative; }
			#sidebar .content-title { padding-right: 23px; }
		</style>
		<a id="group_menu_subscribe_button" class="profile-menu-notify-btn<?=($arReturnGroupMenu["IS_SUBSCRIBED"] ? " profile-menu-notify-btn-active" : "")?>" title="<?=GetMessage("SONET_SGM_T_NOTIFY_TITLE_".($arReturnGroupMenu["IS_SUBSCRIBED"] ? "ON" : "OFF"))?>" href="#" onclick="__SGMSetSubscribe(<?=$arResult["VARIABLES"]["group_id"]?>, event);" style="z-index: 100; position: absolute; top: <?=(strpos(SITE_TEMPLATE_ID, "stretchy") === 0 ? "60" : "93")?>px; right: 6px;"></a><?
		$this->EndViewTarget();
	}
}
?>