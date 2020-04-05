<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!$GLOBALS['USER']->IsAuthorized())
{
	$arParamsToDelete = array(
		"login",
		"logout",
		"register",
		"forgot_password",
		"change_password",
		"confirm_registration",
		"confirm_code",
		"confirm_user_id",
	);
	
	$arResult['AUTH_URL'] = 
		$arParams["REGISTER_URL"]
		.(strpos($arParams["REGISTER_URL"], "?") !== false ? "&" : "?")
		."backurl=".urlencode($APPLICATION->GetCurPageParam(
			"", array_merge($arParamsToDelete, array("backurl")), $get_index_page=false
		));
}
?>