<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if(strlen($arResult["FatalError"])>0)
{
	?><span class='errortext'><?=$arResult["FatalError"]?></span><br /><br /><?
}
else
{
	CUtil::InitJSCore(array("ajax", "popup"));

	if(strlen($arResult["ErrorMessage"])>0)
	{
		?><span class="errortext"><?=$arResult["ErrorMessage"]?></span><br /><br /><?
	}
	
	if (
		$arResult["SHOW_BANNER"]
		|| $arParams["PROCESS_ONLY"] == "Y"
	)
	{
		?><script>

			BX.message({
				SASErrorSessionWrong: '<?=CUtil::JSEscape(GetMessage("SONET_SAS_T_SESSION_WRONG"))?>',
				SASErrorNotAdmin: '<?=CUtil::JSEscape(GetMessage("SONET_SAS_T_NOT_ADMIN"))?>',
				SASErrorCurrentUserNotAuthorized: '<?=CUtil::JSEscape(GetMessage("SONET_SAS_T_NOT_ATHORIZED"))?>',
				SASErrorModuleNotInstalled: '<?=CUtil::JSEscape(GetMessage("SONET_SAS_T_MODULE_NOT_INSTALLED"))?>',
				SASSiteId: '<?=CUtil::JSEscape(SITE_ID)?>',
				SASWaitTitle: '<?=CUtil::JSEscape(GetMessage("SONET_SAS_T_WAIT"))?>',
				SASIsSessionAdmin: '<?=($arResult["IS_SESSION_ADMIN"] ? "Y" : "N")?>'
			});

		</script><?

		if ($arResult["SHOW_BANNER"])
		{
			?><div class="sonet-admin-note"><?
				?><?=GetMessage("SONET_SAS_T_ADMIN_".(!$arResult["IS_SESSION_ADMIN"] ? "OFF" : "ON"))?>
				<a href="#" onclick="__SASSetAdmin(); return false;"><?=GetMessage("SONET_SAS_T_ADMIN_".(!$arResult["IS_SESSION_ADMIN"] ? "SET" : "UNSET"))?></a>
			</div><?
		}

	}
}
?>