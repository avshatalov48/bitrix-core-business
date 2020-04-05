<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

$bSelf = $arParams["USER_ID"] == $USER->GetID();
$bNetwork = is_array($arResult['NETWORK_ACCOUNT']);

if($bNetwork)
{
	$networkUrl = $arResult['NETWORK_ACCOUNT']['PERSONAL_WWW']."?user_lang=".LANGUAGE_ID;
}
?>
<div class="network-note">
	<?=$bSelf
		? (GetMessage('SAL_N_NOTE').'<br /><br />'.GetMessage('SAL_N_NOTE1', array(
				"#PERSONAL_WWW#" => $networkUrl,
				"#NAME#" => htmlspecialcharsbx($arResult["NETWORK_ACCOUNT"]['NAME']),
				"#LAST_NAME#" => htmlspecialcharsbx($arResult["NETWORK_ACCOUNT"]['LAST_NAME']),
				"#LOGIN#" => htmlspecialcharsbx($arResult["NETWORK_ACCOUNT"]['LOGIN']),
			)))
		: ($bNetwork
			? GetMessage('SAL_N_NOTE_OTHER', array(
				"#PERSONAL_WWW#" => $networkUrl,
				"#NAME#" => htmlspecialcharsbx($arResult["NETWORK_ACCOUNT"]['NAME']),
				"#LAST_NAME#" => htmlspecialcharsbx($arResult["NETWORK_ACCOUNT"]['LAST_NAME']),
				"#LOGIN#" => htmlspecialcharsbx($arResult["NETWORK_ACCOUNT"]['LOGIN']),
			))
			: GetMessage('SAL_N_NOTE_OTHER_NOT_ACCEPTED')
		)?><br><br>

	<div class="network-link">
		<a class="webform-small-button" href="<?=CSocServBitrix24Net::NETWORK_URL.'/'?>" target="_blank"><span class="webform-small-button-left"></span><span class="webform-small-button-text"><?=$bSelf ? GetMessage('SAL_N_PASSPORT') : GetMessage('SAL_N_PASSPORT_OTHER')?></span><span class="webform-small-button-right"></span></a>
	</div>
</div>

