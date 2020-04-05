<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!CModule::IncludeModule("forum")):
	ShowError(GetMessage("F_NO_MODULE"));
	return false;
endif;
/********************************************************************
				Input params
********************************************************************/
/***************** BASE ********************************************/
	$arParams["TYPE"] = strToUpper(empty($arParams["TYPE"]) ? $_REQUEST["TYPE"] : $arParams["TYPE"]);
	$arParams["TYPE"] = ($arParams["TYPE"]!="ICQ") ? "MAIL" : "ICQ";
	$arParams["SEND_MAIL"] = empty($arParams["SEND_MAIL"]) ? "E" : $arParams["SEND_MAIL"];
	$arParams["SEND_ICQ"] = empty($arParams["SEND_ICQ"]) ? "A" : $arParams["SEND_ICQ"];
	$arParams["UID"] = intVal(empty($arParams["UID"]) ? $_REQUEST["UID"] : $arParams["UID"]);
/***************** URL *********************************************/
	$URL_NAME_DEFAULT = array(
			"message_send" => "PAGE_NAME=message_send&UID=#UID#&TYPE=#TYPE#",
			"profile_view" => "PAGE_NAME=profile_view&UID=#UID#",
		);
	foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
	{
		if (!isset($arParams["URL_TEMPLATES_".strToUpper($URL)]))
			$arParams["URL_TEMPLATES_".strToUpper($URL)] = $APPLICATION->GetCurPage()."?".$URL_VALUE;
		$arParams["~URL_TEMPLATES_".strToUpper($URL)] = $arParams["URL_TEMPLATES_".strToUpper($URL)];
		$arParams["URL_TEMPLATES_".strToUpper($URL)] = htmlspecialcharsbx($arParams["URL_TEMPLATES_".strToUpper($URL)]);
	}
/***************** ADDITIONAL **************************************/
	$arParams["NAME_TEMPLATE"] = str_replace(array("#NOBR#","#/NOBR#"), "",
		(!empty($arParams["NAME_TEMPLATE"]) ? $arParams["NAME_TEMPLATE"] : CSite::GetDefaultNameFormat()));
/***************** STANDART ****************************************/
	if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
		$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
	else
		$arParams["CACHE_TIME"] = 0;
		
	$arParams["SET_NAVIGATION"] = ($arParams["SET_NAVIGATION"] == "N" ? "N" : "Y");
	$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y");
/********************************************************************
				/Input params
********************************************************************/

if ($arParams["SEND_".strToUpper($arParams["TYPE"])] <= "A"):
	ShowError($arParams["TYPE"] != "ICQ" ? GetMessage("F_NO_ACCESS") : GetMessage("F_NO_ICQ"));
	return false;
elseif ($arParams["SEND_".strToUpper($arParams["TYPE"])] <= "E" && !$USER->IsAuthorized()):
	$GLOBALS["APPLICATION"]->AuthForm($arParams["TYPE"] == "MAIL" ? GetMessage("F_NO_AUTH_MAIL") : GetMessage("F_NO_AUTH_ICQ"));
	return false;
endif;

$userRec = array();
$db_userX = CUser::GetByID($arParams["UID"]);
if (!($db_userX && $userRec = $db_userX->GetNext())):
	ShowError(str_replace("#UID#", $arParams["UID"], GetMessage("F_NO_DUSER")));
	return false;
endif;

/********************************************************************
				Default params
********************************************************************/
$arError = array();
$bVarsFromForm = false;
$userSend = array();
$arResult["ERROR_MESSAGE"] = "";
$arResult["SHOW_USER"] = "Y";
$arResult["CURRENT_USER"] = array();
$arResult["URL"] = array(
	"RECIPIENT" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PROFILE_VIEW"], array("UID" => $arParams["UID"])), 
	"~RECIPIENT" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_PROFILE_VIEW"], array("UID" => $arParams["UID"])),
	"MESSAGE_SEND" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_MESSAGE_SEND"],
		array("UID" => $arParams["UID"], "TYPE" => strtolower($arParams["TYPE"]))),
	"~MESSAGE_SEND" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_MESSAGE_SEND"],
		array("UID" => $arParams["UID"], "TYPE" => strtolower($arParams["TYPE"])))
);

$arResult["profile_view"] = $arResult["URL"]["RECIPIENT"];

