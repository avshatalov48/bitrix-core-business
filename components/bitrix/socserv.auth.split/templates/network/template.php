<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

\Bitrix\Main\UI\Extension::load(array("ui.buttons"));

$bSelf = $arParams["USER_ID"] == $USER->GetID();
$bNetwork = is_array($arResult['NETWORK_ACCOUNT']);

if($bNetwork)
{
	$networkUrl = $arResult['NETWORK_ACCOUNT']['PERSONAL_WWW']."?user_lang=".LANGUAGE_ID;
}
?>
<div class="network-note">
	<?=$bSelf
		? (GetMessage('SAL_N_NOTE_MSGVER_1').'<br /><br />'.GetMessage('SAL_N_NOTE1_MSGVER_1', array(
				"#PERSONAL_WWW#" => $networkUrl,
				"#NAME#" => htmlspecialcharsbx($arResult["NETWORK_ACCOUNT"]['NAME']),
				"#LAST_NAME#" => htmlspecialcharsbx($arResult["NETWORK_ACCOUNT"]['LAST_NAME']),
				"#LOGIN#" => htmlspecialcharsbx($arResult["NETWORK_ACCOUNT"]['LOGIN']),
			)))
		: ($bNetwork
			? GetMessage('SAL_N_NOTE_OTHER_MSGVER_1', array(
				"#PERSONAL_WWW#" => $networkUrl,
				"#NAME#" => htmlspecialcharsbx($arResult["NETWORK_ACCOUNT"]['NAME']),
				"#LAST_NAME#" => htmlspecialcharsbx($arResult["NETWORK_ACCOUNT"]['LAST_NAME']),
				"#LOGIN#" => htmlspecialcharsbx($arResult["NETWORK_ACCOUNT"]['LOGIN']),
			))
			: GetMessage('SAL_N_NOTE_OTHER_NOT_ACCEPTED_MSGVER_1')
		)?><br><br>

	<div>
		<a class="webform-small-button" href="<?=CSocServBitrix24Net::NETWORK_URL.'/'?>" target="_blank"><span class="webform-small-button-left"></span><span class="webform-small-button-text"><?=$bSelf ? GetMessage('SAL_N_PASSPORT_MSGVER_1') : GetMessage('SAL_N_PASSPORT_OTHER_MSGVER_1')?></span><span class="webform-small-button-right"></span></a>
		<br/><br/>
		<span class="ui-btn ui-btn-light-border" data-role="socserv-logout"><?=GetMessage("SAL_N_LOGOUT")?></span>
	</div>
</div>

<script>
	BX.message({
		"SOCSERV_BUTTON_CONTINUE" : "<?=CUtil::JSEscape(GetMessage("SOCSERV_BUTTON_CONTINUE"))?>",
		"SOCSERV_BUTTON_CANCEL" : "<?=CUtil::JSEscape(GetMessage("SOCSERV_BUTTON_CANCEL"))?>",
		"SOCSERV_LOGOUT_TEXT" : "<?=CUtil::JSEscape(GetMessage("SOCSERV_LOGOUT_TEXT"))?>",
		"SOCSERV_LOGOUT_TITLE" : "<?=CUtil::JSEscape(GetMessage("SOCSERV_LOGOUT_TITLE"))?>",
		"SOCSERV_LOGOUT_SUCCESS" : "<?=CUtil::JSEscape(GetMessage("SOCSERV_LOGOUT_SUCCESS"))?>"
	});
	BX.ready(function () {
		BX.SocialServices.Auth.init({
			signedParameters: '<?=$this->getComponent()->getSignedParameters()?>',
			componentName: '<?=$this->getComponent()->getName() ?>'
		});
	});
</script>

