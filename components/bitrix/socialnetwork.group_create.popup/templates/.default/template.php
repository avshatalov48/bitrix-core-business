<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if ($arResult["NEED_AUTH"] == "Y")
{
	$APPLICATION->AuthForm("");
}
{
	$this->setFrameMode(true);

	CJSCore::Init(array('ajax', 'popup'));

	$popupName = htmlspecialcharsback(CUtil::JSEscape($arParams["NAME"]));
	?><script>
	BX.ready(function() {
		BX.SGCP.Init({
			NAME: '<?=$popupName?>',
			pathToCreate: '<?=htmlspecialcharsback($arResult["PATH_TO_GROUP_EDIT"])?>',
			pathToEdit: '<?=htmlspecialcharsback($arResult["PATH_TO_GROUP_EDIT"]).(mb_strpos($arResult["PATH_TO_GROUP_EDIT"], "?") === false ? "?" : "&")."tab=edit"?>',
			pathToInvite: '<?=htmlspecialcharsback($arResult["PATH_TO_GROUP_EDIT"]).(mb_strpos($arResult["PATH_TO_GROUP_EDIT"], "?") === false ? "?" : "&")."tab=invite"?>',
			MESS: {
				'SONET_SGCP_LOADING_<?=$popupName?>': '<?=CUtil::JSEscape(GetMessage("SONET_SGCP_LOADING"))?>',
				'SONET_SGCP_T_DO_CREATE_<?=$popupName?>': '<?=CUtil::JSEscape(str_replace("#NAME#", htmlspecialcharsback($arResult["GROUP_NAME"]), GetMessage("SONET_SGCP_T_DO_CREATE")))?>',
				'SONET_SGCP_T_DO_EDIT_<?=$popupName?>': '<?=CUtil::JSEscape(str_replace("#NAME#", htmlspecialcharsback($arResult["GROUP_NAME"]), GetMessage("SONET_SGCP_T_DO_EDIT")))?>',
				'SONET_SGCP_T_DO_INVITE_<?=$popupName?>': '<?=CUtil::JSEscape(str_replace("#NAME#", htmlspecialcharsback($arResult["GROUP_NAME"]), GetMessage($arResult["IS_PROJECT"] == 'Y' ? "SONET_SGCP_T_DO_INVITE_PROJECT" : "SONET_SGCP_T_DO_INVITE")))?>',
			}
		});
	});
	</script><?
}
?>