$res = CForumUser::GetByUSER_ID($arParams["UID"]);
if ($res)
{
	while (list($key, $val) = each($res))
		$userRec[$key] = htmlspecialcharsbx($val);
}
$userRec["FULL_NAME"] = CForumUser::GetFormattedNameByUserID($arParams["UID"], $arParams["NAME_TEMPLATE"], $userRec);

if ($USER->IsAuthorized())
{
	$db_userY = CUser::GetByID($USER->GetID());
	if ($db_userY)
		$userSend = $db_userY->GetNext();
	$db_res = CForumUser::GetByUSER_ID($USER->GetID());
	if ($db_res)
	{
		while (list($key, $val) = each($db_res))
			$userSend[$key] = htmlspecialcharsbx($val);
	}
	$userSend["FULL_NAME"] = CForumUser::GetFormattedNameByUserID($USER->GetID(), $arParams["NAME_TEMPLATE"], $userSend);
	$userSend["E-MAIL"] = ($arParams["TYPE"]=="ICQ") ? $userSend["PERSONAL_ICQ"] : $USER->GetEmail();
	$arResult["CURRENT_USER"] = $userSend;
}
/********************************************************************
				/Default params
********************************************************************/

/********************************************************************
				Action
********************************************************************/
if ($_SERVER["REQUEST_METHOD"]=="POST" && $_POST["ACTION"] == "SEND" && check_bitrix_sessid())
{
	$userSend["FULL_NAME"] = trim(empty($userSend["FULL_NAME"]) ? $_POST["NAME"] : $userSend["FULL_NAME"]);
	$userSend["E-MAIL"] = trim(empty($userSend["E-MAIL"]) ? $_POST["EMAIL"] : $userSend["E-MAIL"]);
	
	// Use captcha
	if (($arParams["SEND_".strToUpper($arParams["TYPE"])] < "Y") && !$USER->IsAuthorized())
	{
		include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/captcha.php");
		$cpt = new CCaptcha();
		if (strlen($_REQUEST["captcha_code"]) > 0)
		{
			$captchaPass = COption::GetOptionString("main", "captcha_password", "");
			if (!$cpt->CheckCodeCrypt($_REQUEST["captcha_word"], $_REQUEST["captcha_code"], $captchaPass))
				$arError[] = array("id" => "BAD_CAPTCHA", "text" => GetMessage("F_BAD_CAPTCHA"));
		}
		else
		{
			if (!$cpt->CheckCode($_REQUEST["captcha_word"], 0))
				$arError[] = array("id" => "NO_CAPTCHA", "text" => GetMessage("F_BAD_CAPTCHA"));
		}
	}
	
	if (empty($userSend["FULL_NAME"]))
		$arError[] = array("id" => "NO_NAME", "text" => GetMessage("F_NO_NAME"));
	if (empty($userSend["E-MAIL"]))
		$arError[] = array("id" => ($arParams["TYPE"]=="ICQ" ? "NO_ICQ" : "NO_MAIL"), 
			"text" => GetMessage("F_NO_EMAIL1")." ".(($arParams["TYPE"]=="ICQ") ? GetMessage("F_NO_EMAIL2") : GetMessage("F_NO_EMAIL3")));
	elseif ($arParams["TYPE"]!="ICQ" && !check_email($userSend["E-MAIL"]))
		$arError[] = array("id" => "BAD_MAIL", "text" => GetMessage("F_BAD_EMAIL"));
	if (empty($_POST["SUBJECT"]))
		$arError[] = array("id" => "NO_SUBJECT", "text" => GetMessage("F_NO_SUBJECT"));
	if (empty($_POST["MESSAGE"]))
		$arError[] = array("id" => "NO_MESSAGE", "text" => GetMessage("F_NO_MESSAGE"));
	if ($arParams["TYPE"]=="ICQ" && strlen($userRec["PERSONAL_ICQ"])<=0)
		$arError[] = array("id" => "NO_ICQ", "text" => GetMessage("F_NO_ICQ_NUM"));
	if ($arParams["TYPE"]=="MAIL" && strlen($userRec["EMAIL"])<=0)
		$arError[] = array("id" => "NO_MAIL_D", "text" => GetMessage("F_NO_EMAIL_D"));

	if (empty($arError))
	{
		if ($arParams["TYPE"]=="ICQ")
		{
			$body   = "From ".$userSend["FULL_NAME"]." (UIN ".$userSend["E-MAIL"].")\n";
			$body  .= ($USER->IsAuthorized() ? GetMessage("F_MESS_AUTH") : GetMessage("F_MESS_NOAUTH"))."\n";
			$body  .= "<br>-----<br>\n";
			$body  .= $_POST["SUBJECT"]."\n";
			$body  .= "<br>-----<br>\n";
			$body  .= $_POST["MESSAGE"]."\n";
			$headers  = "Content-Type: text/plain; charset=windows-1254\n";
			$headers .= "From: ".$userSend["FULL_NAME"]."\nX-Mailer: System33r";
//				@mail($x_PERSONAL_ICQ."@pager.mirabilis.com", $_POST["SUBJECT"], $body, $headers);
		}
		else
		{
			$event = new CEvent;
			$arFields = Array(
				"FROM_NAME" => $userSend["FULL_NAME"],
				"FROM_EMAIL" => $userSend["E-MAIL"],
				"TO_NAME" => $userRec["FULL_NAME"],
				"TO_EMAIL" => $userRec["EMAIL"],
				"SUBJECT" => $_POST["SUBJECT"],
				"MESSAGE" => $_POST["MESSAGE"],
				"MESSAGE_DATE" => date("d.m.Y H:i:s"),
				"AUTH" => ($USER->IsAuthorized() ? GetMessage("F_MESS_AUTH") : GetMessage("F_MESS_NOAUTH"))
			);
			$event->Send("NEW_FORUM_PRIV", SITE_ID, $arFields);
		}
		LocalRedirect(ForumAddPageParams($arResult["URL"]["MESSAGE_SEND"], array("result" => "message_send")));
	}
	else 
	{
		$bVarsFromForm = true;
	}
}
/********************************************************************
				/Action
********************************************************************/

/********************************************************************
				Data
********************************************************************/
$arResult["IsAuthorized"] = $USER->IsAuthorized() ? "Y" : "N";
$arResult["ShowName"] = $userRec["FULL_NAME"];
if ($USER->IsAuthorized())
{
	$arResult["ShowMyName"] = $userSend["FULL_NAME"];
	$arResult["AuthorContacts"] = $arParams["TYPE"]=="ICQ" ? $userSend["PERSONAL_ICQ"] : $USER->GetEmail();
}
elseif ($arParams["SEND_".strToUpper($arParams["TYPE"])] < "Y" && !$USER->IsAuthorized())
{
	include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/captcha.php");
	$cpt = new CCaptcha();
	$captchaPass = COption::GetOptionString("main", "captcha_password", "");
	if (strlen($captchaPass) <= 0)
	{
		$captchaPass = randString(10);
		COption::SetOptionString("main", "captcha_password", $captchaPass);
	}
	$cpt->SetCodeCrypt($captchaPass);
	$arResult["CAPTCHA_CODE"] = htmlspecialcharsbx($cpt->GetCodeCrypt());
}
if ($bVarsFromForm)
{
	$arResult["AuthorName"] = htmlspecialcharsbx($_REQUEST["NAME"]);
	$arResult["AuthorMail"] = htmlspecialcharsbx($_REQUEST["EMAIL"]);
	$arResult["MailSubject"] = htmlspecialcharsbx($_REQUEST["SUBJECT"]);
	$arResult["MailMessage"] = htmlspecialcharsbx($_REQUEST["MESSAGE"]);
	if(!empty($arError))
	{
		$e = new CAdminException(array_reverse($arError));
		$GLOBALS["APPLICATION"]->ThrowException($e);
		$err = $GLOBALS['APPLICATION']->GetException();
		$arResult["ERROR_MESSAGE"] = $err->GetString();
	}
}
$arResult["OK_MESSAGE"] = ($_REQUEST["result"] == "message_send" ? GetMessage("F_OK_MESSAGE_SEND") : "");
/********************************************************************
				/Data
********************************************************************/
$this->IncludeComponentTemplate();

$title = ($arParams["TYPE"] == "ICQ" ? GetMessage("F_TITLE_ICQ") : GetMessage("F_TITLE_MAIL"));
if ($arParams["SET_NAVIGATION"] != "N"):
	$APPLICATION->AddChainItem($userRec["FULL_NAME"], $arResult["URL"]["~RECIPIENT"]);
	$APPLICATION->AddChainItem($title);
endif;
if ($arParams["SET_TITLE"] != "N")
	$APPLICATION->SetTitle($title);
/*******************************************************************/

?>